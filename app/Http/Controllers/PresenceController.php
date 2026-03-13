<?php

namespace App\Http\Controllers;

use App\Exports\PresencesExport;
use App\Models\Absence;
use App\Models\AnneeScolaire;
use App\Models\Cours;
use App\Models\CoursPresence;
use App\Models\EmploiDuTemp;
use App\Models\EnseignantPresence;
use App\Models\Etudiant;
use App\Models\EtudiantGroup;
use App\Models\Seance;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class PresenceController extends Controller
{
    /**
     * Récupérer les cours de l'utilisateur connecté
     */
    public function mesCours()
    {
        try {
            $user = request()->user();
            
            if (Auth::guard('etudiants')->check()) {
                $group = $user?->etudiantGroups?->first()?->group;
                
                if (!$group) {
                    return response()->json([]);
                }
                
                $cours = EmploiDuTemp::with([
                    'salle', 
                    'group', 
                    'group.niveau', 
                    'uv', 
                    'owner', 
                    'presences',
                    'enseignantPresence'
                ])
                ->where('group_id', $group->id)
                ->where('type_programme', 'Cours')
                ->get();
            } else {
                $cours = EmploiDuTemp::with([
                    'salle', 
                    'group', 
                    'group.niveau', 
                    'uv', 
                    'owner', 
                    'presences',
                    'enseignantPresence'
                ])
                ->where('owner_id', $user->id)
                ->where('type_programme', 'Cours')
                ->get();
            }

            return response()->json($cours);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors du chargement des cours',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Liste de tous les cours
     */
    public function index()
    {
        try {
            $cours = EmploiDuTemp::with([
                'salle', 
                'group',
                'group.niveau', 
                'uv', 
                'owner', 
                'presences',
                'enseignantPresence'
            ])
            ->where('type_programme', 'Cours')
            ->get();

            return response()->json($cours);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors du chargement des cours',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les étudiants d'un cours avec leurs présences pour une date
     */
  public function getEtudiantsAvecPresences(Request $request, $coursId)
{
    try {
        $date = $request->get('date', now()->toDateString());
        
        // Récupérer le cours avec son groupe
        $cours = EmploiDuTemp::with('group.etudiants')->findOrFail($coursId);
        
        if (!$cours->group) {
            return response()->json([
                'message' => 'Ce cours n\'est pas associé à un groupe'
            ], 400);
        }

        // Chercher si une séance existe pour cette date (SANS la créer)
        $seance = Seance::where('emploi_du_temps_id', $cours->id)
            ->whereDate('date_seance', $date)
            ->with('presences')
            ->first();

        // Récupérer tous les étudiants du groupe
        $etudiants = $cours->group->etudiants;

        // Formater les données
        $resultat = $etudiants->map(function($etudiant) use ($seance, $date) {
            // Chercher la présence de l'étudiant si une séance existe
            $presence = null;
            if ($seance) {
                $presence = $seance->presences
                    ->where('etudiant_id', $etudiant->id)
                    ->first();
            }
            
            return [
                'id' => $etudiant->id,
                'matricule' => $etudiant->matricule,
                'nom' => $etudiant->nom,
                'prenom' => $etudiant->prenom,
                'email' => $etudiant->email,
                'statut' => $presence->statut ?? 'absent',
                'heure_arrivee' => $presence->heure_arrivee ?? null,
                'heure_depart' => $presence->heure_depart ?? null,
                'minutes_retard' => $presence->minutes_retard ?? null,
                'commentaire' => $presence->commentaire ?? null,
                'participation' => $presence->participation ?? null,
                'attitude' => $presence->attitude ?? null,
                'observations_comportement' => $presence->observations_comportement ?? null,
                'points_attention' => $presence->points_attention ?? [],
                'points_positifs' => $presence->points_positifs ?? [],
                'a_signalement' => (bool) ($presence->a_signalement ?? false),
                'a_remonter_conseil' => (bool) ($presence->a_remonter_conseil ?? false),
                'presence_id' => $presence->id ?? null,
                'seance_id' => $seance->id ?? null,
                'date' => $date
            ];
        });

        // Déterminer si la séance est modifiable
        $estModifiable = true;
        if ($seance && $seance->presences->count() > 0) {
            $estModifiable = false;
        }

        return response()->json([
            'success' => true,
            'seance_existe' => $seance ? true : false,
            'seance' => $seance ? [
                'id' => $seance->id,
                'date' => $seance->date_seance->format('Y-m-d'),
                'statut' => $seance->statut,
                'nb_presences' => $seance->presences->count()
            ] : null,
            'etudiants' => $resultat,
            'total' => $resultat->count(),
            'est_modifiable' => $estModifiable,
            'statistiques' => [
                'presents' => $resultat->where('statut', 'present')->count(),
                'absents' => $resultat->whereIn('statut', ['absent', 'absent_justifie'])->count(),
                'retards' => $resultat->whereIn('statut', ['retard', 'retard_justifie'])->count(),
                'justifies' => $resultat->whereIn('statut', ['absent_justifie', 'retard_justifie'])->count(),
                'signalements' => $resultat->where('a_signalement', true)->count()
            ]
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors du chargement des étudiants',
            'error' => $e->getMessage()
        ], 500);
    }
}

   /**
 * Enregistrer les présences (avec création de la séance si nécessaire)
 */
public function enregistrerAbsences(Request $request)
{
    DB::beginTransaction();
    
    try {
        $validator = Validator::make($request->all(), [
            'emploi_du_temps_id' => 'required|exists:emploi_du_temps,id',
            'date' => 'nullable|date',
            'presences' => 'required|array|min:1',
            'presences.*.etudiant_id' => 'required|exists:etudiants,id',
            'presences.*.statut' => 'required|string|in:present,absent,retard,absent_justifie,retard_justifie,dispense,exclu_temporairement,malade,sortie_anticipee',
            'presences.*.heure_arrivee' => 'nullable|string|max:5',
            'presences.*.heure_depart' => 'nullable|string|max:5',
            'presences.*.commentaire' => 'nullable|string|max:500',
            'presences.*.participation' => 'nullable|string|in:excellente,bonne,moyenne,faible,nulle,non_concerné',
            'presences.*.attitude' => 'nullable|string|in:exemplaire,correcte,a_surveiller,problematique,perturbateur',
            'presences.*.observations_comportement' => 'nullable|string',
            'presences.*.points_attention' => 'nullable|array',
            'presences.*.points_positifs' => 'nullable|array',
            'presences.*.a_signalement' => 'nullable|boolean',
            'presences.*.a_remonter_conseil' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        $payload = $request->all();
        $emploiId = $payload['emploi_du_temps_id'];
        $date = $payload['date'] ?? now()->toDateString();

        // Récupérer le cours
        $cours = EmploiDuTemp::find($emploiId);

        // VÉRIFICATION CRITIQUE : Voir si une séance existe déjà AVEC des présences
        $seanceExistante = Seance::where('emploi_du_temps_id', $emploiId)
            ->whereDate('date_seance', $date)
            ->withCount('presences')
            ->first();

        // Si une séance existe déjà avec des présences, on interdit la modification
        if ($seanceExistante && $seanceExistante->presences_count > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de modifier : des présences ont déjà été enregistrées pour cette date'
            ], 403);
        }

        // Créer ou récupérer la séance (maintenant on peut la créer)
        $seance = Seance::firstOrCreate(
            [
                'emploi_du_temps_id' => $emploiId,
                'date_seance' => $date
            ],
            [
                'heure_debut_prevue' => $cours->debut,
                'heure_fin_prevue' => $cours->fin,
                'statut' => 'planifie'
            ]
        );

        // Vérifier si le jour est valide (pour cours récurrents)
        if (!$this->estJourValidePourCours($cours, $date)) {
            return response()->json([
                'success' => false,
                'message' => 'Cette date ne correspond pas aux jours de cours prévus'
            ], 403);
        }

        $presences = [];

        foreach ($payload['presences'] as $pr) {
            // Calculer les minutes de retard si nécessaire
            $minutesRetard = null;
            if (in_array($pr['statut'], ['retard', 'retard_justifie']) && isset($pr['heure_arrivee'])) {
                $minutesRetard = $this->calculerMinutesRetard($pr['heure_arrivee'], $cours->debut);
            }

            $presenceData = [
                'emploi_du_temps_id' => $emploiId,
                'seance_id' => $seance->id,
                'etudiant_id' => (int) $pr['etudiant_id'],
                'date' => $date,
                'statut' => $pr['statut'],
                'heure_arrivee' => $pr['heure_arrivee'] ?? null,
                'heure_depart' => $pr['heure_depart'] ?? null,
                'minutes_retard' => $minutesRetard,
                'commentaire' => $pr['commentaire'] ?? null,
                
                // Nouveaux champs comportement
                'participation' => $pr['participation'] ?? null,
                'attitude' => $pr['attitude'] ?? null,
                'observations_comportement' => $pr['observations_comportement'] ?? null,
                'points_attention' => isset($pr['points_attention']) ? json_encode($pr['points_attention']) : null,
                'points_positifs' => isset($pr['points_positifs']) ? json_encode($pr['points_positifs']) : null,
                'a_signalement' => $pr['a_signalement'] ?? false,
                'a_remonter_conseil' => $pr['a_remonter_conseil'] ?? false,
                
                // Validation
                'needs_validation' => in_array($pr['statut'], ['absent', 'retard']) ? true : false,
            ];

            $presences[] = $presenceData;
        }

        // Upsert des présences
        if (count($presences) > 0) {
            CoursPresence::upsert(
                $presences,
                ['emploi_du_temps_id', 'seance_id', 'etudiant_id'], // Clés uniques
                [
                    'statut', 
                    'heure_arrivee', 
                    'heure_depart',
                    'minutes_retard',
                    'commentaire',
                    'participation',
                    'attitude',
                    'observations_comportement',
                    'points_attention',
                    'points_positifs',
                    'a_signalement',
                    'a_remonter_conseil',
                    'needs_validation',
                ]
            );
        }

        // Mettre à jour le statut de la séance
        $seance->update([
            'statut' => 'termine',
            'heure_debut_reelle' => $cours->debut,
            'heure_fin_reelle' => now()
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Présences enregistrées avec succès',
            'count' => count($presences),
            'seance_id' => $seance->id
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        DB::rollBack();
        return response()->json([
            'message' => 'Erreur de validation',
            'errors' => $e->errors(),
        ], 422);
    } catch (\Exception $e) {
        DB::rollBack();
        // \Log::error('Erreur enregistrement présences: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de l\'enregistrement: ' . $e->getMessage()
        ], 500);
    }
}
    /**
     * Mettre à jour le comportement d'un étudiant pour une présence
     */
    public function updateComportement(Request $request, $presenceId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'participation' => 'nullable|string|in:excellente,bonne,moyenne,faible,nulle,non_concerné',
                'attitude' => 'nullable|string|in:exemplaire,correcte,a_surveiller,problematique,perturbateur',
                'observations_comportement' => 'nullable|string',
                'points_attention' => 'nullable|array',
                'points_positifs' => 'nullable|array',
                'a_signalement' => 'nullable|boolean',
                'a_remonter_conseil' => 'nullable|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $presence = CoursPresence::findOrFail($presenceId);
            
            $presence->update([
                'participation' => $request->participation,
                'attitude' => $request->attitude,
                'observations_comportement' => $request->observations_comportement,
                'points_attention' => $request->points_attention ? json_encode($request->points_attention) : null,
                'points_positifs' => $request->points_positifs ? json_encode($request->points_positifs) : null,
                'a_signalement' => $request->a_signalement ?? false,
                'a_remonter_conseil' => $request->a_remonter_conseil ?? false
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Comportement mis à jour',
                'data' => $presence
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer l'historique des présences d'un étudiant
     */
    public function historiqueEtudiant($etudiantId)
    {
        try {
            $presences = CoursPresence::with(['seance.emploiDuTemps.uv', 'seance.emploiDuTemps.owner'])
                ->where('etudiant_id', $etudiantId)
                ->orderBy('date', 'desc')
                ->get();

            $statistiques = [
                'total' => $presences->count(),
                'presents' => $presences->whereIn('statut', ['present'])->count(),
                'absents' => $presences->whereIn('statut', ['absent', 'absent_justifie'])->count(),
                'retards' => $presences->whereIn('statut', ['retard', 'retard_justifie'])->count(),
                'justifies' => $presences->whereIn('statut', ['absent_justifie', 'retard_justifie'])->count(),
                'taux_presence' => $presences->count() > 0 
                    ? round(($presences->where('statut', 'present')->count() / $presences->count()) * 100, 2)
                    : 0
            ];

            return response()->json([
                'success' => true,
                'statistiques' => $statistiques,
                'presences' => $presences->map(function($p) {
                    return [
                        'id' => $p->id,
                        'date' => $p->date->format('d/m/Y'),
                        'cours' => $p->seance?->emploiDuTemps?->uv?->nom ?? 'Cours',
                        'enseignant' => $p->seance?->emploiDuTemps?->owner?->nom ?? '',
                        'statut' => $p->statut,
                        'statut_libelle' => $p->statut_libelle,
                        'heure_arrivee' => $p->heure_arrivee,
                        'minutes_retard' => $p->minutes_retard,
                        'commentaire' => $p->commentaire,
                        'participation' => $p->participation,
                        'attitude' => $p->attitude,
                        'a_signalement' => $p->a_signalement
                    ];
                })
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les statistiques pour un cours
     */
    public function statistiquesCours($coursId)
    {
        try {
            $cours = EmploiDuTemp::findOrFail($coursId);
            
            $seances = Seance::where('emploi_du_temps_id', $coursId)
                ->with('presences')
                ->get();

            $stats = [
                'cours' => [
                    'nom' => $cours->uv?->nom,
                    'groupe' => $cours->group?->nom,
                    'total_seances' => $seances->count()
                ],
                'global' => [
                    'total_presences' => 0,
                    'moyenne_presents' => 0,
                    'moyenne_absents' => 0,
                    'moyenne_retards' => 0
                ],
                'par_seance' => []
            ];

            $totalPresents = 0;
            $totalAbsents = 0;
            $totalRetards = 0;
            $totalEtudiants = 0;

            foreach ($seances as $seance) {
                $presents = $seance->presences->whereIn('statut', ['present'])->count();
                $absents = $seance->presences->whereIn('statut', ['absent', 'absent_justifie'])->count();
                $retards = $seance->presences->whereIn('statut', ['retard', 'retard_justifie'])->count();
                $total = $seance->presences->count();

                $stats['par_seance'][] = [
                    'date' => $seance->date_seance->format('d/m/Y'),
                    'presents' => $presents,
                    'absents' => $absents,
                    'retards' => $retards,
                    'total' => $total,
                    'taux_presence' => $total > 0 ? round(($presents / $total) * 100, 2) : 0
                ];

                $totalPresents += $presents;
                $totalAbsents += $absents;
                $totalRetards += $retards;
                $totalEtudiants += $total;
            }

            $stats['global'] = [
                'total_presences' => $totalEtudiants,
                'total_presents' => $totalPresents,
                'total_absents' => $totalAbsents,
                'total_retards' => $totalRetards,
                'moyenne_presents' => $seances->count() > 0 ? round($totalPresents / $seances->count(), 2) : 0,
                'moyenne_absents' => $seances->count() > 0 ? round($totalAbsents / $seances->count(), 2) : 0,
                'moyenne_retards' => $seances->count() > 0 ? round($totalRetards / $seances->count(), 2) : 0,
                'taux_presence_moyen' => $totalEtudiants > 0 ? round(($totalPresents / $totalEtudiants) * 100, 2) : 0
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Utilitaires privés
     */
    private function estSeanceModifiable($seance, $cours)
    {
        // Si des présences existent déjà, non modifiable
        if ($seance->presences()->count() > 0) {
            return false;
        }

        // Si la séance est déjà terminée ou annulée
        if (in_array($seance->statut, ['termine', 'annule'])) {
            return false;
        }

        // Vérifier le jour pour les cours récurrents
        if ($cours->recurrence_type === 'hebdomadaire' && $cours->recurrence_days) {
            $joursMap = ['SU' => 0, 'MO' => 1, 'TU' => 2, 'WE' => 3, 'TH' => 4, 'FR' => 5, 'SA' => 6];
            $joursRecurrence = explode(',', $cours->recurrence_days);
            $joursNumeriques = array_map(fn($j) => $joursMap[$j] ?? null, $joursRecurrence);
            
            $jourSeance = Carbon::parse($seance->date_seance)->dayOfWeek;
            
            if (!in_array($jourSeance, $joursNumeriques)) {
                return false;
            }
        }

        return true;
    }

    private function calculerMinutesRetard($heureArrivee, $heureDebutCours)
    {
        if (!$heureArrivee || !$heureDebutCours) {
            return null;
        }

        $arrivee = strtotime($heureArrivee);
        $debut = strtotime($heureDebutCours);
        
        $retard = max(0, ($arrivee - $debut) / 60);
        
        return round($retard);
    }

    private function getSeanceDuJour($coursId)
    {
        return Seance::where('emploi_du_temps_id', $coursId)
            ->whereDate('date_seance', now()->toDateString())
            ->first();
    }

    private function estJourCours($cours)
    {
        if ($cours->recurrence_type !== 'hebdomadaire' || !$cours->recurrence_days) {
            return true;
        }

        $joursMap = ['SU' => 0, 'MO' => 1, 'TU' => 2, 'WE' => 3, 'TH' => 4, 'FR' => 5, 'SA' => 6];
        $joursRecurrence = explode(',', $cours->recurrence_days);
        $joursNumeriques = array_map(fn($j) => $joursMap[$j] ?? null, $joursRecurrence);
        
        $jourActuel = now()->dayOfWeek;
        
        return in_array($jourActuel, $joursNumeriques);
    }

    private function creerSeancePlanifiee($cours)
    {
        return Seance::firstOrCreate(
            [
                'emploi_du_temps_id' => $cours->id,
                'date_seance' => now()->toDateString()
            ],
            [
                'heure_debut_prevue' => $cours->debut,
                'heure_fin_prevue' => $cours->fin,
                'statut' => 'planifie'
            ]
        );
    }


    public function listeSeances($coursId)
{
    try {
        $seances = Seance::where('emploi_du_temps_id', $coursId)
            ->withCount('presences')
            ->orderBy('date_seance', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'seances' => $seances->map(function($seance) {
                return [
                    'id' => $seance->id,
                    'date_seance' => $seance->date_seance->format('Y-m-d'),
                    'date_formatee' => $seance->date_seance->format('d/m/Y'),
                    'statut' => $seance->statut,
                    'presences_count' => $seance->presences_count,
                    'est_terminee' => $seance->estTerminee(),
                    'est_annulee' => $seance->estAnnulee()
                ];
            })
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Vérifier si une date est valide pour un cours récurrent
 */
private function estJourValidePourCours($cours, $date)
{
    // Si le cours n'est pas récurrent, toujours valide
    if ($cours->recurrence_type !== 'hebdomadaire' || !$cours->recurrence_days) {
        return true;
    }

    // Mapper les codes de jours
    $joursMap = [
        'SU' => 0, // Dimanche
        'MO' => 1, // Lundi
        'TU' => 2, // Mardi
        'WE' => 3, // Mercredi
        'TH' => 4, // Jeudi
        'FR' => 5, // Vendredi
        'SA' => 6  // Samedi
    ];

    // Récupérer le jour de la semaine pour la date donnée
    $jourSemaine = Carbon::parse($date)->dayOfWeek;
    
    // Récupérer les jours prévus pour le cours
    $joursPrevu = explode(',', $cours->recurrence_days);
    $joursNumeriques = array_map(fn($j) => $joursMap[$j] ?? null, $joursPrevu);
    
    return in_array($jourSemaine, $joursNumeriques);
}


/**
 * Récupérer la liste des alertes
 */
public function getAlertes()
{
    try {
        // Alertes pour absences répétées (plus de 3 absences non justifiées)
        $etudiantsAbsences = DB::table('presences')
            ->select('etudiant_id', DB::raw('count(*) as total_absences'))
            ->whereIn('statut', ['absent', 'retard'])
            ->where('needs_validation', true)
            ->groupBy('etudiant_id')
            ->having('total_absences', '>=', 3)
            ->get();

        $alertes = collect();
        
        foreach ($etudiantsAbsences as $item) {
            $etudiant = Etudiant::find($item->etudiant_id);
            if ($etudiant) {
                $alertes->push([
                    'id' => 'abs_' . $item->etudiant_id,
                    'type' => 'absence',
                    'etudiant_id' => $etudiant->id,
                    'etudiant_nom' => $etudiant->prenom . ' ' . $etudiant->nom,
                    'message' => $item->total_absences . ' absences/retards non justifiés',
                    'niveau' => $item->total_absences >= 5 ? 'rouge' : 'orange',
                    'date' => now()->format('Y-m-d'),
                    'lue' => false
                ]);
            }
        }
        
        // Alertes pour signalements
        $signalements = CoursPresence::where('a_signalement', true)
            ->with('etudiant')
            ->get();
            
        foreach ($signalements as $signalement) {
            $alertes->push([
                'id' => 'sig_' . $signalement->id,
                'type' => 'comportement',
                'etudiant_id' => $signalement->etudiant->id,
                'etudiant_nom' => $signalement->etudiant->prenom . ' ' . $signalement->etudiant->nom,
                'message' => 'Signalement comportemental',
                'niveau' => 'jaune',
                'date' => $signalement->date->format('Y-m-d'),
                'lue' => false
            ]);
        }
        
        // Compter les non lues (vous pouvez stocker dans une table si besoin)
        $alertesNonLues = $alertes->count(); // Ou filtrez selon votre logique
        
        return response()->json([
            'success' => true,
            'alertes' => $alertes->values(),
            'non_lues' => $alertesNonLues
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur: ' . $e->getMessage()
        ], 500);
    }
}

public function exportPresencesCours($coursId, Request $request)
{
    $date = $request->get('date', now()->toDateString());
    
    // Récupérer la séance
    $seance = Seance::where('emploi_du_temps_id', $coursId)
        ->whereDate('date_seance', $date)
        ->first();
    
    if (!$seance) {
        return response()->json(['message' => 'Aucune séance trouvée pour cette date'], 404);
    }
    
    // Récupérer les présences
    $presences = CoursPresence::where('seance_id', $seance->id)
        ->with('etudiant')
        ->get();
    
    $emploi = EmploiDuTemp::find($coursId);
    
    return Excel::download(
        new PresencesExport($presences, $emploi, $seance),
        'presences_' . $date . '.xlsx'
    );
}
}