<?php

namespace App\Services;

use App\Models\AnneeScolaire;
use App\Models\FraisEtudiant;
use App\Models\Paiement;
use App\Models\Depense;
use App\Models\Niveau;
use App\Models\Filiere;
use App\Models\Etudiant;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FinanceService
{
    protected $anneeId;

    public function __construct($anneeId = null)
    {
        $this->anneeId = $anneeId ?? getAnneeScolaireId();
    }

    /**
     * Structure complète pour le Dashboard
     */
    public function getFullDashboardData($periode = 'annee', $dateDebut = null, $dateFin = null)
    {
        $ca = $this->getAnalyseCA($periode, $dateDebut, $dateFin);
        $previsionsTotal = $this->getPrevisionsTotales();

        $fraisInscriptionActif = \App\Models\FraisInscription::where('active', true)
            ->where('annee_scolaire_id', $this->anneeId)
            ->first();
        $totalEtudiants = Etudiant::whereHas('etudiantGroups', fn($q) => $q->where('annee_scolaire_id', $this->anneeId))->count();
        $previsionsInscriptions = $fraisInscriptionActif ? $totalEtudiants * $fraisInscriptionActif->montant : 0;

        return [
            'resume' => [
                // Anciennes clés maintenues uniquement pour SCD (fixées à SCOLARITÉ seule)
                'montant_total_a_payer' => $previsionsTotal,
                'montant_collecte' => $ca['scolarite'], // Correction : Scolarité uniquement
                'montant_restant' => max(0, $previsionsTotal - $ca['scolarite']),
                'taux_collecte' => $previsionsTotal > 0 ? round(($ca['scolarite'] / $previsionsTotal) * 100, 2) : 0,
                
                // Nouvelles clés explicites séparées
                'scolarite_a_payer' => $previsionsTotal,
                'scolarite_collecte' => $ca['scolarite'],
                'scolarite_restant' => max(0, $previsionsTotal - $ca['scolarite']),
                'scolarite_taux' => $previsionsTotal > 0 ? round(($ca['scolarite'] / $previsionsTotal) * 100, 2) : 0,

                'inscription_a_payer' => $previsionsInscriptions,
                'inscription_collecte' => $ca['inscription'],
                'inscription_restant' => max(0, $previsionsInscriptions - $ca['inscription']),
                'inscription_taux' => $previsionsInscriptions > 0 ? round(($ca['inscription'] / $previsionsInscriptions) * 100, 2) : 0,

                // Informations globales
                'grand_total_collecte' => $ca['total_global'],
                'ca_inscription' => $ca['inscription'],
                'ca_scolarite' => $ca['scolarite'],
                'ca_abandons' => $ca['abandons'],
                'total_etudiants' => $totalEtudiants,
                'periode' => $this->formatPeriodeLabel($periode, $dateDebut, $dateFin)
            ],
            'evolution_paiements' => $this->getEvolutionPaiements($periode, $dateDebut, $dateFin),
            'repartition_statuts' => $this->getRepartitionStatuts(),
            'statistiques_filieres' => $this->getStatsFilieres(),
            'statistiques_niveaux' => $this->getIndicateursParNiveau(),
            'encaissements_par_niveau' => $this->getEncaissementsParNiveau($periode, $dateDebut, $dateFin),
            'top_performers' => $this->getTopPerformers(),
            'etudiants_en_retard' => $this->getEtudiantsEnRetard(10),
            'retards_par_niveau' => $this->getRetardsParNiveau(),
            'effectifs_par_niveau' => $this->getEffectifsParNiveau(),
            'paiements_recents' => $this->getPaiementsRecents(10),
            'previsions' => $this->getProchainesPrevisions(6),
            'series_graphique' => $this->getSeriesDashboard(), // Les 3 courbes
            'extra_kpis' => $this->getExtraKPIs()
        ];
    }

    private function getAnalyseCA($periode, $dateDebut, $dateFin)
    {
        $query = Paiement::where('status', 'valide');
        $this->applyTimeFilter($query, $periode, $dateDebut, $dateFin);

        $totalEncaisse = (clone $query)->sum('montant');
        
        $caInscriptions = (clone $query)->where('nature_paiement', 'inscription')->sum('montant');
        
        $caAbandons = (clone $query)->whereHas('etudiant.etudiantGroups', function($q) {
            $q->where('annee_scolaire_id', $this->anneeId)->where('statut_scolaire', 'abandon');
        })->sum('montant');

        $caScolarite = $totalEncaisse - $caInscriptions;

        return [
            'inscription' => $caInscriptions,
            'scolarite' => $caScolarite - $caAbandons,
            'abandons' => $caAbandons,
            'total_global' => $totalEncaisse
        ];
    }

    private function getPrevisionsTotales()
    {
        // On récupère la somme réelle attendue (après bourses) pour tous les dossiers actifs
        return FraisEtudiant::where('annee_scolaire_id', $this->anneeId)
            ->where('est_en_abandon', false)
            ->sum('montant_apres_bourse');
    }

    private function getEvolutionPaiements($periode, $dateDebut, $dateFin)
    {
        return Paiement::where('status', 'valide')
            ->select(DB::raw('DATE(date_paiement) as date'), DB::raw('SUM(montant) as montant'), DB::raw('COUNT(*) as nombre'))
            ->whereYear('date_paiement', Carbon::now()->year)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($item) => [
                'label' => Carbon::parse($item->date)->format('d/m'),
                'montant' => (float)$item->montant,
                'nombre' => $item->nombre
            ]);
    }

    private function getRepartitionStatuts()
    {
        $dossiers = FraisEtudiant::where('annee_scolaire_id', $this->anneeId)
            ->with(['echeances'])
            ->get();
        
        $stats = [
            'solde' => 0,
            'a_jour' => 0,
            'retard' => 0,
            'abandon' => 0
        ];

        foreach ($dossiers as $d) {
            if ($d->est_en_abandon || $d->statut === 'abandon') {
                $stats['abandon']++;
                continue;
            }

            // Calcul asynchrone du total payé pour la scolarité
            $paye = Paiement::where('etudiant_id', $d->etudiant_id)
                ->where('status', 'valide')
                ->where(function($q){
                    $q->where('nature_paiement', '!=', 'inscription')->orWhereNull('nature_paiement');
                })->sum('montant');

            if ($paye >= $d->montant_apres_bourse) {
                $stats['solde']++;
                continue;
            }

            // Vérifier s'il y a une échéance passée non couverte par le montant payé
            $expectedToDate = 0;
            $estEnRetard = false;
            foreach ($d->echeances->sortBy('date_limite') as $ech) {
                if ($ech->date_limite < now()) {
                    $expectedToDate += $ech->montant;
                }
            }
            if ($paye < $expectedToDate) {
                $estEnRetard = true;
            }

            if ($estEnRetard || $d->statut === 'en_retard') {
                $stats['retard']++;
            } else {
                $stats['a_jour']++;
            }
        }

        return $stats;
    }

    private function getStatsFilieres()
    {
        return Filiere::all()->map(function($filiere) {
            // Prévisions réelles SC uniquement
            $prev = FraisEtudiant::where('annee_scolaire_id', $this->anneeId)
                ->where('est_en_abandon', false)
                ->whereHas('etudiant.etudiantGroups', fn($q) => $q->where('filiere_id', $filiere->id)->where('annee_scolaire_id', $this->anneeId))
                ->sum('montant_apres_bourse');
            
            // Paiements scolarité
            $paye = Paiement::where('status', 'valide')
                ->where(function($q){
                    $q->where('nature_paiement', '!=', 'inscription')->orWhereNull('nature_paiement');
                })
                ->whereHas('etudiant.etudiantGroups', fn($q) => $q->where('filiere_id', $filiere->id)->where('annee_scolaire_id', $this->anneeId))
                ->sum('montant');

            return [
                'filiere_nom' => $filiere->nom,
                'filiere_code' => $filiere->code,
                'nombre_etudiants' => Etudiant::whereHas('etudiantGroups', fn($q) => $q->where('filiere_id', $filiere->id)->where('annee_scolaire_id', $this->anneeId))->count(),
                'total_a_payer' => $prev,
                'total_paye' => $paye,
                'total_restant' => max(0, $prev - $paye),
                'taux_collecte' => $prev > 0 ? round(($paye / $prev) * 100, 1) : 0
            ];
        });
    }

    public function getIndicateursParNiveau()
    {
        return Niveau::all()->map(function($niveau) {
            // Prévisions réelles basées sur les dossiers FraisEtudiant
            $prev = FraisEtudiant::where('annee_scolaire_id', $this->anneeId)
                ->where('est_en_abandon', false)
                ->whereHas('fraisScolarite', fn($q) => $q->where('niveau_id', $niveau->id))
                ->sum('montant_apres_bourse');
            
            $paye = Paiement::where('status', 'valide')
                ->whereHas('etudiant.etudiantGroups', fn($q) => $q->where('niveau_id', $niveau->id)->where('annee_scolaire_id', $this->anneeId))
                ->sum('montant');

            return [
                'nom' => $niveau->libelle,
                'code' => $niveau->code ?? $niveau->libelle,
                'previsions' => $prev,
                'encaisse' => $paye,
                'reste' => max(0, $prev - $paye),
                'taux_recouvrement' => $prev > 0 ? round(($paye / $prev) * 100, 1) : 0
            ];
        });
    }

    /**
     * Encaissements séparés inscription/scolarité par niveau
     */
    public function getEncaissementsParNiveau($periode = 'annee', $dateDebut = null, $dateFin = null)
    {
        return Niveau::all()->map(function($niveau) use ($periode, $dateDebut, $dateFin) {
            $queryBase = Paiement::where('status', 'valide')
                ->whereHas('etudiant.etudiantGroups', fn($q) =>
                    $q->where('niveau_id', $niveau->id)->where('annee_scolaire_id', $this->anneeId)
                );
            $this->applyTimeFilter($queryBase, $periode, $dateDebut, $dateFin);

            $totalEncaisse  = (clone $queryBase)->sum('montant');
            $inscription    = (clone $queryBase)->where('nature_paiement', 'inscription')->sum('montant');
            $scolarite      = $totalEncaisse - $inscription;

            // Prévisions réelles basées sur les dossiers FraisEtudiant
            $previsions = FraisEtudiant::where('annee_scolaire_id', $this->anneeId)
                ->where('est_en_abandon', false)
                ->whereHas('fraisScolarite', fn($q) => $q->where('niveau_id', $niveau->id))
                ->sum('montant_apres_bourse');

            return [
                'nom' => $niveau->libelle,
                'total' => $totalEncaisse,
                'inscription' => $inscription,
                'scolarite' => $scolarite,
                'previsions' => $previsions,
                'rar' => max(0, $previsions - $totalEncaisse),
                'taux_recouvrement' => $previsions > 0 ? round(($totalEncaisse / $previsions) * 100, 1) : 0,
            ];
        });
    }

    private function getTopPerformers()
    {
        return Etudiant::withSum(['paiements' => function($q) {
                $q->where('status', 'valide')
                  ->where(function($sub){
                      $sub->where('nature_paiement', '!=', 'inscription')->orWhereNull('nature_paiement');
                  });
            }], 'montant')
            ->orderByDesc('paiements_sum_montant')
            ->take(5)
            ->get()
            ->map(fn($e) => [
                'nom' => $e->nom,
                'prenom' => $e->prenom,
                'matricule' => $e->matricule,
                'total_paye' => $e->paiements_sum_montant ?? 0,
                'nombre_paiements' => \App\Models\Paiement::where('etudiant_id', $e->id)->where('status','valide')
                                    ->where(function($sq){ $sq->where('nature_paiement', '!=', 'inscription')->orWhereNull('nature_paiement'); })->count()
            ]);
    }

    public function getExtraKPIs()
    {
        // 1. Encaissement abandon
        $caAbandons = Paiement::where('status', 'valide')->whereHas('etudiant.etudiantGroups', function($q) {
            $q->where('annee_scolaire_id', $this->anneeId)->where('statut_scolaire', 'abandon');
        })->sum('montant');
        
        $totalEncaisse = Paiement::where('status', 'valide')->whereYear('date_paiement', Carbon::now()->year)->sum('montant');
        $tauxAbandon = $totalEncaisse > 0 ? round(($caAbandons / $totalEncaisse) * 100, 1) : 0;

        // 2. Retard de paiement
        $retards = FraisEtudiant::where('annee_scolaire_id', $this->anneeId)
            ->where('statut', 'en_retard');
        
        $montantRetards = $retards->sum(DB::raw('montant_apres_bourse - (SELECT COALESCE(SUM(montant), 0) FROM paiements WHERE paiements.etudiant_id = frais_etudiants.etudiant_id AND paiements.status = "valide")'));
        $nbreEtudiantsRetard = $retards->count();

        // 3. Non échues
        // Montant non échu = Total Prévu - (Total Payé + Montant Retards) en gros.
        $totalPrevu = $this->getPrevisionsTotales();
        $totalPayeGlobal = Paiement::where('status', 'valide')->whereHas('etudiant.etudiantGroups', fn($q) => $q->where('annee_scolaire_id', $this->anneeId))->sum('montant');
        $nonEchues = max(0, $totalPrevu - $totalPayeGlobal - $montantRetards);

        return [
            'encaissement_abandon' => [
                'montant' => $caAbandons,
                'taux' => $tauxAbandon
            ],
            'retard_paiement' => [
                'montant' => $montantRetards,
                'nombre_etudiants' => $nbreEtudiantsRetard
            ],
            'non_echues' => [
                'montant' => $nonEchues
            ]
        ];
    }

    public function getRecouvrementJournalierParNiveau($dateFin)
    {
        $end = Carbon::parse($dateFin);
        $start = (clone $end)->subDays(6); // 7 days total including endDate

        $dates = [];
        for ($i = 0; $i <= 6; $i++) {
            $d = (clone $start)->addDays($i);
            $dates[] = $d->format('Y-m-d');
        }

        $niveaux = Niveau::all();
        $results = [];

        foreach ($niveaux as $niveau) {
            $row = [
                'niveau_id' => $niveau->id,
                'niveau_nom' => $niveau->libelle,
                'total_semaine' => 0,
                'jours' => []
            ];

            foreach ($dates as $date) {
                $montantJour = Paiement::where('status', 'valide')
                    ->where(function($q) { $q->where('nature_paiement', '!=', 'inscription')->orWhereNull('nature_paiement'); })
                    ->whereDate('date_paiement', $date)
                    ->whereHas('etudiant.etudiantGroups', fn($q) => 
                        $q->where('niveau_id', $niveau->id)->where('annee_scolaire_id', $this->anneeId)
                    )->sum('montant');

                $row['jours'][$date] = $montantJour;
                $row['total_semaine'] += $montantJour;
            }
            $results[] = $row;
        }

        return [
            'dates' => $dates,
            'lignes' => $results
        ];
    }

    public function getRetardsParNiveau()
    {
        return Niveau::all()->map(function($niveau) {
            $dossiers = FraisEtudiant::with(['echeances'])
                ->where('annee_scolaire_id', $this->anneeId)
                ->where('est_en_abandon', false)
                ->whereHas('fraisScolarite', fn($q) => $q->where('niveau_id', $niveau->id))
                ->get();

            $montantRetardsTotal = 0;
            $nombreEtudiants = 0;

            foreach ($dossiers as $f) {
                $expectedToDate = 0;
                foreach ($f->echeances as $ech) {
                    if ($ech->date_limite < now()) {
                        $expectedToDate += $ech->montant;
                    }
                }

                if ($expectedToDate > 0) {
                    $paye = \App\Models\Paiement::where('etudiant_id', $f->etudiant_id)->where('status', 'valide')
                        ->where(function($q) { $q->where('nature_paiement', '!=', 'inscription')->orWhereNull('nature_paiement'); })
                        ->sum('montant');
                        
                    if ($paye < $expectedToDate) {
                        $montantRetardsTotal += max(0, $f->montant_apres_bourse - $paye);
                        $nombreEtudiants++;
                    }
                }
            }

            return [
                'nom' => $niveau->libelle,
                'montant_retard' => $montantRetardsTotal,
                'nombre_etudiants' => $nombreEtudiants
            ];
        });
    }

    public function getEffectifsParNiveau()
    {
        return Niveau::all()->map(function($niveau) {
            // Abandons
            $abandons = \App\Models\Group::where('niveau_id', $niveau->id)
                ->where('annee_scolaire_id', $this->anneeId)
                ->withCount(['etudiants' => function($q) {
                    $q->where('etudiant_group.statut_scolaire', 'abandon');
                }])->get()->sum('etudiants_count');

            // Actifs (Inscrits - Abandons, ou ceux qui ont un fraisEtudiant non en abandon)
            $actifs = FraisEtudiant::where('annee_scolaire_id', $this->anneeId)
                ->where('est_en_abandon', false)
                ->whereHas('fraisScolarite', fn($q) => $q->where('niveau_id', $niveau->id))
                ->count();

            // Si le nombre d'abandons n'est pas fiable via group, on le prend sur FraisEtudiant
            $abandonsFrais = FraisEtudiant::where('annee_scolaire_id', $this->anneeId)
                ->where('est_en_abandon', true)
                ->whereHas('fraisScolarite', fn($q) => $q->where('niveau_id', $niveau->id))
                ->count();

            return [
                'nom' => $niveau->libelle,
                'actifs' => $actifs,
                'abandons' => max($abandons, $abandonsFrais)
            ];
        });
    }

    public function getSuiviMensuelParNiveau($mois, $annee)
    {
        // Si mois est un tableau, on boucle
        if (is_array($mois)) {
            $allResults = [];
            foreach ($mois as $m) {
                // $m = ['mois' => 1, 'annee' => 2024]
                $allResults[] = [
                    'periode' => $m,
                    'donnees' => $this->getOneMonthSuivi($m['mois'], $m['annee'])
                ];
            }
            return $allResults;
        }

        return $this->getOneMonthSuivi($mois, $annee);
    }

    private function getOneMonthSuivi($mois, $annee)
    {
        $startDate = \Carbon\Carbon::createFromDate($annee, $mois, 1)->startOfMonth();
        $endDate = (clone $startDate)->endOfMonth();

        // M-1
        $endPrevMonth = (clone $startDate)->subDay()->endOfMonth();

        $niveaux = \App\Models\Niveau::all();
        $results = [];

        foreach ($niveaux as $niveau) {
            // Prevision = Echeances of this specific month
            $previsionMois = (float) \Illuminate\Support\Facades\DB::table('echeances')
                ->join('frais_etudiants', 'frais_etudiants.id', '=', 'echeances.frais_etudiant_id')
                ->join('etudiants', 'etudiants.id', '=', 'frais_etudiants.etudiant_id')
                ->join('etudiant_group', 'etudiants.id', '=', 'etudiant_group.etudiant_id')
                ->where('etudiant_group.annee_scolaire_id', $this->anneeId)
                ->where('etudiant_group.niveau_id', $niveau->id)
                ->where('frais_etudiants.est_en_abandon', false)
                ->whereBetween('echeances.date_limite', [$startDate, $endDate])
                ->sum('echeances.montant');

            // Cumulative Expected before strictly this month (up to M-1)
            $previsionM1 = (float) \Illuminate\Support\Facades\DB::table('echeances')
                ->join('frais_etudiants', 'frais_etudiants.id', '=', 'echeances.frais_etudiant_id')
                ->join('etudiants', 'etudiants.id', '=', 'frais_etudiants.etudiant_id')
                ->join('etudiant_group', 'etudiants.id', '=', 'etudiant_group.etudiant_id')
                ->where('etudiant_group.annee_scolaire_id', $this->anneeId)
                ->where('etudiant_group.niveau_id', $niveau->id)
                ->where('frais_etudiants.est_en_abandon', false)
                ->where('echeances.date_limite', '<=', $endPrevMonth)
                ->sum('echeances.montant');
            
            // Expected completely until End of Month (YTD logic)
            $previsionYTD = $previsionM1 + $previsionMois;

            // Montant recouvré sur le mois
            $recouvreMois = (float) \App\Models\Paiement::where('status', 'valide')
                ->where(function($q) { $q->where('nature_paiement', '!=', 'inscription')->orWhereNull('nature_paiement'); })
                ->whereBetween('date_paiement', [$startDate, $endDate])
                ->whereHas('etudiant.etudiantGroups', fn($q) => 
                    $q->where('niveau_id', $niveau->id)->where('annee_scolaire_id', $this->anneeId)
                )->sum('montant');

            // Recouvré jusqu'à M-1
            $recouvreTotalPrev = (float) \App\Models\Paiement::where('status', 'valide')
                ->where(function($q) { $q->where('nature_paiement', '!=', 'inscription')->orWhereNull('nature_paiement'); })
                ->where('date_paiement', '<=', $endPrevMonth)
                ->whereHas('etudiant.etudiantGroups', fn($q) => 
                    $q->where('niveau_id', $niveau->id)->where('annee_scolaire_id', $this->anneeId)
                )->sum('montant');

            // Recouvré jusqu'à la fin de ce mois (YTD progress)
            $recouvreTotalCurrent = $recouvreTotalPrev + $recouvreMois;

            // Reste à recouvrer du mois pur (floor at 0 - you can't officially have 'negative' debt on a table)
            // L'utilisateur indique M-1 est ce qui restait non payé avant, et Reste est juste Prevision - Recouvre.
            $resteMois = max(0, $previsionMois - $recouvreMois);

            // Excédent du mois : l'argent collecté au-delà de la prévision du mois (sert à éponger le M-1)
            $excedentMois = max(0, $recouvreMois - $previsionMois);

            // Cumul RAR M-1 (Total Arrears up to End of Previous Month)
            $cumulRarM1 = max(0, $previsionM1 - $recouvreTotalPrev);
            
            // Cumul RAR YTD (Total Arrears up to End of Current Month)
            $cumulRarYTD = max(0, $previsionYTD - $recouvreTotalCurrent);

            $results[] = [
                'niveau_nom' => $niveau->libelle,
                'prevision' => $previsionMois,
                'montant_recouvre' => $recouvreMois,
                'taux_recouvre' => $previsionMois > 0 ? (float) round(($recouvreMois / $previsionMois) * 100, 1) : 0,
                'reste_a_recouvrer' => $resteMois,
                'excedent' => $excedentMois,
                'cumul_rar_m1' => $cumulRarM1,
                'cumul_rar_ytd' => $cumulRarYTD
            ];
        }

        return $results;
    }

    private function getEtudiantsEnRetard($limit)
    {
        $dossiers = FraisEtudiant::with(['etudiant', 'echeances'])
            ->where('annee_scolaire_id', $this->anneeId)
            ->where('est_en_abandon', false)
            ->get();
            
        $enRetard = collect();
        
        foreach ($dossiers as $f) {
            $expectedToDate = 0;
            $echeanceRetard = null;
            
            foreach ($f->echeances->sortBy('date_limite') as $ech) {
                if ($ech->date_limite < now()) {
                    $expectedToDate += $ech->montant;
                    $echeanceRetard = $ech;
                }
            }
            
            if ($expectedToDate > 0) {
                $paye = \App\Models\Paiement::where('etudiant_id', $f->etudiant_id)->where('status', 'valide')
                    ->where(function($q) { $q->where('nature_paiement', '!=', 'inscription')->orWhereNull('nature_paiement'); })
                    ->sum('montant');
                    
                if ($paye < $expectedToDate) {
                    $reste = max(0, $f->montant_apres_bourse - $paye);
                    $joursRetard = $echeanceRetard ? (int) round(now()->diffInDays($echeanceRetard->date_limite)) : 0;
                    
                    $enRetard->push([
                        'nom' => $f->etudiant->nom,
                        'prenom' => $f->etudiant->prenom,
                        'matricule' => $f->etudiant->matricule,
                        'montant_restant' => $reste,
                        'jours_retard' => max(1, $joursRetard) // au moins 1 jour
                    ]);
                }
            }
        }
        
        return $enRetard->sortByDesc('jours_retard')->take($limit)->values();
    }

    private function getPaiementsRecents($limit)
    {
        return Paiement::with('etudiant')->where('status', 'valide')
            ->latest('date_paiement')
            ->take($limit)
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'etudiant' => $p->etudiant->nom_complet ?? ($p->etudiant->nom . ' ' . $p->etudiant->prenom),
                'libelle' => $p->nature_paiement === 'inscription' ? 'Inscription' : 'Scolarité',
                'mode_paiement' => $p->mode_paiement,
                'montant' => $p->montant,
                'date' => Carbon::parse($p->date_paiement)->format('d/m/Y')
            ]);
    }

    private function getProchainesPrevisions($limit)
    {
        return DB::table('echeances')
            ->select(DB::raw('MONTH(date_limite) as mois'), DB::raw('SUM(montant) as total'), DB::raw('COUNT(*) as count'))
            ->where('date_limite', '>=', Carbon::now())
            ->groupBy('mois')
            ->orderBy('mois')
            ->take($limit)
            ->get()
            ->map(fn($item) => [
                'label' => Carbon::create()->month($item->mois)->translatedFormat('F'),
                'montant_prevu' => $item->total,
                'nombre_echeances' => $item->count
            ]);
    }

    public function getSeriesDashboard()
    {
        // Courbe 1 : Encaissements réels par mois
        $encaissements = Paiement::where('status', 'valide')
            ->select(DB::raw('MONTH(date_paiement) as mois'), DB::raw('SUM(montant) as total'))
            ->whereYear('date_paiement', Carbon::now()->year)
            ->groupBy('mois')
            ->pluck('total', 'mois');

        // Courbe 2 : Prévisions (échéances planifiées)
        $previsions = [];
        try {
            $previsions = DB::table('echeances')
                ->select(DB::raw('MONTH(date_limite) as mois'), DB::raw('SUM(montant) as total'))
                ->groupBy('mois')
                ->pluck('total', 'mois');
        } catch (\Exception $e) {
            // Table absente ou vide
        }

        // Courbe 3 : Dépenses
        $depenses = Depense::where('annee_scolaire_id', $this->anneeId)
            ->select(DB::raw('MONTH(date_depense) as mois'), DB::raw('SUM(montant) as total'))
            ->whereYear('date_depense', Carbon::now()->year)
            ->groupBy('mois')
            ->pluck('total', 'mois');

        return [
            'labels'        => ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sept', 'Oct', 'Nov', 'Déc'],
            'encaissements' => $this->formatMonthlyData($encaissements),
            'previsions'    => $this->formatMonthlyData($previsions),
            'depenses'      => $this->formatMonthlyData($depenses),
        ];
    }

    private function formatMonthlyData($data)
    {
        $formatted = [];
        for ($i = 1; $i <= 12; $i++) { $formatted[] = (float)($data[$i] ?? 0); }
        return $formatted;
    }

    /**
     * Liste détaillée pour le suivi du recouvrement
     */
    public function getSuiviRecouvrement($params)
    {
        $query = FraisEtudiant::with(['etudiant.etudiantGroups'])
            ->where('annee_scolaire_id', $this->anneeId);

        if (!empty($params['niveau_id'])) {
            $query->whereHas('fraisScolarite', fn($q) => $q->where('niveau_id', $params['niveau_id']));
        }

        if (!empty($params['filiere_id'])) {
            $query->whereHas('etudiant.etudiantGroups', fn($q) => $q->where('filiere_id', $params['filiere_id']));
        }

        if (!empty($params['statut'])) {
            $query->where('statut', $params['statut']);
        }

        return $query->get()->map(function($f) {
            $totalPaye = \App\Models\Paiement::where('etudiant_id', $f->etudiant_id)
                ->where('status', 'valide')
                ->where(function($q) { $q->where('nature_paiement', '!=', 'inscription')->orWhereNull('nature_paiement'); })
                ->sum('montant');
            
            // Trouver l'échéance non payée la plus pertinente
            $prochaine = null;
            $cumulPaye = $totalPaye;
            $expectedToDate = 0;
            
            foreach ($f->echeances->sortBy('date_limite') as $ech) {
                if ($ech->date_limite < now()) {
                    $expectedToDate += $ech->montant;
                }
                if ($prochaine === null && $cumulPaye < $ech->montant) {
                    $prochaine = $ech;
                }
                if ($cumulPaye >= $ech->montant) {
                    $cumulPaye -= $ech->montant;
                } else {
                    $cumulPaye = 0;
                }
            }
            
            $estEnRetard = ($totalPaye < $expectedToDate);
            $statutReel = $f->statut;
            if ($statutReel !== 'abandon') {
                if ($totalPaye >= $f->montant_apres_bourse && $f->montant_apres_bourse > 0) {
                    $statutReel = 'solde';
                } elseif ($estEnRetard) {
                    $statutReel = 'retard';
                } else {
                    $statutReel = 'en_cours';
                }
            }

            return [
                'id' => $f->id,
                'slug' => $f->slug,
                'etudiant' => $f->etudiant->nom_complet ?? ($f->etudiant->nom . ' ' . $f->etudiant->prenom),
                'matricule' => $f->etudiant->matricule,
                'montant_du' => $f->montant_apres_bourse,
                'montant_paye' => $totalPaye,
                'reste' => max(0, $f->montant_apres_bourse - $totalPaye),
                'statut' => $statutReel,
                'est_en_retard' => $estEnRetard,
                'prochaine_echeance_date' => $prochaine ? \Carbon\Carbon::parse($prochaine->date_limite)->format('d/m/Y') : '--',
                'prochaine_echeance_montant' => $prochaine ? ($prochaine->montant - $cumulPaye) : 0,
            ];
        });
    }

    private function applyTimeFilter($query, $periode, $dateDebut, $dateFin)
    {
        if ($periode === 'semaine') { $query->whereBetween('date_paiement', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]); }
        elseif ($periode === 'mois') { $query->whereMonth('date_paiement', Carbon::now()->month); }
        elseif ($periode === 'personnalise' && $dateDebut && $dateFin) { $query->whereBetween('date_paiement', [$dateDebut, $dateFin]); }
        else { $query->whereYear('date_paiement', Carbon::now()->year); }
    }

    private function formatPeriodeLabel($periode, $debut, $fin)
    {
        if ($periode === 'semaine') return "Cette semaine";
        if ($periode === 'mois') return Carbon::now()->translatedFormat('F Y');
        if ($periode === 'personnalise') return "Du $debut au $fin";
        return "Année " . Carbon::now()->year;
    }
}
