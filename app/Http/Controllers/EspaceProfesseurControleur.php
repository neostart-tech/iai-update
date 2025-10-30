<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\CahierTexte;
use App\Models\Cours;
use App\Models\EmploiDuTemp;
use App\Models\Etudiant;
use App\Models\Devoir;
use App\Models\CoursPresence;
use App\Models\Sanction;
use App\Models\EnseignantPresence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\AttendanceService;

class EspaceProfesseurControleur extends Controller
{
    public function show()
    {
        return view('professeurs._index');
    }

    public function mescours()
    {
        $cours = EmploiDuTemp::with(['salle', 'group', 'uv'])
            ->where('owner_id', Auth::guard('enseignants')->user()->id)
            ->get();

        $formatted = $cours->map(function ($c) {
            return [
                'title' => ($c->uv?->uniteEnseignement?->nom ?? $c->uv?->nom ?? $c->details ?? 'Cours') . ' - ' . ($c->salle->nom ?? 'Salle ?'),
                'start' => $c->debut,
                'end' => $c->fin,
                'extendedProps' => [
                    'salle' => $c->salle->nom ?? 'Salle inconnue',
                    'groupe_id' => $c->group->id ?? null,
                    'groupe' => $c->group->nom ?? 'Groupe inconnu',
                    'type_programme' => $c->type_programme,
                    'uv_id' => $c->uv_id,
                    'emploi_du_temps_id' => $c->id,
                    'matiere' => $c->uv?->nom ?? $c->uv?->code ?? null,
                    'creneau' => ($c->debut?->format('H:i') ?? '') . ' - ' . ($c->fin?->format('H:i') ?? ''),
                ],
            ];
        });

        return response()->json($formatted);
    }

    // Backward-compatible alias for older routes
    public function myCourses()
    {
        return $this->mescours();
    }

    // Backward-compatible stub; can be enhanced to return students by teacher's groups
    public function myStudents()
    {
        return response()->json([]);
    }

    public function listeetudiant($group)
    {
        $group = EmploiDuTemp::with(['group.etudiants'])
            ->where('group_id', $group)
            ->first();

        if (!$group) {
            return response()->json(['message' => 'Groupe non trouvé'], 404);
        }

        $etudiants = $group->group->etudiants;

        return response()->json($etudiants);
    }

    // Enregistrer les absences (POST)
    public function enregistrerAbsences(Request $request)
    {
        try {
            $payload = $request->all();

            // Déterminer la séance et en déduire le cours + la date de la séance
            $emploiId = $payload['emploi_du_temps_id'] ?? null;
            $coursId = $payload['cours_id'] ?? null; // si fourni directement
            $sessionDate = now()->toDateString();

            if ($emploiId) {
                $emploi = EmploiDuTemp::find($emploiId);
                if (!$emploi) {
                    return response()->json(['message' => 'Séance introuvable'], 404);
                }
                $sessionDate = $emploi->debut ? date('Y-m-d', strtotime($emploi->debut)) : $sessionDate;
                // Relier la séance à un Cours du jour (uv + groupe + date)
                $cours = Cours::firstOrCreate(
                    [
                        'uv_id' => $emploi->uv_id,
                        'groupe_id' => $emploi->group_id,
                        'date_cours' => $sessionDate,
                    ],
                    [
                        'titre' => $emploi->details ?? 'Cours',
                    ]
                );
                $coursId = $cours->id;
            }

            if (!$coursId) {
                return response()->json(['message' => 'Le cours lié à la séance est introuvable'], 422);
            }

            // Nouveau schéma: presences tri-state. Fallback: absents[]
            if (isset($payload['presences'])) {
                $request->validate([
                    'presences' => 'required|array',
                    'presences.*.etudiant_id' => 'required|exists:etudiants,id',
                    'presences.*.statut' => 'required|string|in:present,retard,absent,justifie',
                    'presences.*.commentaire' => 'nullable|string|max:500',
                    'presences.*.sanction' => 'nullable|string|max:500',
                ]);

                // Effacer toutes les entrées existantes de la journée pour ce cours afin de simplifier
                CoursPresence::where('cours_id', $coursId)->delete();

                foreach ($payload['presences'] as $pr) {
                    $sanctionId = null;
                    if (($pr['statut'] ?? null) === 'retard' && !empty($pr['sanction'])) {
                        $sanction = Sanction::create([
                            'cours_id' => $coursId,
                            'etudiant_id' => (int)$pr['etudiant_id'],
                            'enseignant_id' => Auth::guard('enseignants')->id() ?? Auth::id(),
                            'description' => $pr['sanction'],
                        ]);
                        $sanctionId = $sanction->id;
                    }

                    CoursPresence::updateOrCreate(
                        [
                            'cours_id' => $coursId,
                            'etudiant_id' => (int)$pr['etudiant_id'],
                        ],
                        [
                            'emploi_du_temps_id' => $emploiId,
                            'statut' => $pr['statut'],
                            'commentaire' => $pr['commentaire'] ?? null,
                            'needs_validation' => $pr['statut'] === 'absent', // absence nécessite validation
                            'sanction_id' => $sanctionId,
                        ]
                    );
                }
                // Recompute absence thresholds and notifications
                try {
                    (new AttendanceService())->updateStatusesForCours($cours);
                } catch (\Throwable $e) {
                    // silently ignore; logging could be added
                }

                return response()->json(['message' => 'Présences enregistrées avec succès']);
            } else {
                // Compat: ancienne API absents[]
                $request->validate([
                    'absents' => 'required|array',
                    'absents.*.etudiant_id' => 'required|exists:etudiants,id',
                    'absents.*.motif' => 'nullable|string',
                ]);

                $absentIds = collect($payload['absents'] ?? [])->pluck('etudiant_id')->map(fn($v) => (int)$v)->all();

                // Supprimer les absences décochées pour cette séance
                $deleteQuery = Absence::where('cours_id', $coursId)->whereDate('date_absence', $sessionDate);
                if (count($absentIds) > 0) {
                    $deleteQuery->whereNotIn('etudiant_id', $absentIds);
                }
                $deleteQuery->delete();

                // Créer/mettre à jour les absences soumises
                foreach ($payload['absents'] as $absent) {
                    Absence::updateOrCreate(
                        [
                            'etudiant_id' => (int)$absent['etudiant_id'],
                            'cours_id' => (int)$coursId,
                            'date_absence' => $sessionDate,
                        ],
                        [
                            'motif' => $absent['motif'] ?? null,
                        ]
                    );
                }

                return response()->json(['message' => 'Absences enregistrées avec succès']);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Retourne les erreurs de validation
            return response()->json(['message' => $e->getMessage(), 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function presenceStats($emploi_du_temps_id)
    {
        $emploi = EmploiDuTemp::findOrFail($emploi_du_temps_id);
        $date = $emploi->debut ? date('Y-m-d', strtotime($emploi->debut)) : now()->toDateString();
        $cours = Cours::where('uv_id', $emploi->uv_id)
            ->where('groupe_id', $emploi->group_id)
            ->whereDate('date_cours', $date)
            ->first();
        if (!$cours) return response()->json(['present' => 0, 'retard' => 0, 'absent' => 0, 'justifie' => 0]);

        $counts = CoursPresence::where('cours_id', $cours->id)
            ->selectRaw("statut, COUNT(*) as c")
            ->groupBy('statut')
            ->pluck('c', 'statut');

        $teacher = EnseignantPresence::where('emploi_du_temps_id', $emploi->id)
            ->where('enseignant_id', Auth::guard('enseignants')->id() ?? Auth::id())
            ->first();

        return response()->json([
            'present' => (int)($counts['present'] ?? 0),
            'retard' => (int)($counts['retard'] ?? 0),
            'absent' => (int)($counts['absent'] ?? 0),
            'justifie' => (int)($counts['justifie'] ?? 0),
            'teacher' => $teacher ? ['statut' => $teacher->statut, 'commentaire' => $teacher->commentaire] : null,
        ]);
    }

    public function saveTeacherPresence(Request $request)
    {
        $request->validate([
            'emploi_du_temps_id' => 'required|exists:emploi_du_temps,id',
            'statut' => 'required|in:present,retard,absent',
            'commentaire' => 'nullable|string|max:500'
        ]);
        $enseignantId = Auth::guard('enseignants')->id() ?? Auth::id();
        EnseignantPresence::updateOrCreate(
            [
                'emploi_du_temps_id' => $request->emploi_du_temps_id,
                'enseignant_id' => $enseignantId,
            ],
            [
                'statut' => $request->statut,
                'commentaire' => $request->commentaire,
            ]
        );
        return response()->json(['message' => 'Présence enseignant enregistrée']);
    }

    public function getTeacherPresence($emploi_du_temps_id)
    {
        $enseignantId = Auth::guard('enseignants')->id() ?? Auth::id();
        $p = EnseignantPresence::where('emploi_du_temps_id', $emploi_du_temps_id)
            ->where('enseignant_id', $enseignantId)
            ->first();
        return response()->json($p);
    }

    // Récupérer le nombre d'absences d'un étudiant pour un cours et un semestre
    public function nombreAbsences($etudiant_id, $cours_id, $debut_semestre, $fin_semestre)
    {
        $count = Absence::where('etudiant_id', $etudiant_id)
            ->where('cours_id', $cours_id)
            ->whereBetween('date_absence', [$debut_semestre, $fin_semestre])
            ->count();

        return response()->json(['nombre_absences' => $count]);
    }

    // Récupérer absents et présents pour un cours à une date
    public function listePresence($cours_id, $date)
    {
        $cours = Cours::findOrFail($cours_id);
        $groupe_id = $cours->groupe_id;

        $etudiants = Etudiant::where('groupe_id', $groupe_id)->get();
        $absents = Absence::where('cours_id', $cours_id)
            ->whereDate('date_absence', $date)
            ->pluck('etudiant_id')
            ->toArray();

        $liste_absents = $etudiants->whereIn('id', $absents)->values();
        $liste_presents = $etudiants->whereNotIn('id', $absents)->values();

        return response()->json([
            'absents' => $liste_absents,
            'presents' => $liste_presents,
        ]);
    }

    // Enregistrer le cahier de texte (POST)
    public function enregistrerCahierTexte(Request $request)
    {
        $request->validate([
            'emploi_du_temps_id' => 'required|exists:emploi_du_temps,id',
            'titre' => 'required|string',
            'contenu' => 'required|string',
            'piece_jointe' => 'nullable|file|max:2048',
        ]);

        $data = $request->only(['emploi_du_temps_id', 'titre', 'contenu']);
        if ($request->hasFile('piece_jointe')) {
            $data['piece_jointe'] = $request->file('piece_jointe')->store('cahier_textes');
        }

        // Fill workflow context
        $emploi = EmploiDuTemp::find($request->emploi_du_temps_id);
        $data['group_id'] = $emploi?->group_id;
        $data['niveau_id'] = $emploi?->group?->niveau_id ?? null;
        $data['created_by_user_id'] = Auth::id();
        $data['created_by_role'] = 'enseignant';

        CahierTexte::updateOrCreate(
            ['emploi_du_temps_id' => $data['emploi_du_temps_id']],
            $data
        );

        return response()->json(['message' => 'Cahier de texte enregistré avec succès']);
    }

    // Teacher approval endpoint
    public function approuverCahierTexte(Request $request)
    {
        $request->validate([
            'emploi_du_temps_id' => 'required|exists:emploi_du_temps,id',
            'remarks' => 'nullable|string',
        ]);
        $cahier = \App\Models\CahierTexte::where('emploi_du_temps_id', $request->emploi_du_temps_id)->first();
        if (!$cahier) return response()->json(['message' => 'Cahier introuvable'], 404);
        $cahier->update([
            'approved_by_user_id' => Auth::id(),
            'approved_at' => now(),
            'remarks' => $request->remarks,
        ]);
        return response()->json(['message' => 'Cahier approuvé']);
    }

    // Mark inconsistency between committee content and teacher remarks, with notes
    public function marquerIncoherenceCahier(Request $request)
    {
        $request->validate([
            'emploi_du_temps_id' => 'required|exists:emploi_du_temps,id',
            'notes' => 'required|string'
        ]);
        $cahier = \App\Models\CahierTexte::where('emploi_du_temps_id', $request->emploi_du_temps_id)->first();
        if (!$cahier) return response()->json(['message' => 'Cahier introuvable'], 404);
        $cahier->update([
            'incoherent' => true,
            'incoherence_notes' => $request->notes,
        ]);
        return response()->json(['message' => 'Incohérence marquée']);
    }

    // Backward-compatible alias to support legacy route signature with {group}
    public function storeCahierDeTexte($group, Request $request)
    {
        return $this->enregistrerCahierTexte($request);
    }

    // Vue pour afficher absents et présents d'un cours à une date
    public function vuePresence($emploi_du_temps_id)
    {
        $emploi = EmploiDuTemp::findOrFail($emploi_du_temps_id);
        $groupe_id = $emploi->group_id;
        $etudiants = Etudiant::where('groupe_id', $groupe_id)->get();
        $sessionDate = $emploi->debut ? date('Y-m-d', strtotime($emploi->debut)) : now()->toDateString();
        $cours = Cours::firstOrCreate(
            [
                'uv_id' => $emploi->uv_id,
                'groupe_id' => $groupe_id,
                'date_cours' => $sessionDate,
            ],
            [
                'titre' => $emploi->details ?? 'Cours',
            ]
        );
        $absents = Absence::where('cours_id', $cours->id)
            ->whereDate('date_absence', $sessionDate)
            ->pluck('etudiant_id')
            ->toArray();

        return view('professeurs.presence', [
            'emploi' => $emploi,
            'cours' => $cours,
            'etudiants' => $etudiants,
            'absents' => $absents,
        ]);
    }

    // Enregistrer un devoir (POST)
    public function enregistrerDevoir(Request $request)
    {
        $request->validate([
            'emploi_du_temps_id' => 'required|exists:emploi_du_temps,id',
            'titre' => 'required|string',
            'consignes' => 'required|string',
            'fichier' => 'nullable|file|max:2048',
            'date_limite' => 'required|date',
            'correction' => 'nullable|string',
        ]);

        $data = $request->only(['emploi_du_temps_id', 'titre', 'consignes', 'date_limite', 'correction']);
        if ($request->hasFile('fichier')) {
            $data['fichier'] = $request->file('fichier')->store('devoirs');
        }

        Devoir::updateOrCreate($data);

        return response()->json(['message' => 'Devoir enregistré avec succès']);
    }

    // Backward-compatible alias to support legacy route signature with {group}
    public function storeDevoir($group, Request $request)
    {
        return $this->enregistrerDevoir($request);
    }

    public function getCahierTexte($emploi_du_temps_id)
    {
        $cahier = CahierTexte::where('emploi_du_temps_id', $emploi_du_temps_id)->first();
        $emploi = EmploiDuTemp::with(['group','owner'])->find($emploi_du_temps_id);
        $profName = method_exists($emploi?->owner, 'completName') ? $emploi->owner->completName() : ($emploi?->owner?->name ?? null);
        $niveau = $emploi?->group?->niveau?->libelle ?? null;
        $etudiantName = null;
        if ($cahier?->etudiant_id) {
            $et = \App\Models\Etudiant::find($cahier->etudiant_id);
            $etudiantName = $et?->completName();
        }
        return response()->json([
            'cahier' => $cahier,
            'professeur' => $profName,
            'niveau' => $niveau,
            'etudiant' => $etudiantName,
        ]);
    }

    public function getDevoir($emploi_du_temps_id)
    {
        $devoir = Devoir::where('emploi_du_temps_id', $emploi_du_temps_id)->first();
        return response()->json($devoir);
    }

    public function getAbsences($emploi_du_temps_id)
    {
        $emploi = EmploiDuTemp::find($emploi_du_temps_id);
        if (!$emploi) return response()->json([]);
        $date = $emploi->debut ? date('Y-m-d', strtotime($emploi->debut)) : now()->toDateString();
        $cours = Cours::where('uv_id', $emploi->uv_id)
            ->where('groupe_id', $emploi->group_id)
            ->whereDate('date_cours', $date)
            ->first();
        if (!$cours) return response()->json([]);

        // Prefer new tri-state presence data if exists
        $pres = CoursPresence::where('cours_id', $cours->id)->get();
        if ($pres->isNotEmpty()) {
            $data = $pres->map(function($p){
                return [
                    'etudiant_id' => $p->etudiant_id,
                    'statut' => $p->statut,
                    'commentaire' => $p->commentaire,
                    'sanction' => optional($p->sanction)->description,
                    'needs_validation' => (bool)$p->needs_validation,
                ];
            });
            return response()->json($data);
        }

        // Fallback for legacy: mark absences
        $absences = Absence::where('cours_id', $cours->id)
            ->whereDate('date_absence', $date)
            ->get(['etudiant_id', 'motif']);

        $data = $absences->map(fn($a) => [
            'etudiant_id' => $a->etudiant_id,
            'statut' => 'absent',
            'commentaire' => $a->motif,
            'sanction' => null,
        ]);

        return response()->json($data);
    }

    // Backward-compatible alias to support legacy route signature with {group}
    public function storePresence($group, Request $request)
    {
        return $this->enregistrerAbsences($request);
    }
}
