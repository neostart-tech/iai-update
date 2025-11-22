<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\AnneeScolaire;
use App\Models\CahierTexte;
use App\Models\Cours;
use App\Models\CoursPresence;
use App\Models\Devoir;
use App\Models\EmploiDuTemp;
use App\Models\EnseignantPresence;
use App\Models\Etudiant;
use App\Models\EtudiantGroup;
use App\Models\Group;
use App\Models\Evaluation;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EspaceProfesseurControleur extends Controller
{
    public function show()
    {
        return view('professeurs._index');
    }

    public function mescours()
    {
        $cours = EmploiDuTemp::with(['salle', 'group', 'uv','owner'])
            ->where('owner_id', Auth::guard('enseignants')->user()->id)
            ->get();

        $formatted = $cours->map(function ($c) {
            return [
                'title' => ($c->uv?->uniteEnseignement?->nom ?? $c->uv?->nom ?? $c->details ?? 'Cours').' - '.($c->salle->nom ?? 'Salle ?'),
                'start' => $c->debut,
                'end' => $c->fin,
                'extendedProps' => [
                    'salle' => $c->salle->nom ?? 'Salle inconnue',
                    'salle_id' => $c->salle->id ?? null,
                    'groupe_id' => $c->group->id ?? null,
                    'groupe' => $c->group->nom ?? 'Groupe inconnu',
                    'type_programme' => $c->type_programme,
                    'uv_id' => $c->uv_id,
                    'emploi_du_temps_id' => $c->id,
                    "annee_scolaire_id" => $c->annee_scolaire_id,
                    'matiere' => $c->uv?->nom ?? $c->uv?->code ?? null,
                    'creneau' => ($c->debut?->format('H:i') ?? '').' - '.($c->fin?->format('H:i') ?? ''),
                    'is_online' => $c->is_online,
                    'duration_minutes' => $c->duration_minutes ?? null,
                    'security_level' => $c->security_level ?? null,
                    'debut' => $c->debut,
                    'user'=> $c->owner?->completName() ?? $c->owner?->name ?? 'Inconnu',
                    'fin' => $c->fin,
                    'details' => $c->details,
                    'user_id' => $c->owner_id,
                    'autosave_enabled' => $c->autosave_enabled ?? null,
                    'disable_copy_paste' => $c->disable_copy_paste ?? null,
                    'disable_right_click' => $c->disable_right_click ?? null,
                    'disable_printscreen' => $c->disable_printscreen ?? null,
                    'forbid_tab_switch' => $c->forbid_tab_switch ?? null,
                    'max_focus_lost' => $c->max_focus_lost ?? null,
                    'auto_submit_on_time_end' => $c->auto_submit_on_time_end ?? null,
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

    public function mesCoursShow()
    {

        return view('professeurs.mes-cours');
    }

    public function mesEvaluationsShow()
    {
        return view('professeurs.evaluations.mes-evaluations');
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

        if (! $group) {
            return response()->json(['message' => 'Groupe non trouvé'], 404);
        }

        $etudiants = $group->group->etudiants;

        return response()->json($etudiants);
    }

    public function enregistrerAbsences(Request $request)
    {
        try {
            $payload = $request->all();

            $emploiId = $payload['emploi_du_temps_id'] ?? null;
            $coursId = $payload['cours_id'] ?? null;
            $sessionDate = now()->toDateString();

            if ($emploiId) {
                $emploi = EmploiDuTemp::find($emploiId);
                if (! $emploi) {
                    return response()->json(['message' => 'Séance introuvable'], 404);
                }

                $sessionDate = $emploi->debut ? date('Y-m-d', strtotime($emploi->debut)) : $sessionDate;

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

            if (! $coursId) {
                return response()->json(['message' => 'Le cours lié à la séance est introuvable'], 422);
            }

            $request->validate([
                'presences' => 'required|array',
                'presences.*.etudiant_id' => 'required|exists:etudiants,id',
                'presences.*.statut' => 'required|string|in:present,retard,absent,justifie',
                'presences.*.commentaire' => 'nullable|string|max:500',
                'presences.*.sanction' => 'nullable|string|max:500',
            ]);

            $exceptions = [];
            $presentIds = [];

            foreach ($payload['presences'] as $pr) {
                if ($pr['statut'] !== 'present') {
                    $exceptions[] = [
                        'cours_id' => $coursId,
                        'etudiant_id' => (int) $pr['etudiant_id'],
                        'emploi_du_temps_id' => $emploiId,
                        'statut' => $pr['statut'],
                        'commentaire' => $pr['commentaire'] ?? null,
                        'needs_validation' => $pr['statut'] === 'absent',
                        'sanction' => $pr['sanction'] ?? null,
                    ];
                } else {
                    $presentIds[] = (int) $pr['etudiant_id'];
                }
            }

            // Supprimer les enregistrements pour ceux qui sont redevenus "present"
            if (count($presentIds) > 0) {
                CoursPresence::where('cours_id', $coursId)
                    ->whereIn('etudiant_id', $presentIds)
                    ->delete();
            }

            // Upsert des exceptions pour ceux qui ne sont pas présents
            if (count($exceptions) > 0) {
                CoursPresence::upsert(
                    $exceptions,
                    ['cours_id', 'etudiant_id'],
                    ['statut', 'commentaire', 'needs_validation', 'sanction']
                );
            }

            // Mettre à jour les statuts globaux si nécessaire
            try {
                (new AttendanceService)->updateStatusesForCours($cours);
            } catch (\Throwable $e) {
                // Optionnel: loguer l'erreur
            }

            return response()->json([
                'message' => 'Présences enregistrées avec succès',
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
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
        if (! $cours) {
            return response()->json(['present' => 0, 'retard' => 0, 'absent' => 0, 'justifie' => 0]);
        }

        $counts = CoursPresence::where('cours_id', $cours->id)
            ->selectRaw('statut, COUNT(*) as c')
            ->groupBy('statut')
            ->pluck('c', 'statut');

        $teacher = EnseignantPresence::where('emploi_du_temps_id', $emploi->id)
            ->where('enseignant_id', Auth::guard('enseignants')->id() ?? Auth::id())
            ->first();

        return response()->json([
            'present' => (int) ($counts['present'] ?? 0),
            'retard' => (int) ($counts['retard'] ?? 0),
            'absent' => (int) ($counts['absent'] ?? 0),
            'justifie' => (int) ($counts['justifie'] ?? 0),
            'teacher' => $teacher ? ['statut' => $teacher->statut, 'commentaire' => $teacher->commentaire] : null,
        ]);
    }

    public function saveTeacherPresence(Request $request)
    {
        $request->validate([
            'emploi_du_temps_id' => 'required|exists:emploi_du_temps,id',
            'statut' => 'required|in:present,retard,absent',
            'commentaire' => 'nullable|string|max:500',
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
    // public function listePresence($cours_id, $date)
    // {
    //     $cours = Cours::findOrFail($cours_id);
    //     $groupe_id = $cours->groupe_id;

    //     $etudiants = Etudiant::where('groupe_id', $groupe_id)->get();
    //     $absents = Absence::where('cours_id', $cours_id)
    //         ->whereDate('date_absence', $date)
    //         ->pluck('etudiant_id')
    //         ->toArray();

    //     $liste_absents = $etudiants->whereIn('id', $absents)->values();
    //     $liste_presents = $etudiants->whereNotIn('id', $absents)->values();

    //     return response()->json([
    //         'absents' => $liste_absents,
    //         'presents' => $liste_presents,
    //     ]);
    // }
    public function listePresence($cours_id)
    {
        $cours = Cours::findOrFail($cours_id);
        $groupe_id = $cours->groupe_id;

        // Étudiants avec un statut enregistré (≠ present)
        $absents = Etudiant::where('groupe_id', $groupe_id)
            ->whereHas('coursPresences', function ($q) use ($cours_id) {
                $q->where('cours_id', $cours_id);
            })
            ->with(['coursPresences' => function ($q) use ($cours_id) {
                $q->where('cours_id', $cours_id);
            }])
            ->get();

        // Étudiants non enregistrés => considérés présents
        $presents = Etudiant::where('groupe_id', $groupe_id)
            ->whereDoesntHave('coursPresences', function ($q) use ($cours_id) {
                $q->where('cours_id', $cours_id);
            })
            ->get();

        return response()->json([
            'absents' => $absents,
            'presents' => $presents,
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
        if (! $cahier) {
            return response()->json(['message' => 'Cahier introuvable'], 404);
        }
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
            'notes' => 'required|string',
        ]);
        $cahier = \App\Models\CahierTexte::where('emploi_du_temps_id', $request->emploi_du_temps_id)->first();
        if (! $cahier) {
            return response()->json(['message' => 'Cahier introuvable'], 404);
        }
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
        // Récupération de l'emploi du temps
        $emploi = EmploiDuTemp::findOrFail($emploi_du_temps_id);
        $groupe_id = $emploi->group_id;

        // Récupérer l'année académique active
        $annee = AnneeScolaire::where('active', 1)->first()?->id;

        // Récupérer les étudiants du groupe via EtudiantGroup
        $etudiants = EtudiantGroup::where('group_id', $groupe_id)
            ->where('annee_scolaire_id', $annee)
            ->with('etudiant')
            ->get()
            ->pluck('etudiant');

        // Gestion de la date
        $sessionDate = $emploi->debut
            ? date('Y-m-d', strtotime($emploi->debut))
            : now()->toDateString();

        // Récupération ou création du cours du jour
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

        // Récupération des absents pour cette date
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
        $emploi = EmploiDuTemp::with(['group', 'owner'])->find($emploi_du_temps_id);
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
        if (! $emploi) {
            return response()->json([]);
        }
        $date = $emploi->debut ? date('Y-m-d', strtotime($emploi->debut)) : now()->toDateString();
        $cours = Cours::where('uv_id', $emploi->uv_id)
            ->where('groupe_id', $emploi->group_id)
            ->whereDate('date_cours', $date)
            ->first();
        if (! $cours) {
            return response()->json([]);
        }

        // Prefer new tri-state presence data if exists
        $pres = CoursPresence::where('cours_id', $cours->id)->get();
        if ($pres->isNotEmpty()) {
            $data = $pres->map(function ($p) {
                return [
                    'etudiant_id' => $p->etudiant_id,
                    'statut' => $p->statut,
                    'commentaire' => $p->commentaire,
                    'sanction' => optional($p->sanction)->description,
                    'needs_validation' => (bool) $p->needs_validation,
                ];
            });

            return response()->json($data);
        }

        // Fallback for legacy: mark absences
        $absences = Absence::where('cours_id', $cours->id)
            ->whereDate('date_absence', $date)
            ->get(['etudiant_id', 'motif']);

        $data = $absences->map(fn ($a) => [
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
