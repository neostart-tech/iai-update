<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Controllers\DashboardService;
use App\Models\Etudiant;
use App\Models\Filiere;
use App\Models\Group;
use App\Models\User;
use App\Models\UniteValeur;
use App\Models\Evaluation;
use App\Models\Note;
use App\Models\AnneeScolaire;
use App\Models\EmploiDuTemp;
use App\Models\Candidature;
use App\Models\Paiement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    protected DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Récupère toutes les statistiques pour le dashboard
     */
    public function index(Request $request): JsonResponse
    {
        $anneeScolaireId = $request->get('annee_scolaire_id', injectAnneeScolaireId());
        
        $statistics = [
            'cards' => $this->getMainCardsStats($anneeScolaireId),
            'repartition' => $this->getRepartitionStats($anneeScolaireId),
            'performance' => $this->getPerformanceStats($anneeScolaireId),
            'groups' => $this->getGroupsStats($anneeScolaireId),
            'todayCourses' => $this->getTodayCourses(),
            'deadlines' => $this->getUpcomingDeadlines(),
            'evolution' => $this->getEvolutionStats($anneeScolaireId),
            'financial' => $this->getFinancialStats($anneeScolaireId),
        ];

        return response()->json($statistics);
    }

    /**
     * Statistiques des cartes principales
     */
    private function getMainCardsStats(int $anneeScolaireId): array
    {
        $currentYear = AnneeScolaire::find($anneeScolaireId);
        $previousYear = AnneeScolaire::where('id', '<', $anneeScolaireId)
            ->orderBy('id', 'desc')
            ->first();

        // Étudiants
        $etudiantsCount = Etudiant::whereHas('etudiantGroups', function($q) use ($anneeScolaireId) {
            $q->where('annee_scolaire_id', $anneeScolaireId);
        })->count();

        $etudiantsPreviousCount = $previousYear ? Etudiant::whereHas('etudiantGroups', function($q) use ($previousYear) {
            $q->where('annee_scolaire_id', $previousYear->id);
        })->count() : $etudiantsCount;

        $etudiantsEvolution = $this->calculateEvolution($etudiantsCount, $etudiantsPreviousCount);

        // Filières actives
        $filieresCount = Filiere::whereHas('anneesScolaires', function($q) use ($anneeScolaireId) {
            $q->where('annee_scolaire_id', $anneeScolaireId);
        })->count();

        $newFilieres = Filiere::whereHas('anneesScolaires', function($q) use ($anneeScolaireId) {
            $q->where('annee_scolaire_id', $anneeScolaireId)
              ->where('created_at', '>=', now()->subYear());
        })->count();

        // Taux de réussite
        $successRate = $this->dashboardService->calculateSuccessRate($anneeScolaireId);
        $previousSuccessRate = $previousYear ? 
            $this->dashboardService->calculateSuccessRate($previousYear->id) : $successRate;
        $successEvolution = $this->calculateEvolution($successRate, $previousSuccessRate);

        // Enseignants
        $enseignantsCount = User::enseignants()
            ->whereHas('userUniteValeurs', function($q) use ($anneeScolaireId) {
                $q->where('annee_scolaire_id', $anneeScolaireId);
            })->count();

        $permanents = User::enseignants()
            ->whereNull('supervisor_type')
            ->whereHas('userUniteValeurs', function($q) use ($anneeScolaireId) {
                $q->where('annee_scolaire_id', $anneeScolaireId);
            })->count();

        return [
            'etudiants' => [
                'total' => $etudiantsCount,
                'evolution' => $etudiantsEvolution,
                'evolution_text' => $this->formatEvolution($etudiantsEvolution),
                'comparison_year' => $previousYear?->libelle ?? '2023'
            ],
            'filieres' => [
                'total' => $filieresCount,
                'new' => $newFilieres,
                'new_text' => "+{$newFilieres} nouvelle" . ($newFilieres > 1 ? 's' : '')
            ],
            'reussite' => [
                'total' => round($successRate, 1),
                'evolution' => $successEvolution,
                'evolution_text' => $this->formatEvolution($successEvolution),
                'comparison_period' => 'vs S1'
            ],
            'enseignants' => [
                'total' => $enseignantsCount,
                'permanents' => $permanents,
                'vacataires' => $enseignantsCount - $permanents
            ]
        ];
    }

    /**
     * Statistiques de répartition
     */
    private function getRepartitionStats(int $anneeScolaireId): array
    {
        // Répartition par cycle (Licence/Master)
        $repartitionCycle = Etudiant::whereHas('etudiantGroups', function($q) use ($anneeScolaireId) {
            $q->where('annee_scolaire_id', $anneeScolaireId);
        })
        ->with('etudiantGroups.group.niveau')
        ->get()
        ->groupBy(function($etudiant) {
            $niveau = $etudiant->etudiantGroups->first()?->group?->niveau;
            if ($niveau) {
                return $niveau->code === 'L' ? 'Licence' : 'Master';
            }
            return 'Non défini';
        })
        ->map(function($group) {
            return $group->count();
        });

        // Répartition par filière
        $repartitionFiliere = DB::table('etudiant_group')
            ->join('groups', 'etudiant_group.group_id', '=', 'groups.id')
            ->join('filiere_group', 'groups.id', '=', 'filiere_group.group_id')
            ->join('filieres', 'filiere_group.filiere_id', '=', 'filieres.id')
            ->where('etudiant_group.annee_scolaire_id', $anneeScolaireId)
            ->select('filieres.nom as filiere', DB::raw('count(distinct etudiant_group.etudiant_id) as total'))
            ->groupBy('filieres.nom')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return [
            'cycle' => [
                'licence' => $repartitionCycle['Licence'] ?? 0,
                'master' => $repartitionCycle['Master'] ?? 0,
                'total' => ($repartitionCycle['Licence'] ?? 0) + ($repartitionCycle['Master'] ?? 0)
            ],
            'filieres' => $repartitionFiliere
        ];
    }

    /**
     * Performance par filière
     */
    private function getPerformanceStats(int $anneeScolaireId): array
    {
        $filieres = Filiere::whereHas('anneesScolaires', function($q) use ($anneeScolaireId) {
            $q->where('annee_scolaire_id', $anneeScolaireId);
        })->get();

        $colors = ['bg-blue-500', 'bg-purple-500', 'bg-green-500', 'bg-orange-500', 'bg-pink-500', 'bg-indigo-500'];
        
        $performance = [];
        foreach ($filieres as $index => $filiere) {
            $tauxReussite = $this->dashboardService->calculateFiliereSuccessRate($filiere->id, $anneeScolaireId);
            $performance[] = [
                'id' => $filiere->id,
                'name' => $filiere->nom,
                'rate' => round($tauxReussite, 1),
                'color' => $colors[$index % count($colors)]
            ];
        }

        return $performance;
    }

    /**
     * Statistiques des groupes
     */
    private function getGroupsStats(int $anneeScolaireId): array
    {
        $groupsCount = Group::where('annee_scolaire_id', $anneeScolaireId)->count();
        
        $etudiantsCount = Etudiant::whereHas('etudiantGroups', function($q) use ($anneeScolaireId) {
            $q->where('annee_scolaire_id', $anneeScolaireId);
        })->count();

        $groupsWithCounts = Group::where('annee_scolaire_id', $anneeScolaireId)
            ->withCount(['etudiants' => function($q) use ($anneeScolaireId) {
                $q->wherePivot('annee_scolaire_id', $anneeScolaireId);
            }])
            ->get();

        $averagePerGroup = $groupsCount > 0 ? round($etudiantsCount / $groupsCount) : 0;
        
        $occupationRate = $this->calculateOccupationRate($groupsWithCounts);

        return [
            'total' => $groupsCount,
            'average_students' => $averagePerGroup,
            'occupation_rate' => $occupationRate,
            'groups_list' => $groupsWithCounts->take(5)->map(function($group) {
                return [
                    'id' => $group->id,
                    'nom' => $group->nom,
                    'etudiants_count' => $group->etudiants_count,
                    'niveau' => $group->niveau?->code ?? 'N/A'
                ];
            })
        ];
    }

    /**
     * Cours du jour
     */
    private function getTodayCourses(): array
    {
        $today = now()->startOfDay();
        $tomorrow = now()->endOfDay();

        $courses = EmploiDuTemp::with(['matiere', 'enseignant', 'salle', 'group'])
            ->whereBetween('date', [$today, $tomorrow])
            ->orderBy('heure_debut')
            ->get();

        $colors = [
            'Cours' => 'bg-blue-100 text-blue-800',
            'TD' => 'bg-green-100 text-green-800',
            'TP' => 'bg-orange-100 text-orange-800',
            'Examen' => 'bg-red-100 text-red-800'
        ];

        return $courses->map(function($course) use ($colors) {
            $type = $course->type_cours ?? 'Cours';
            return [
                'name' => $course->matiere?->nom ?? 'Cours',
                'time' => $course->heure_debut?->format('H:i') . '-' . $course->heure_fin?->format('H:i'),
                'type' => strtolower($type),
                'type_label' => $type,
                'badge_class' => $colors[$type] ?? 'bg-gray-100 text-gray-800',
                'professor' => $course->enseignant?->nom_complet,
                'room' => $course->salle?->nom,
                'group' => $course->group?->nom
            ];
        })->toArray();
    }

    /**
     * Prochaines échéances
     */
    private function getUpcomingDeadlines(): array
    {
        $deadlines = collect();

        // Évaluations à venir
        $evaluations = Evaluation::with(['matiere', 'group'])
            ->where('date', '>=', now())
            ->where('date', '<=', now()->addDays(30))
            ->orderBy('date')
            ->limit(5)
            ->get()
            ->map(function($evaluation) {
                $daysLeft = now()->diffInDays($evaluation->date);
                return [
                    'id' => 'eval-' . $evaluation->id,
                    'title' => $evaluation->type->value . ' - ' . ($evaluation->matiere?->nom ?? ''),
                    'date' => $evaluation->date->translatedFormat('d F Y'),
                    'days_left' => $daysLeft,
                    'days_text' => $daysLeft == 0 ? "Aujourd'hui" : ($daysLeft == 1 ? "Demain" : "Dans {$daysLeft} jours"),
                    'type' => 'exam',
                    'group' => $evaluation->group?->nom,
                    'time' => $evaluation->debut?->format('H:i') . '-' . $evaluation->fin?->format('H:i')
                ];
            });

        // Réunions/Conseils à venir
        $meetings = DB::table('evenements')
            ->where('date_debut', '>=', now())
            ->where('date_debut', '<=', now()->addDays(30))
            ->orderBy('date_debut')
            ->limit(3)
            ->get()
            ->map(function($meeting) {
                $daysLeft = now()->diffInDays($meeting->date_debut);
                return [
                    'id' => 'meet-' . $meeting->id,
                    'title' => $meeting->titre,
                    'date' => \Carbon\Carbon::parse($meeting->date_debut)->translatedFormat('d F Y'),
                    'days_left' => $daysLeft,
                    'days_text' => $daysLeft == 0 ? "Aujourd'hui" : ($daysLeft == 1 ? "Demain" : "Dans {$daysLeft} jours"),
                    'type' => 'meeting'
                ];
            });

        // Paiements en attente (pour RAF)
        $pendingPayments = Paiement::where('statut', 'en_attente')
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        if ($pendingPayments > 0) {
            $deadlines->push([
                'id' => 'payment-pending',
                'title' => 'Paiements en attente',
                'date' => $pendingPayments . ' paiement' . ($pendingPayments > 1 ? 's' : ''),
                'days_text' => 'À traiter',
                'type' => 'payment',
                'count' => $pendingPayments
            ]);
        }

        // Candidatures à traiter (pour DGA/Chargé clientèle)
        $pendingCandidatures = Candidature::whereNull('validation_date')
            ->count();

        if ($pendingCandidatures > 0) {
            $deadlines->push([
                'id' => 'candidatures-pending',
                'title' => 'Candidatures à valider',
                'date' => $pendingCandidatures . ' dossier' . ($pendingCandidatures > 1 ? 's' : ''),
                'days_text' => 'En attente',
                'type' => 'candidature'
            ]);
        }

        return $evaluations->concat($meetings)->concat($deadlines)->take(5)->values()->toArray();
    }

    /**
     * Évolution des inscriptions
     */
    private function getEvolutionStats(int $anneeScolaireId): array
    {
        $anneeScolaire = AnneeScolaire::find($anneeScolaireId);
        
        // Derniers 6 mois
        $months = collect(range(5, 0))->map(function($i) use ($anneeScolaire) {
            return now()->subMonths($i)->format('Y-m');
        });

        $evolution = [];
        foreach ($months as $month) {
            $count = Etudiant::whereHas('etudiantGroups', function($q) use ($anneeScolaireId, $month) {
                $q->where('annee_scolaire_id', $anneeScolaireId)
                  ->whereYear('created_at', substr($month, 0, 4))
                  ->whereMonth('created_at', substr($month, 5, 2));
            })->count();

            $evolution[] = [
                'month' => \Carbon\Carbon::createFromFormat('Y-m', $month)->translatedFormat('M'),
                'count' => $count
            ];
        }

        return $evolution;
    }

    /**
     * Statistiques financières (pour RAF/DGA)
     */
    private function getFinancialStats(int $anneeScolaireId): array
    {
        $totalPaiements = Paiement::whereHas('etudiant.etudiantGroups', function($q) use ($anneeScolaireId) {
            $q->where('annee_scolaire_id', $anneeScolaireId);
        })->sum('montant');

        $paiementsAttente = Paiement::where('statut', 'en_attente')
            ->whereHas('etudiant.etudiantGroups', function($q) use ($anneeScolaireId) {
                $q->where('annee_scolaire_id', $anneeScolaireId);
            })->sum('montant');

        $etudiantsAjour = Etudiant::whereHas('etudiantGroups', function($q) use ($anneeScolaireId) {
            $q->where('annee_scolaire_id', $anneeScolaireId);
        })->get()->filter(function($etudiant) {
            return $etudiant->estAjour();
        })->count();

        $totalEtudiants = Etudiant::whereHas('etudiantGroups', function($q) use ($anneeScolaireId) {
            $q->where('annee_scolaire_id', $anneeScolaireId);
        })->count();

        return [
            'total_paiements' => $totalPaiements,
            'paiements_attente' => $paiementsAttente,
            'etudiants_ajour' => $etudiantsAjour,
            'taux_ajour' => $totalEtudiants > 0 ? round(($etudiantsAjour / $totalEtudiants) * 100, 1) : 0
        ];
    }

    /**
     * Calcule l'évolution entre deux valeurs
     */
    private function calculateEvolution(float $current, float $previous): float
    {
        if ($previous == 0) return 0;
        return round((($current - $previous) / $previous) * 100, 1);
    }

    /**
     * Formate l'évolution en texte
     */
    private function formatEvolution(float $evolution): string
    {
        $sign = $evolution >= 0 ? '+' : '';
        return $sign . $evolution . '%';
    }

    /**
     * Calcule le taux d'occupation des groupes
     */
    private function calculateOccupationRate(Collection $groupsWithCounts): int
    {
        if ($groupsWithCounts->isEmpty()) return 0;
        
        $capaciteMax = 50; // Capacité maximale théorique par groupe
        $totalOccupation = $groupsWithCounts->sum(function($group) use ($capaciteMax) {
            return min(($group->etudiants_count / $capaciteMax) * 100, 100);
        });
        
        return round($totalOccupation / $groupsWithCounts->count());
    }
}