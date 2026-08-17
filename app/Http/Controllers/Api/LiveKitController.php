<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Filiere;
use App\Models\Group;
use App\Models\User;
use App\Models\Etudiant;
use App\Models\UniteValeur;
use App\Services\LiveKitService;
use App\Mail\LiveKitCourseInvitationMail;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class LiveKitController extends Controller
{
    protected LiveKitService $liveKitService;

    public function __construct(LiveKitService $liveKitService)
    {
        $this->liveKitService = $liveKitService;
    }

    /**
     * Obtenir la liste des filières/classes disponibles depuis la base de données
     */
    public function getClasses(Request $request)
    {
        $filieres = Filiere::select('id', 'nom', 'code', 'slug')->get();
        
        if ($filieres->isEmpty()) {
            $filieres = Group::select('id', 'nom', 'slug')->get();
        }

        return response()->json([
            'data' => $filieres
        ]);
    }

    /**
     * Obtenir la liste des matières attribuées au professeur connecté
     */
    public function getTeacherMatieres(Request $request)
    {
        $user = $request->user() ?? auth('sanctum')->user() ?? auth()->user();
        if (!$user) {
            return response()->json(['data' => UniteValeur::select('id', 'nom', 'libelle', 'code')->get()]);
        }

        $userId = $user->id ?? $user->getKey();
        $matieres = collect();

        if ($userId) {
            // 1. Récupérer les IDs d'UV attribués dans la table user_unite_valeur
            $uvIds = \Illuminate\Support\Facades\DB::table('user_unite_valeur')
                ->where('user_id', $userId)
                ->pluck('unite_valeur_id')
                ->filter()
                ->toArray();

            if (!empty($uvIds)) {
                $matieres = UniteValeur::whereIn('id', $uvIds)->get();
            }

            // 2. Tenter aussi via le modèle UserUniteValeur
            if ($matieres->isEmpty()) {
                $uvIdsRelation = \App\Models\UserUniteValeur::where('user_id', $userId)
                    ->pluck('unite_valeur_id')
                    ->filter()
                    ->toArray();

                if (!empty($uvIdsRelation)) {
                    $matieres = UniteValeur::whereIn('id', $uvIdsRelation)->get();
                }
            }

            // 3. Tenter via colonnes directes
            if ($matieres->isEmpty()) {
                $matieres = UniteValeur::where('user_id', $userId)
                    ->orWhere('enseignant_id', $userId)
                    ->get();
            }
        }

        // Si l'utilisateur n'a pas encore de matières attribuées ou si admin, retourner toutes les matières par sécurité
        if ($matieres->isEmpty()) {
            $matieres = UniteValeur::select('id', 'nom', 'libelle', 'code')->get();
        }

        return response()->json([
            'data' => $matieres
        ]);
    }

    /**
     * Obtenir la liste des étudiants de la classe/filière sélectionnée
     */
    public function getStudentsForClass(Request $request)
    {
        $classeStr = $request->query('classe');
        $matiereId = $request->query('matiere_id');
        $matiereName = $request->query('matiere');
        
        // Nettoyer le nom de la matière si un préfixe de semestre est présent (ex: "Semestre 1 - GESTION...")
        if ($matiereName && str_contains($matiereName, ' - ')) {
            $parts = explode(' - ', $matiereName);
            $matiereName = trim(end($parts));
        }

        $filiereId = null;
        $niveauId = null;

        // 1. Recherche de la Matière (UniteValeur)
        $uv = null;
        if ($matiereId) {
            $uv = UniteValeur::find($matiereId);
        }
        if (!$uv && $matiereName) {
            $uv = UniteValeur::where('nom', 'LIKE', "%{$matiereName}%")
                ->orWhere('libelle', 'LIKE', "%{$matiereName}%")
                ->orWhere('code', 'LIKE', "%{$matiereName}%")
                ->first();
        }

        if ($uv) {
            $filiereId = $uv->filiere_id ?? ($uv->uniteEnseignement->filiere_id ?? null);
            $niveauId = $uv->niveau_id ?? ($uv->uniteEnseignement->niveau_id ?? null);
        }

        // 2. Requête pour cibler les étudiants de la Filière / Niveau associés à la matière
        $query = Etudiant::query();

        if ($filiereId || $niveauId) {
            $query->where(function ($q) use ($filiereId, $niveauId) {
                // Recherche via la table pivot etudiant_group
                $q->whereExists(function ($sub) use ($filiereId, $niveauId) {
                    $sub->select(\DB::raw(1))
                        ->from('etudiant_group')
                        ->whereColumn('etudiant_group.etudiant_id', 'etudiants.id');
                    
                    if ($filiereId) {
                        $sub->where('etudiant_group.filiere_id', $filiereId);
                    }
                    if ($niveauId) {
                        $sub->where('etudiant_group.niveau_id', $niveauId);
                    }
                });

                // Recherche via relation directe filière si disponible
                if ($filiereId) {
                    $q->orWhereHas('filiere', function ($fQ) use ($filiereId) {
                        $fQ->where('id', $filiereId);
                    });
                }
            });
        } elseif ($classeStr) {
            $classeClean = strtolower(trim($classeStr));
            $query->where(function ($q) use ($classeClean) {
                $q->whereHas('groupes', function ($gQ) use ($classeClean) {
                    $gQ->whereRaw('LOWER(nom) LIKE ?', ["%{$classeClean}%"])
                       ->orWhereRaw('LOWER(code) LIKE ?', ["%{$classeClean}%"]);
                })
                ->orWhereHas('group', function ($gQ) use ($classeClean) {
                    $gQ->whereRaw('LOWER(nom) LIKE ?', ["%{$classeClean}%"])
                       ->orWhereRaw('LOWER(code) LIKE ?', ["%{$classeClean}%"]);
                })
                ->orWhereHas('filiere', function ($fQ) use ($classeClean) {
                    $fQ->whereRaw('LOWER(nom) LIKE ?', ["%{$classeClean}%"])
                       ->orWhereRaw('LOWER(code) LIKE ?', ["%{$classeClean}%"]);
                })
                ->orWhereRaw('LOWER(classe) LIKE ?', ["%{$classeClean}%"]);
            });
        }

        $etudiants = $query->limit(200)->get();

        // 3. Secours : si aucun étudiant n'est rattaché spécifiquement à cette filière/niveau
        if ($etudiants->isEmpty()) {
            $etudiants = Etudiant::where('statut', '!=', 'bloque')->limit(200)->get();
        }

        if ($etudiants->isEmpty()) {
            $etudiants = Etudiant::limit(200)->get();
        }

        $formatted = $etudiants->map(function ($e) {
            $isBloque = ($e->statut === 'bloque' || $e->estEnAbandon());
            return [
                'id' => $e->id,
                'nom' => $e->nom,
                'prenom' => $e->prenom,
                'nom_complet' => $e->nom_complet,
                'email' => $e->email,
                'matricule' => $e->matricule,
                'statut' => $e->statut ?? 'actif',
                'scolarite_a_jour' => !$isBloque,
            ];
        });

        return response()->json([
            'data' => $formatted
        ]);
    }

    /**
     * Expédier les invitations par email aux étudiants sélectionnés
     */
    public function sendInvitations(Request $request)
    {
        $request->validate([
            'room_slug' => 'required|string',
            'course_title' => 'required|string',
            'classe' => 'required|string',
            'student_ids' => 'required|array',
        ]);

        $user = auth('sanctum')->user() ?? auth()->user();
        $teacherName = $user ? ($user->nom_complet ?? (($user->nom ?? '') . ' ' . ($user->prenom ?? ''))) : 'Enseignant';

        $baseUrl = config('app.frontend_url') ?: (config('app.url') ?: 'http://localhost:3000');
        $courseUrl = rtrim($baseUrl, '/') . '/cours-en-ligne/' . $request->input('room_slug');

        $students = Etudiant::whereIn('id', $request->input('student_ids'))->get();
        $sentCount = 0;

        foreach ($students as $student) {
            if ($student->email) {
                try {
                    Mail::to($student->email)->queue(new LiveKitCourseInvitationMail(
                        $student->nom_complet,
                        $request->input('course_title'),
                        trim($teacherName) ?: 'Enseignant',
                        $request->input('classe'),
                        $courseUrl
                    ));
                    $sentCount++;
                } catch (\Exception $ex) {
                    \Log::error("Erreur d'envoi d'email à {$student->email}: " . $ex->getMessage());
                }
            }
        }

        return response()->json([
            'message' => "{$sentCount} invitation(s) e-mail expédiée(s) avec succès.",
            'sent_count' => $sentCount
        ]);
    }

    /**
     * Basculer le statut d'accès d'un étudiant (Bloqué / Actif)
     */
    public function toggleStudentAccess(Request $request)
    {
        $request->validate([
            'student_id' => 'required|integer',
            'statut' => 'required|string|in:bloque,actif',
        ]);

        $student = Etudiant::findOrFail($request->input('student_id'));
        $student->statut = $request->input('statut');
        $student->save();

        return response()->json([
            'message' => "Statut de l'étudiant mis à jour avec succès : {$student->statut}",
            'student' => [
                'id' => $student->id,
                'statut' => $student->statut,
                'scolarite_a_jour' => ($student->statut !== 'bloque')
            ]
        ]);
    }

    /**
     * Obtenir les sessions de cours en direct actives
     */
    public function getActiveRooms(Request $request)
    {
        $rooms = Cache::get('livekit_active_rooms', []);
        return response()->json([
            'data' => array_values($rooms)
        ]);
    }

    /**
     * Créer / Démarrer une nouvelle session de cours en direct
     */
    public function createRoom(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'classe' => 'required|string',
        ]);

        $user = auth('sanctum')->user() ?? auth()->user();
        $teacherName = $user ? ($user->nom_complet ?? (($user->nom ?? '') . ' ' . ($user->prenom ?? ''))) : 'Enseignant';

        $titleClean = Str::slug($request->input('title'));
        $classeClean = Str::slug($request->input('classe'));
        $slug = $titleClean . '-' . $classeClean;

        $newRoom = [
            'id' => uniqid(),
            'title' => $request->input('title'),
            'teacher' => trim($teacherName) ?: 'Enseignant',
            'classe' => $request->input('classe'),
            'slug' => $slug,
            'created_at' => now()->toIso8601String(),
        ];

        $rooms = Cache::get('livekit_active_rooms', []);
        $rooms[$slug] = $newRoom;
        Cache::put('livekit_active_rooms', $rooms, now()->addHours(6));

        // Déclencher automatiquement l'envoi d'invitations e-mail aux étudiants de la classe si spécifié
        if ($request->has('student_ids') && is_array($request->input('student_ids')) && !empty($request->input('student_ids'))) {
            $this->sendInvitations(new Request([
                'room_slug' => $slug,
                'course_title' => $request->input('title'),
                'classe' => $request->input('classe'),
                'student_ids' => $request->input('student_ids')
            ]));
        }

        return response()->json([
            'message' => 'Cours démarré avec succès',
            'room' => $newRoom,
        ]);
    }

    /**
     * Endpoint API pour délivrer un token LiveKit à un utilisateur authentifié
     */
    public function getToken(Request $request)
    {
        $request->validate([
            'room_name' => 'required|string',
        ]);

        $user = auth('sanctum')->user() ?? auth()->user();
        if (!$user) {
            return response()->json(['message' => 'Non autorisé'], 401);
        }

        // Vérification des droits enseignant / admin
        $isTeacher = false;
        if ($user instanceof User) {
            $isTeacher = $user->roles()->whereIn('slug', ['enseignant', 'admin', 'directeur-academique', 'informaticien', 'directeur-general'])->exists();
        }

        // Vérification de la scolarité et du statut pour les étudiants
        if (!$isTeacher && $user instanceof Etudiant) {
            if ($user->statut === 'bloque' || $user->estEnAbandon() || !$user->peutComposer()) {
                return response()->json([
                    'message' => 'Accès refusé : Vos frais de scolarité ne sont pas à jour ou votre compte est restreint. Veuillez contacter le service comptabilité.'
                ], 403);
            }
        }

        $identity = 'user_' . $user->id;
        $name = $user->nom_complet ?? (($user->nom ?? '') . ' ' . ($user->prenom ?? ''));

        $token = $this->liveKitService->generateToken(
            $request->input('room_name'),
            $identity,
            trim($name) ?: 'Participant',
            $isTeacher
        );

        return response()->json([
            'token' => $token,
            'url' => config('services.livekit.url') ?: env('LIVEKIT_URL', 'wss://escen-t8b81gm4.livekit.cloud'),
            'is_teacher' => $isTeacher,
            'identity' => $identity,
            'name' => trim($name) ?: 'Participant',
        ]);
    }
}
