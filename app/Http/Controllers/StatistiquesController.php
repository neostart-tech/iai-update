<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use App\Models\EmploiDuTemp;
use App\Models\Etudiant;
use App\Models\Filiere;
use App\Models\Niveau;
use App\Models\Salle;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StatistiquesController extends Controller
{
    /**
     * Compte le nombre de filières pour l'année scolaire active.
     *
     * @return int
     */
    public function NbreFilieres(): int
    {
        $anneeActiveId = AnneeScolaire::where('active', true)->value('id');

        // Compte le nombre de filières liées à l'année scolaire active
        return Filiere::whereHas('anneesScolaires', function ($query) use ($anneeActiveId) {
            $query->where('annee_filiere.annee_scolaire_id', $anneeActiveId);
        })->count();
    }

    /**
     * Compte le nombre d'étudiants par niveau (Licence/Master) pour l'année scolaire active.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function NbreEtudiants()
    {
        $anneeActiveId = AnneeScolaire::where('active', true)->value('id');

        // Liste des codes de niveau pour Licence et Master
        $licenceCodes = ['L1', 'L2', 'L3'];
        $masterCodes = ['M1', 'M2', 'EM'];

        // Récupérer les IDs des niveaux Licence et Master
        $licenceNiveauIds = Niveau::whereIn('code', $licenceCodes)->pluck('id');
        $masterNiveauIds = Niveau::whereIn('code', $masterCodes)->pluck('id');

        // Compter les étudiants en Licence pour l'année active
        $nbreLicence = Etudiant::whereHas('etudiantGroups', function ($query) use ($anneeActiveId, $licenceNiveauIds) {
            $query->where('annee_scolaire_id', $anneeActiveId)
                ->whereIn('niveau_id', $licenceNiveauIds);
        })->count();

        // Compter les étudiants en Master pour l'année active
        $nbreMaster = Etudiant::whereHas('etudiantGroups', function ($query) use ($anneeActiveId, $masterNiveauIds) {
            $query->where('annee_scolaire_id', $anneeActiveId)
                ->whereIn('niveau_id', $masterNiveauIds);
        })->count();

        return response()->json([
            'licence' => $nbreLicence,
            'master' => $nbreMaster,
        ]);
    }

    /**
     * Compte le nombre de salles utilisées maintenant.
     *
     * @param string|null $dateHeureDeb (format : 'YYYY-MM-DD HH:MM:SS')
     * @param string|null $dateHeureFin (format : 'YYYY-MM-DD HH:MM:SS')
     * @return int
     */
    public function NbreSallesUtilisees(string $dateHeureDeb = null, string $dateHeureFin = null): int
    {
        $now = Carbon::now();
        $start = $dateHeureDeb ? Carbon::parse($dateHeureDeb) : $now;
        $end = $dateHeureFin ? Carbon::parse($dateHeureFin) : $now->copy()->addHour();

        $anneeActiveId = AnneeScolaire::where('active', true)->value('id');

        // Récupère les IDs des salles utilisées dans la plage horaire
        $sallesUtiliseesIds = EmploiDuTemp::where('annee_scolaire_id', $anneeActiveId)
            ->where(function ($query) use ($start, $end) {
                // Chevauchement : (debut_edt <= $end) AND (fin_edt >= $start)
                $query->where(function ($q) use ($start, $end) {
                    $q->where('debut', '<=', $end)
                        ->where('fin', '>=', $start);
                });
            })
            ->pluck('salle_id')
            ->unique();

        return $sallesUtiliseesIds->count();
    }

    /**
     * Compte le nombre de salles disponibles maintenant.
     *
     * @param string|null $dateHeureDeb (format : 'YYYY-MM-DD HH:MM:SS')
     * @param string|null $dateHeureFin (format : 'YYYY-MM-DD HH:MM:SS')
     * @return int
     */
    public function NbreSallesDispos(string $dateHeureDeb = null, string $dateHeureFin = null): int
    {
        $anneeActiveId = AnneeScolaire::where('active', true)->value('id');
        $totalSalles = Salle::where('annee_scolaire_id', $anneeActiveId)->count();
        $sallesUtilisees = $this->NbreSallesUtilisees($dateHeureDeb, $dateHeureFin);

        return $totalSalles - $sallesUtilisees;
    }

    /**
     * Compte le nombre total d'étudiants pour une année scolaire donnée (par défaut : l'année active).
     *
     * @param int|null $anneeScolaireId (optionnel) ID de l'année scolaire. Si null, utilise l'année active.
     * @return int
     */
    public function NbreTotalEtudiants(int $anneeScolaireId = null): int
    {
        // Si aucun ID n'est fourni, utilise l'année scolaire active
        $anneeId = $anneeScolaireId ?? AnneeScolaire::where('active', true)->value('id');

        // Compte le nombre d'étudiants inscrits dans des groupes pour cette année scolaire
        return Etudiant::whereHas('etudiantGroups', function ($query) use ($anneeId) {
            $query->where('annee_scolaire_id', $anneeId);
        })->count();
    }

    /**
     * Compte le nombre total d'enseignants.
     */
    public function NbreEnseignants(): int
    {
        $anneeActiveId = AnneeScolaire::where('active', true)->value('id');
        return \App\Models\User::enseignants()
            ->whereHas('userUniteValeurs', function($q) use ($anneeActiveId) {
                $q->where('annee_scolaire_id', $anneeActiveId);
            })->count();
    }

    /**
     * Compte le nombre d'évaluations pour l'année scolaire active.
     */
    public function NbreEvaluations(): int
    {
        $anneeActiveId = AnneeScolaire::where('active', true)->value('id');
        return \App\Models\Evaluation::whereHas('group', function ($query) use ($anneeActiveId) {
            $query->where('annee_scolaire_id', $anneeActiveId);
        })->count();
    }

    /**
     * Calcule le taux de présence moyen.
     */
    public function TauxPresenceMoyen(): float
    {
        $anneeActiveId = AnneeScolaire::where('active', true)->value('id');
        $totalPresences = \DB::table('presences')
            ->join('etudiant_group', 'presences.etudiant_id', '=', 'etudiant_group.etudiant_id')
            ->where('etudiant_group.annee_scolaire_id', $anneeActiveId)
            ->count();

        if ($totalPresences === 0) return 0;

        $presents = \DB::table('presences')
            ->join('etudiant_group', 'presences.etudiant_id', '=', 'etudiant_group.etudiant_id')
            ->where('etudiant_group.annee_scolaire_id', $anneeActiveId)
            ->whereIn('statut', ['present', 'retard'])
            ->count();

        return round(($presents / $totalPresences) * 100, 1);
    }

    /**
     * Compte le nombre de candidatures en attente.
     */
    public function NbreCandidaturesEnAttente(): int
    {
        return \App\Models\Candidature::whereNull('validation_date')->count();
    }

    /**
     * Récupère la tendance de l'assiduité sur les 6 derniers mois.
     */
    public function fetchPresenceTrend(): \Illuminate\Http\JsonResponse
    {
        $months = [];
        $data = [];
        $anneeActiveId = AnneeScolaire::where('active', true)->value('id');

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthName = $date->translatedFormat('M');
            $months[] = $monthName;

            $total = \DB::table('presences')
                ->join('etudiant_group', 'presences.etudiant_id', '=', 'etudiant_group.etudiant_id')
                ->where('etudiant_group.annee_scolaire_id', $anneeActiveId)
                ->whereYear('presences.created_at', $date->year)
                ->whereMonth('presences.created_at', $date->month)
                ->count();

            if ($total === 0) {
                $data[] = 0;
                continue;
            }

            $presents = \DB::table('presences')
                ->join('etudiant_group', 'presences.etudiant_id', '=', 'etudiant_group.etudiant_id')
                ->where('etudiant_group.annee_scolaire_id', $anneeActiveId)
                ->whereYear('presences.created_at', $date->year)
                ->whereMonth('presences.created_at', $date->month)
                ->whereIn('statut', ['present', 'retard'])
                ->count();

            $data[] = round(($presents / $total) * 100, 1);
        }

        return response()->json([
            'labels' => $months,
            'data' => $data
        ]);
    }

    /**
     * Récupère le top 5 des filières par nombre d'étudiants.
     */
    public function fetchTopFilieres(): \Illuminate\Http\JsonResponse
    {
        $anneeActiveId = AnneeScolaire::where('active', true)->value('id');

        $topFilieres = \DB::table('etudiant_group')
            ->join('filieres', 'etudiant_group.filiere_id', '=', 'filieres.id')
            ->where('etudiant_group.annee_scolaire_id', $anneeActiveId)
            ->select('filieres.nom as name', \DB::raw('count(distinct etudiant_group.etudiant_id) as total'))
            ->groupBy('filieres.id', 'filieres.nom')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return response()->json($topFilieres);
    }

    /**
     * Récupère les statistiques réelles des évaluations (taux de réussite et examens validés).
     */
    public function fetchEvaluationsStats(Request $request): \Illuminate\Http\JsonResponse
    {
        $anneeActiveId = AnneeScolaire::where('active', true)->value('id');
        $periodeId = $request->get('periode_id');

        // 1. Nombre d'évaluations "validées" (soumises par les enseignants)
        $valideesQuery = \App\Models\Evaluation::whereNotNull('correction_submission_date')
            ->whereHas('group', function ($query) use ($anneeActiveId) {
                $query->where('annee_scolaire_id', $anneeActiveId);
            });
        
        if ($periodeId) {
            $valideesQuery->where('semestre', $periodeId); // On suppose que la colonne est 'semestre'
        }
        
        $validees = $valideesQuery->count();

        // 2. Taux de réussite global (moyenne des notes >= 10 sur les évaluations soumises)
        $notesQuery = \App\Models\Note::whereHas('evaluation', function ($query) use ($anneeActiveId, $periodeId) {
            $query->whereNotNull('correction_submission_date')
                  ->whereHas('group', function ($q) use ($anneeActiveId) {
                      $q->where('annee_scolaire_id', $anneeActiveId);
                  });
            
            if ($periodeId) {
                $query->where('semestre', $periodeId);
            }
        });

        $totalNotes = $notesQuery->count();

        if ($totalNotes === 0) {
            return response()->json([
                'reussite' => 0,
                'validees' => $validees
            ]);
        }

        $reussites = $notesQuery->where('note', '>=', 10)->count();
        $tauxReussite = round(($reussites / $totalNotes) * 100, 1);

        return response()->json([
            'reussite' => $tauxReussite,
            'validees' => $validees
        ]);
    }

    /**
     * Récupère la période (semestre) actuelle.
     */
    public function fetchCurrentPeriode(): \Illuminate\Http\JsonResponse
    {
        $anneeActiveId = AnneeScolaire::where('active', true)->value('id');
        $now = now();
        
        $periode = \App\Models\Periode::where('annee_scolaire_id', $anneeActiveId)
            ->where('debut', '<=', $now)
            ->where('fin', '>=', $now)
            ->first();

        if (!$periode) {
            $periode = \App\Models\Periode::where('annee_scolaire_id', $anneeActiveId)
                ->orderByDesc('debut')
                ->first();
        }

        return response()->json($periode);
    }

    /**
     * Liste toutes les périodes de l'année scolaire active.
     */
    public function fetchPeriodes(): \Illuminate\Http\JsonResponse
    {
        $anneeActiveId = AnneeScolaire::where('active', true)->value('id');
        $periodes = \App\Models\Periode::where('annee_scolaire_id', $anneeActiveId)->get();
        return response()->json($periodes);
    }
}
