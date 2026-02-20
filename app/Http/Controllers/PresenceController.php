<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\AnneeScolaire;
use App\Models\Cours;
use App\Models\CoursPresence;
use App\Models\EmploiDuTemp;
use App\Models\EnseignantPresence;
use App\Models\Etudiant;
use App\Models\EtudiantGroup;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PresenceController extends Controller
{

    public function mesCours()
    {
        $cours = EmploiDuTemp::with(['salle', 'group', 'uv', 'owner','presences'])
            ->where('owner_id', request()->user()->id)
            ->where('type_programme', 'Cours')
            ->get();
        return response()->json($cours);
    }



    public function enregistrerAbsences(Request $request)
    {
        try {
            $payload = $request->all();

            $emploiId = $payload['emploi_du_temps_id'] ?? null;
            $sessionDate = now()->toDateString();

            if ($emploiId) {
                $emploi = EmploiDuTemp::find($emploiId);
                if (! $emploi) {
                    return response()->json(['message' => 'Séance introuvable'], 404);
                }
                $sessionDate = $emploi->debut ? date('Y-m-d', strtotime($emploi->debut)) : $sessionDate;
            }

            // Validation avec ajout de heure_arrivee
            $request->validate([
                'presences' => 'required|array',
                'presences.*.etudiant_id' => 'required|exists:etudiants,id',
                'presences.*.statut' => 'required|string|in:present,retard,absent,justifie',
                'presences.*.heure_arrivee' => 'nullable|string|max:5', // Format HH:MM
                'presences.*.commentaire' => 'nullable|string|max:500',
                // 'presences.*.sanction' => 'nullable|string|max:500',
            ]);

            $presences = [];
            $presentIds = [];

            foreach ($payload['presences'] as $pr) {
                // Préparer les données communes
                $presenceData = [
                    'emploi_du_temps_id' => $emploiId,
                    'etudiant_id' => (int) $pr['etudiant_id'],
                    'date' => $sessionDate,
                    'statut' => $pr['statut'],
                    'commentaire' => $pr['commentaire'] ?? null,
                    // 'sanction' => $pr['sanction'] ?? null,
                ];

                // Ajouter l'heure d'arrivée seulement si elle est fournie (pour présent et retard)
                if (isset($pr['heure_arrivee']) && !empty($pr['heure_arrivee'])) {
                    $presenceData['heure_arrivee'] = $pr['heure_arrivee'];
                } else {
                    $presenceData['heure_arrivee'] = null;
                }

                // Gérer needs_validation pour les absences
                if ($pr['statut'] === 'absent') {
                    $presenceData['needs_validation'] = true;
                } else {
                    $presenceData['needs_validation'] = false;
                }

                $presences[] = $presenceData;

                // Pour compter les présents 
                if ($pr['statut'] === 'present') {
                    $presentIds[] = (int) $pr['etudiant_id'];
                }
            }

            // Upsert des présences
            if (count($presences) > 0) {
                CoursPresence::upsert(
                    $presences,
                    ['emploi_du_temps_id', 'etudiant_id', 'date'], // Clés uniques
                    ['statut', 'heure_arrivee', 'commentaire', 'needs_validation', 'sanction'] // Champs à mettre à jour
                );
            }

            // Mettre à jour les statuts globaux si nécessaire
            try {
                (new AttendanceService)->updateStatusesForEmploi($emploiId, $sessionDate);
            } catch (\Throwable $e) {
                \Log::error('Erreur mise à jour statuts: ' . $e->getMessage());
            }

            return response()->json([
                'message' => 'Présences enregistrées avec succès',
                'count' => count($presences)
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Erreur de validation',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Erreur enregistrement présences: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erreur lors de l\'enregistrement: ' . $e->getMessage()
            ], 500);
        }
    }


    public function saveTeacherPresence(Request $request)
    {
        $request->validate([
            'emploi_du_temps_id' => 'required|exists:emploi_du_temps,id',
            'statut' => 'required|in:present,retard,absent',
            'commentaire' => 'nullable|string|max:500',
        ]);
        $enseignantId = request()->user()->id() ?? Auth::id();
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
