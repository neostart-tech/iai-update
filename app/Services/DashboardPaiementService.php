<?php

namespace App\Services;

use App\Models\Etudiant;
use App\Models\FraisEtudiant;
use App\Models\Echeance;
use App\Models\Paiement;
use App\Models\TranchePaiement;
use App\Models\FraisScolarite;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardPaiementService
{
    protected $anneeScolaireId;

    public function __construct()
    {
        $this->anneeScolaireId = getAnneeScolaireId();
    }

    /**
     * Récupère toutes les statistiques du dashboard
     */
    public function getStatistiquesGlobales($periode = 'annee', $dateDebut = null, $dateFin = null)
    {
        $dates = $this->determinerPeriode($periode, $dateDebut, $dateFin);
        
        return [
            'resume' => $this->getResumeGlobal($dates),
            'evolution_paiements' => $this->getEvolutionPaiements($dates),
            'repartition_statuts' => $this->getRepartitionStatuts(),
            'top_performers' => $this->getTopPerformers($dates),
            'etudiants_en_retard' => $this->getEtudiantsEnRetard(),
            'paiements_recents' => $this->getPaiementsRecents($dates),
            'statistiques_filieres' => $this->getStatistiquesParFiliere(),
            'statistiques_niveaux' => $this->getStatistiquesParNiveau(),
            'previsions' => $this->getPrevisions(),
            'historique_6_mois' => $this->getHistorique6Mois(),
        ];
    }

    /**
     * Détermine la période en fonction du paramètre
     */
    protected function determinerPeriode($periode, $dateDebut = null, $dateFin = null)
    {
        $now = Carbon::now();
        
        switch ($periode) {
            case 'semaine':
                return [
                    'debut' => $now->copy()->startOfWeek(),
                    'fin' => $now->copy()->endOfWeek(),
                    'label' => 'Cette semaine'
                ];
            case 'mois':
                return [
                    'debut' => $now->copy()->startOfMonth(),
                    'fin' => $now->copy()->endOfMonth(),
                    'label' => 'Ce mois'
                ];
            case 'annee':
                return [
                    'debut' => $now->copy()->startOfYear(),
                    'fin' => $now->copy()->endOfYear(),
                    'label' => 'Cette année'
                ];
            case 'personnalise':
                return [
                    'debut' => $dateDebut ? Carbon::parse($dateDebut) : $now->copy()->subMonth(),
                    'fin' => $dateFin ? Carbon::parse($dateFin) : $now,
                    'label' => 'Période personnalisée'
                ];
            default:
                return [
                    'debut' => $now->copy()->startOfYear(),
                    'fin' => $now->copy()->endOfYear(),
                    'label' => 'Cette année'
                ];
        }
    }

    /**
     * Résumé global des paiements
     */
    protected function getResumeGlobal($dates)
    {
        $paiements = Paiement::whereBetween('created_at', [$dates['debut'], $dates['fin']])
            ->where('status', 'valide')
            ->get();

        $totalEtudiants = Etudiant::whereHas('etudiantGroups', function($q) {
            $q->where('annee_scolaire_id', $this->anneeScolaireId);
        })->count();

        // Calcul des montants totaux à payer
        $montantTotalAPayer = $this->calculerMontantTotalAPayer();

        // Montants collectés
        $montantCollecte = $paiements->sum('montant');

        // Objectif de collecte (vous pouvez ajuster selon votre logique)
        $objectifMensuel = $montantTotalAPayer / 12; // Si réparti sur 12 mois
        $objectifAtteint = $montantCollecte > 0 ? ($montantCollecte / $objectifMensuel) * 100 : 0;

        return [
            'total_etudiants' => $totalEtudiants,
            'etudiants_avec_frais' => FraisEtudiant::where('annee_scolaire_id', $this->anneeScolaireId)->count(),
            'montant_total_a_payer' => $montantTotalAPayer,
            'montant_collecte' => $montantCollecte,
            'montant_restant' => $montantTotalAPayer - $montantCollecte,
            'taux_collecte' => $montantTotalAPayer > 0 ? round(($montantCollecte / $montantTotalAPayer) * 100, 2) : 0,
            'objectif_mensuel' => $objectifMensuel,
            'objectif_atteint' => round($objectifAtteint, 2),
            'nombre_paiements' => $paiements->count(),
            'periode' => $dates['label'],
        ];
    }

    /**
     * Calcule le montant total à payer pour l'année en cours
     */
    protected function calculerMontantTotalAPayer()
    {
        $total = 0;

        // Montant des frais négociés
        $total += FraisEtudiant::where('annee_scolaire_id', $this->anneeScolaireId)
            ->sum('montant_apres_bourse');

        // Montant des frais standard (via tranches)
        $etudiants = Etudiant::whereHas('etudiantGroups', function($q) {
            $q->where('annee_scolaire_id', $this->anneeScolaireId);
        })->get();

        foreach ($etudiants as $etudiant) {
            // Vérifier si l'étudiant n'a pas déjà un frais négocié
            $aFraisNegocie = FraisEtudiant::where('etudiant_id', $etudiant->id)
                ->where('annee_scolaire_id', $this->anneeScolaireId)
                ->exists();

            if (!$aFraisNegocie) {
                $dernierGroupe = $etudiant->etudiantGroups()
                    ->where('annee_scolaire_id', $this->anneeScolaireId)
                    ->first();

                if ($dernierGroupe && $dernierGroupe->niveau_id) {
                    $fraisScolarite = FraisScolarite::where('niveau_id', $dernierGroupe->niveau_id)
                        ->where('annee_scolaire_id', $this->anneeScolaireId)
                        ->first();

                    if ($fraisScolarite) {
                        $total += $fraisScolarite->tranchepaiement->sum('montant');
                    }
                }
            }
        }

        return $total;
    }

    /**
     * Évolution des paiements sur la période
     */
    protected function getEvolutionPaiements($dates)
    {
        $debut = $dates['debut']->copy();
        $fin = $dates['fin']->copy();
        
        $interval = $this->determinerInterval($debut, $fin);
        $resultats = [];

        switch ($interval) {
            case 'jour':
                while ($debut <= $fin) {
                    $jourSuivant = $debut->copy()->addDay();
                    $montant = Paiement::whereBetween('created_at', [$debut, $jourSuivant])
                        ->where('status', 'valide')
                        ->sum('montant');
                    
                    $resultats[] = [
                        'date' => $debut->format('Y-m-d'),
                        'label' => $debut->format('d/m'),
                        'montant' => $montant,
                        'nombre' => Paiement::whereBetween('created_at', [$debut, $jourSuivant])
                            ->where('status', 'valide')
                            ->count()
                    ];
                    
                    $debut = $jourSuivant;
                }
                break;

            case 'semaine':
                while ($debut <= $fin) {
                    $finSemaine = $debut->copy()->addWeek();
                    $montant = Paiement::whereBetween('created_at', [$debut, $finSemaine])
                        ->where('status', 'valide')
                        ->sum('montant');
                    
                    $resultats[] = [
                        'date' => $debut->format('Y-m-d'),
                        'label' => 'Semaine du ' . $debut->format('d/m'),
                        'montant' => $montant,
                        'nombre' => Paiement::whereBetween('created_at', [$debut, $finSemaine])
                            ->where('status', 'valide')
                            ->count()
                    ];
                    
                    $debut = $finSemaine;
                }
                break;

            case 'mois':
                while ($debut <= $fin) {
                    $finMois = $debut->copy()->addMonth();
                    $montant = Paiement::whereBetween('created_at', [$debut, $finMois])
                        ->where('status', 'valide')
                        ->sum('montant');
                    
                    $resultats[] = [
                        'date' => $debut->format('Y-m'),
                        'label' => $debut->format('F Y'),
                        'montant' => $montant,
                        'nombre' => Paiement::whereBetween('created_at', [$debut, $finMois])
                            ->where('status', 'valide')
                            ->count()
                    ];
                    
                    $debut = $finMois;
                }
                break;
        }

        return $resultats;
    }

    /**
     * Détermine l'intervalle pour le graphique d'évolution
     */
    protected function determinerInterval($debut, $fin)
    {
        $jours = $debut->diffInDays($fin);
        
        if ($jours <= 31) {
            return 'jour';
        } elseif ($jours <= 90) {
            return 'semaine';
        } else {
            return 'mois';
        }
    }

   /**
 * Répartition des étudiants par statut
 */
protected function getRepartitionStatuts()
{
    $total = Etudiant::whereHas('etudiantGroups', function($q) {
        $q->where('annee_scolaire_id', $this->anneeScolaireId);
    })->count();

    $stats = [
        'solde' => 0,
        'a_jour' => 0,
        'en_retard' => 0,
    ];

    // Récupérer tous les étudiants avec leurs relations
    $etudiants = Etudiant::with([
        'fraisEtudiant' => function($q) {
            $q->where('annee_scolaire_id', $this->anneeScolaireId);
        },
        'fraisEtudiant.echeances',
        'etudiantGroups.niveau',
        'etudiantGroups.filiere'
    ])->get();

    foreach ($etudiants as $etudiant) {
        $statut = $this->determinerStatutEtudiant($etudiant);
        if ($statut === 'aucun_frais') continue;
        
        // Mapper 'en_cours' vers 'a_jour' pour le dashboard
        $key = ($statut === 'en_cours') ? 'a_jour' : $statut;
        
        if (isset($stats[$key])) {
            $stats[$key]++;
        }
    }

    // Ajouter les pourcentages
    foreach ($stats as $key => $value) {
        $stats[$key . '_pourcentage'] = $total > 0 ? round(($value / $total) * 100, 2) : 0;
    }

    return $stats;
}
    /**
     * Détermine le statut d'un étudiant
     */
   /**
 * Détermine le statut d'un étudiant
 */
protected function determinerStatutEtudiant($etudiant)
{
    // Vérifier si l'étudiant a un frais négocié
    // Utiliser la relation que nous venons d'ajouter
    $fraisEtudiant = $etudiant->fraisEtudiant()
        ->where('annee_scolaire_id', $this->anneeScolaireId)
        ->first();

    if ($fraisEtudiant) {
        return $fraisEtudiant->statut;
    }

    // Vérifier via les tranches standard
    $dernierGroupe = $etudiant->etudiantGroups()
        ->where('annee_scolaire_id', $this->anneeScolaireId)
        ->first();

    if (!$dernierGroupe || !$dernierGroupe->niveau_id) {
        return 'aucun_frais';
    }

    $fraisScolarite = FraisScolarite::where('niveau_id', $dernierGroupe->niveau_id)
        ->where('annee_scolaire_id', $this->anneeScolaireId)
        ->first();

    if (!$fraisScolarite) {
        return 'aucun_frais';
    }

    // Calculer le total payé pour cet étudiant
    $totalPaye = Paiement::where('etudiant_id', $etudiant->id)
        ->where('status', 'valide')
        ->sum('montant');

    $montantTotal = $fraisScolarite->tranchepaiement->sum('montant');

    if ($totalPaye >= $montantTotal) {
        return 'solde';
    }

    // Vérifier s'il y a des échéances en retard
    $aDesRetards = false;
    foreach ($fraisScolarite->tranchepaiement as $tranche) {
        $payeTranche = Paiement::where('etudiant_id', $etudiant->id)
            ->where('payable_type', TranchePaiement::class)
            ->where('payable_id', $tranche->id)
            ->where('status', 'valide')
            ->sum('montant');

        if ($tranche->montant > $payeTranche && Carbon::now()->gt($tranche->date_limite)) {
            $aDesRetards = true;
            break;
        }
    }

    return $aDesRetards ? 'en_retard' : 'en_cours';
}

    /**
     * Top performers (étudiants qui ont le plus payé)
     */
    protected function getTopPerformers($dates, $limit = 5)
    {
        $top = Paiement::whereBetween('created_at', [$dates['debut'], $dates['fin']])
            ->where('status', 'valide')
            ->select('etudiant_id', DB::raw('SUM(montant) as total_paye'), DB::raw('COUNT(*) as nombre_paiements'))
            ->groupBy('etudiant_id')
            ->orderByDesc('total_paye')
            ->limit($limit)
            ->with('etudiant')
            ->get();

        return $top->map(function($item) {
            return [
                'etudiant_id' => $item->etudiant_id,
                'nom' => $item->etudiant->nom ?? 'N/A',
                'prenom' => $item->etudiant->prenom ?? 'N/A',
                'matricule' => $item->etudiant->matricule ?? 'N/A',
                'total_paye' => $item->total_paye,
                'nombre_paiements' => $item->nombre_paiements,
            ];
        });
    }

    /**
     * Étudiants en retard de paiement
     */
    protected function getEtudiantsEnRetard($limit = 10)
    {
        $etudiants = Etudiant::whereHas('etudiantGroups', function($q) {
            $q->where('annee_scolaire_id', $this->anneeScolaireId);
        })->get()->take(5);

        $enRetard = [];

        foreach ($etudiants as $etudiant) {
            $statut = $this->determinerStatutEtudiant($etudiant);
            
            if ($statut === 'en_retard') {
                $montantRestant = $this->calculerMontantRestant($etudiant);
                $prochaineEcheance = $this->getProchaineEcheance($etudiant);
                
                $enRetard[] = [
                    'etudiant_id' => $etudiant->id,
                    'nom' => $etudiant->nom,
                    'prenom' => $etudiant->prenom,
                    'matricule' => $etudiant->matricule,
                    'montant_restant' => $montantRestant,
                    'prochaine_echeance' => $prochaineEcheance ,
                    'jours_retard' => $prochaineEcheance ? Carbon::now()->diffInDays($prochaineEcheance) : 0,
                ];
            }
        }

        // Trier par montant restant décroissant
        usort($enRetard, function($a, $b) {
            return $b['montant_restant'] <=> $a['montant_restant'];
        });

        return array_slice($enRetard, 0, $limit);
    }

    /**
     * Calcule le montant restant pour un étudiant
     */
    protected function calculerMontantRestant($etudiant)
    {
        $fraisEtudiant = $etudiant->fraisEtudiant()
            ->where('annee_scolaire_id', $this->anneeScolaireId)
            ->first();

        if ($fraisEtudiant) {
            $totalPaye = $fraisEtudiant->echeances->sum('montant_paye');
            return $fraisEtudiant->montant_apres_bourse - $totalPaye;
        }

        $dernierGroupe = $etudiant->etudiantGroups()
            ->where('annee_scolaire_id', $this->anneeScolaireId)
            ->first();

        if (!$dernierGroupe || !$dernierGroupe->niveau_id) {
            return 0;
        }

        $fraisScolarite = FraisScolarite::where('niveau_id', $dernierGroupe->niveau_id)
            ->where('annee_scolaire_id', $this->anneeScolaireId)
            ->first();

        if (!$fraisScolarite) {
            return 0;
        }

        $montantTotal = $fraisScolarite->tranchepaiement->sum('montant');
        $totalPaye = Paiement::where('etudiant_id', $etudiant->id)
            ->where('status', 'valide')
            ->sum('montant');

        return $montantTotal - $totalPaye;
    }

    /**
     * Récupère la prochaine échéance pour un étudiant
     */
 /**
 * Récupère la prochaine échéance pour un étudiant
 */
protected function getProchaineEcheance($etudiant)
{
    $fraisEtudiant = $etudiant->fraisEtudiant()
        ->where('annee_scolaire_id', $this->anneeScolaireId)
        ->first();

    if ($fraisEtudiant) {
        $prochaine = $fraisEtudiant->echeances()
            ->where('statut', '!=', 'paye')
            ->orderBy('date_limite')
            ->first();
        
        return $prochaine ? $prochaine->date_limite : null;
    }

    $dernierGroupe = $etudiant->etudiantGroups()
        ->where('annee_scolaire_id', $this->anneeScolaireId)
        ->first();

    if (!$dernierGroupe || !$dernierGroupe->niveau_id) {
        return null;
    }

    $fraisScolarite = FraisScolarite::where('niveau_id', $dernierGroupe->niveau_id)
        ->where('annee_scolaire_id', $this->anneeScolaireId)
        ->first();

    if (!$fraisScolarite) {
        return null;
    }

    $tranches = $fraisScolarite->tranchepaiement;
    $aujourdhui = Carbon::now();

    foreach ($tranches as $tranche) {
        $payeTranche = Paiement::where('etudiant_id', $etudiant->id)
            ->where('payable_type', TranchePaiement::class)
            ->where('payable_id', $tranche->id)
            ->where('status', 'valide')
            ->sum('montant');

        if ($tranche->montant > $payeTranche && $tranche->date_limite >= $aujourdhui) {
            return date_format(date_create($tranche->date_limite),'Y-m-d');
        }
    }

    return null;
}

    /**
     * Paiements récents
     */
    protected function getPaiementsRecents($dates, $limit = 10)
    {
        return Paiement::whereBetween('created_at', [$dates['debut'], $dates['fin']])
            ->where('status', 'valide')
            ->with(['etudiant', 'payable'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()->take(5)
            ->map(function($paiement) {
                return [
                    'id' => $paiement->id,
                    'etudiant' => $paiement->etudiant ? ($paiement->etudiant->nom . ' ' . $paiement->etudiant->prenom) : 'N/A',
                    'matricule' => $paiement->etudiant->matricule ?? 'N/A',
                    'montant' => $paiement->montant,
                    'mode_paiement' => $paiement->mode_paiement,
                    'reference' => $paiement->reference,
                    'date' => $paiement->created_at->format('Y-m-d H:i'),
                    'libelle' => $this->getLibellePayable($paiement->payable),
                ];
            });
    }

    /**
     * Statistiques par filière
     */
    protected function getStatistiquesParFiliere()
    {
        $filieres = \App\Models\Filiere::all();
        $resultats = [];

        foreach ($filieres as $filiere) {
            $etudiants = Etudiant::whereHas('etudiantGroups', function($q) use ($filiere) {
                $q->where('filiere_id', $filiere->id)
                  ->where('annee_scolaire_id', $this->anneeScolaireId);
            })->get();

            if ($etudiants->isEmpty()) {
                continue;
            }

            $totalAPayer = 0;
            $totalPaye = 0;
            $statsStatuts = [
                'solde' => 0,
                'en_cours' => 0,
                'en_retard' => 0,
                'aucun_frais' => 0,
            ];

            foreach ($etudiants as $etudiant) {
                $statut = $this->determinerStatutEtudiant($etudiant);
                $statsStatuts[$statut]++;

                $montantAPayer = $this->getMontantAPayerEtudiant($etudiant);
                $totalAPayer += $montantAPayer;

                $montantPaye = $this->getMontantPayeEtudiant($etudiant);
                $totalPaye += $montantPaye;
            }

            $resultats[] = [
                'filiere_id' => $filiere->id,
                'filiere_nom' => $filiere->nom,
                'filiere_code' => $filiere->code,
                'nombre_etudiants' => $etudiants->count(),
                'total_a_payer' => $totalAPayer,
                'total_paye' => $totalPaye,
                'total_restant' => $totalAPayer - $totalPaye,
                'taux_collecte' => $totalAPayer > 0 ? round(($totalPaye / $totalAPayer) * 100, 2) : 0,
                'statuts' => $statsStatuts,
            ];
        }

        return $resultats;
    }

    /**
     * Statistiques par niveau
     */
    protected function getStatistiquesParNiveau()
    {
        $niveaux = \App\Models\Niveau::all();
        $resultats = [];

        foreach ($niveaux as $niveau) {
            $etudiants = Etudiant::whereHas('etudiantGroups', function($q) use ($niveau) {
                $q->where('niveau_id', $niveau->id)
                  ->where('annee_scolaire_id', $this->anneeScolaireId);
            })->get();

            if ($etudiants->isEmpty()) {
                continue;
            }

            $totalAPayer = 0;
            $totalPaye = 0;
            $statsStatuts = [
                'solde' => 0,
                'en_cours' => 0,
                'en_retard' => 0,
                'aucun_frais' => 0,
            ];

            foreach ($etudiants as $etudiant) {
                $statut = $this->determinerStatutEtudiant($etudiant);
                $statsStatuts[$statut]++;

                $montantAPayer = $this->getMontantAPayerEtudiant($etudiant);
                $totalAPayer += $montantAPayer;

                $montantPaye = $this->getMontantPayeEtudiant($etudiant);
                $totalPaye += $montantPaye;
            }

            $resultats[] = [
                'niveau_id' => $niveau->id,
                'niveau_nom' => $niveau->libelle,
                'niveau_code' => $niveau->code,
                'nombre_etudiants' => $etudiants->count(),
                'total_a_payer' => $totalAPayer,
                'total_paye' => $totalPaye,
                'total_restant' => $totalAPayer - $totalPaye,
                'taux_collecte' => $totalAPayer > 0 ? round(($totalPaye / $totalAPayer) * 100, 2) : 0,
                'statuts' => $statsStatuts,
            ];
        }

        return $resultats;
    }

    /**
     * Récupère le montant à payer pour un étudiant
     */
    /**
 * Récupère le montant à payer pour un étudiant
 */
protected function getMontantAPayerEtudiant($etudiant)
{
    $fraisEtudiant = $etudiant->fraisEtudiant()
        ->where('annee_scolaire_id', $this->anneeScolaireId)
        ->first();

    if ($fraisEtudiant) {
        return $fraisEtudiant->montant_apres_bourse;
    }

    $dernierGroupe = $etudiant->etudiantGroups()
        ->where('annee_scolaire_id', $this->anneeScolaireId)
        ->first();

    if (!$dernierGroupe || !$dernierGroupe->niveau_id) {
        return 0;
    }

    $fraisScolarite = FraisScolarite::where('niveau_id', $dernierGroupe->niveau_id)
        ->where('annee_scolaire_id', $this->anneeScolaireId)
        ->first();

    return $fraisScolarite ? $fraisScolarite->tranchepaiement->sum('montant') : 0;
}

    /**
     * Récupère le montant payé pour un étudiant
     */
    protected function getMontantPayeEtudiant($etudiant)
    {
        return Paiement::where('etudiant_id', $etudiant->id)
            ->where('status', 'valide')
            ->sum('montant');
    }

    /**
     * Prévisions pour les prochains mois
     */
    protected function getPrevisions($mois = 6)
    {
        $previsions = [];
        $aujourdhui = Carbon::now();

        for ($i = 0; $i < $mois; $i++) {
            $moisDate = $aujourdhui->copy()->addMonths($i);
            $moisSuivant = $moisDate->copy()->addMonth();

            // Montant des échéances à venir pour ce mois
            $montantPrevu = 0;

            // Échéances négociées
            $echeances = Echeance::whereBetween('date_limite', [$moisDate, $moisSuivant])
                ->where('statut', '!=', 'paye')
                ->get();

            foreach ($echeances as $echeance) {
                $montantPrevu += $echeance->reste_a_payer;
            }

            // Tranches à venir
            $tranches = TranchePaiement::whereBetween('date_limite', [$moisDate, $moisSuivant])
                ->get();

            foreach ($tranches as $tranche) {
                // Pour chaque tranche, on estime le nombre d'étudiants concernés
                $fraisScolarite = $tranche->fraiscolarite;
                if ($fraisScolarite) {
                    $nbEtudiants = Etudiant::whereHas('etudiantGroups', function($q) use ($fraisScolarite) {
                        $q->where('niveau_id', $fraisScolarite->niveau_id)
                          ->where('annee_scolaire_id', $this->anneeScolaireId);
                        if ($fraisScolarite->filiere_id) {
                            $q->where('filiere_id', $fraisScolarite->filiere_id);
                        }
                    })->count();

                    // On estime que 80% des étudiants paieront
                    $montantPrevu += $tranche->montant * ceil($nbEtudiants * 0.8);
                }
            }

            $previsions[] = [
                'mois' => $moisDate->format('Y-m'),
                'label' => $moisDate->format('F Y'),
                'montant_prevu' => $montantPrevu,
                'nombre_echeances' => $echeances->count() + $tranches->count(),
            ];
        }

        return $previsions;
    }

    /**
     * Historique des 6 derniers mois
     */
    protected function getHistorique6Mois()
    {
        $historique = [];
        $aujourdhui = Carbon::now();

        for ($i = 5; $i >= 0; $i--) {
            $moisDate = $aujourdhui->copy()->subMonths($i)->startOfMonth();
            $moisFin = $moisDate->copy()->endOfMonth();

            $collecte = Paiement::whereBetween('created_at', [$moisDate, $moisFin])
                ->where('status', 'valide')
                ->sum('montant');

            $historique[] = [
                'mois' => $moisDate->format('Y-m'),
                'label' => $moisDate->translatedFormat('F Y'),
                'montant' => $collecte,
            ];
        }

        return $historique;
    }

    /**
     * Récupère le libellé d'un payable
     */
    protected function getLibellePayable($payable)
    {
        if (!$payable) return 'Paiement';

        if ($payable instanceof \App\Models\Echeance) {
            return $payable->libelle;
        }

        if ($payable instanceof \App\Models\TranchePaiement) {
            return $payable->libelle;
        }

        return 'Paiement';
    }
}