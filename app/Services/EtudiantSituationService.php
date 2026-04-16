<?php
// app/Services/EtudiantSituationService.php

namespace App\Services;

use App\Models\Etudiant;
use App\Models\FraisEtudiant;
use App\Models\FraisScolarite;
use App\Models\Paiement;
use App\Models\TranchePaiement;
use App\Models\Echeance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EtudiantSituationService
{
    protected $anneeScolaireId;

    public function __construct()
    {
        $this->anneeScolaireId = getAnneeScolaireId();
    }

    /**
     * Récupère la situation de tous les étudiants avec filtres optionnels
     */
    public function getSituationEtudiants($filtres = [])
    {
        // IMPORTANT: on filtre sur les étudiants de l'année active (cohérence avec le Dashboard)
        $query = Etudiant::whereHas('etudiantGroups', function($q) {
            $q->where('annee_scolaire_id', $this->anneeScolaireId);
        })->with([
            'etudiantGroups' => function($q) {
                $q->where('annee_scolaire_id', $this->anneeScolaireId)
                  ->with(['niveau', 'filiere']);
            },
            'fraisEtudiant' => function($q) {
                $q->where('annee_scolaire_id', $this->anneeScolaireId)
                  ->with(['echeances']);
            }
        ]);

        // Filtres
        if (!empty($filtres['filiere_id'])) {
            $query->whereHas('etudiantGroups', function($q) use ($filtres) {
                $q->where('filiere_id', $filtres['filiere_id'])
                  ->where('annee_scolaire_id', $this->anneeScolaireId);
            });
        }

        if (!empty($filtres['niveau_id'])) {
            $query->whereHas('etudiantGroups', function($q) use ($filtres) {
                $q->where('niveau_id', $filtres['niveau_id'])
                  ->where('annee_scolaire_id', $this->anneeScolaireId);
            });
        }

        if (!empty($filtres['statut'])) {
            // Le filtrage par statut sera fait après récupération
        }

        if (!empty($filtres['recherche'])) {
            $search = $filtres['recherche'];
            $query->where(function($q) use ($search) {
                $q->where('nom', 'LIKE', "%{$search}%")
                  ->orWhere('prenom', 'LIKE', "%{$search}%")
                  ->orWhere('matricule', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        $etudiants = $query->get();
        $resultats = [];

        foreach ($etudiants as $etudiant) {
            $situation = $this->calculerSituationEtudiant($etudiant);
            
            // Appliquer le filtre par statut si nécessaire
            if (!empty($filtres['statut']) && $situation['statut'] !== $filtres['statut']) {
                continue;
            }

            $resultats[] = $situation;
        }

        // Tri
        if (!empty($filtres['tri'])) {
            $resultats = $this->trierResultats($resultats, $filtres['tri'], $filtres['ordre'] ?? 'desc');
        }

        return $resultats;
    }

    /**
     * Calcule la situation complète d'un étudiant
     */
    protected function calculerSituationEtudiant($etudiant)
    {
        // Informations de base
        $groupe = $etudiant->etudiantGroups->first();
        $fraisEtudiant = $etudiant->fraisEtudiant->first();

        // Calcul des montants
        $montantAPayer = $this->calculerMontantAPayer($etudiant, $fraisEtudiant);
        $montantPaye = $this->calculerMontantPaye($etudiant);
        $montantRestant = $montantAPayer - $montantPaye;

        // Détails des paiements
        $paiements = $this->getPaiementsDetails($etudiant);

        // Détails des échéances
        $echeances = $this->getEcheancesDetails($etudiant, $fraisEtudiant);

        // Calcul du statut et des retards
        $statutInfo = $this->determinerStatutAvecDetails($etudiant, $echeances, $montantPaye, $montantAPayer);

        // Taux de progression
        $tauxProgression = $montantAPayer > 0 ? round(($montantPaye / $montantAPayer) * 100, 2) : 0;

        return [
            // Infos de base
            'id' => $etudiant->id,
            'statut_global' => $etudiant->statut ?? 'actif',
            'matricule' => $etudiant->matricule,
            'nom' => $etudiant->nom,
            'prenom' => $etudiant->prenom,
            'email' => $etudiant->email,
            'telephone' => $etudiant->telephone,
            'adresse' => $etudiant->adresse,
            'date_naissance' =>  $etudiant->date_naissance ? $etudiant->date_naissance?->format('Y-m-d') : '--',
            'lieu_naissance' => $etudiant->lieu_naissance,
            'genre' => $etudiant->genre?->value,
            
            // Infos académiques
            'filiere' => $groupe?->filiere?->nom ?? 'Non assigné',
            'filiere_id' => $groupe?->filiere_id,
            'niveau' => $groupe?->niveau?->libelle ?? 'Non assigné',
            'niveau_id' => $groupe?->niveau_id,
            
            // Infos financières
            'montant_total_a_payer' => $montantAPayer,
            'montant_total_a_payer_formatted' => $this->formatMontant($montantAPayer),
            'montant_paye' => $montantPaye,
            'montant_paye_formatted' => $this->formatMontant($montantPaye),
            'montant_restant' => $montantRestant,
            'montant_restant_formatted' => $this->formatMontant($montantRestant),
            'taux_progression' => $tauxProgression,
            
            // Statut et retards - CORRIGÉ
            'statut' => $statutInfo['statut'],
            'statut_libelle' => $this->getStatutLibelle($statutInfo['statut']),
            'statut_couleur' => $this->getStatutCouleur($statutInfo['statut']),
            'en_retard' => $statutInfo['en_retard'],
            'jours_retard_max' => $statutInfo['jours_retard_max'],
            'prochaine_echeance' => $statutInfo['prochaine_echeance'],
            'prochaine_echeance_formatted' => $statutInfo['prochaine_echeance'] ? 
                Carbon::parse($statutInfo['prochaine_echeance'])->format('d/m/Y') : null,
            'montant_prochaine_echeance' => $statutInfo['montant_prochaine_echeance'],
            
            // Détails des retards
            'details_retards' => $statutInfo['details_retards'],
            
            // Détails des échéances
            'echeances' => $echeances,
            
            // Détails des paiements
            'paiements' => $paiements,
            
            // Frais négociés
            'a_frais_negocies' => !is_null($fraisEtudiant),
            'frais_negocies' => $fraisEtudiant ? [
                'montant_initial' => $fraisEtudiant->montant_initial,
                'montant_apres_bourse' => $fraisEtudiant->montant_apres_bourse,
                'bourse' => $fraisEtudiant->bourse,
                'type_bourse' => $fraisEtudiant->type_bourse,
            ] : null,
            
            // Date de dernière activité - CORRIGÉ
            'dernier_paiement' => $paiements->isNotEmpty() ? $paiements->first()['date'] : null,
            'dernier_paiement_formatted' => $paiements->isNotEmpty() ? $paiements->first()['date_formatted'] : null,
        ];
    }

    /**
     * Calcule le montant total à payer pour un étudiant
     */
    protected function calculerMontantAPayer($etudiant, $fraisEtudiant = null)
    {
        // Si l'étudiant a un dossier financier (contrat), c'est la source de vérité
        if ($fraisEtudiant) {
            return (float) $fraisEtudiant->montant_apres_bourse;
        }

        // Sinon (fallback de sécurité), on cherche le tarif global
        $groupe = $etudiant->etudiantGroups->first();
        if (!$groupe || !$groupe->niveau_id) {
            return 0;
        }

        $fraisScolarite = FraisScolarite::getFraisForEtudiant(
            $groupe->niveau_id, 
            $etudiant->genre?->value ?? 'Tous', 
            $groupe->filiere_id,
            $this->anneeScolaireId
        );

        return $fraisScolarite ? (float) $fraisScolarite->montant : 0;
    }

    /**
     * Calcule le montant total payé par un étudiant
     */
    protected function calculerMontantPaye($etudiant)
    {
        return Paiement::where('etudiant_id', $etudiant->id)
            ->where('status', 'valide')
            ->sum('montant');
    }

    /**
     * Récupère les détails des paiements d'un étudiant
     */
    protected function getPaiementsDetails($etudiant)
    {
        return Paiement::where('etudiant_id', $etudiant->id)
            ->where('status', 'valide')
            ->with(['payable', 'user'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function($paiement) {
                return [
                    'id' => $paiement->id,
                    'montant' => $paiement->montant,
                    'montant_formatted' => $this->formatMontant($paiement->montant),
                    'mode_paiement' => $paiement->mode_paiement,
                    'mode_paiement_libelle' => Paiement::MODES_PAIEMENT[$paiement->mode_paiement] ?? $paiement->mode_paiement,
                    'reference' => $paiement->reference,
                    'date' => $paiement->created_at->format('Y-m-d H:i:s'),
                    'date_formatted' => $paiement->created_at->format('d/m/Y H:i'),
                    'libelle' => $this->getLibellePayable($paiement->payable),
                    'recu' => $paiement->recu,
                    'valide_par' => $paiement->user?->name,
                ];
            });
    }

    /**
     * Récupère les détails des échéances d'un étudiant
     */
   protected function getEcheancesDetails($etudiant, $fraisEtudiant = null)
{
    $echeances = collect();

    if ($fraisEtudiant) {
        // Échéances négociées
        $echeances = $fraisEtudiant->echeances->map(function($echeance) use ($etudiant) {
            $paye = Paiement::where('etudiant_id', $etudiant->id)
                ->where('payable_type', Echeance::class)
                ->where('payable_id', $echeance->id)
                ->where('status', 'valide')
                ->sum('montant');

            return [
                'type' => 'echeance',
                'libelle' => $echeance->libelle,
                'date_limite' => $echeance->date_limite->format('Y-m-d'),
                'date_limite_formatted' => $echeance->date_limite->format('d/m/Y'),
                'montant' => $echeance->montant,
                'montant_formatted' => $this->formatMontant($echeance->montant),
                'paye' => $paye,
                'paye_formatted' => $this->formatMontant($paye),
                'reste' => $echeance->montant - $paye,
                'reste_formatted' => $this->formatMontant($echeance->montant - $paye),
                'statut' => $paye >= $echeance->montant ? 'paye' : ($echeance->date_limite->isPast() ? 'en_retard' : 'en_attente'),
                'jours_retard' => $echeance->date_limite->isPast() && $paye < $echeance->montant ? 
                    now()->diffInDays($echeance->date_limite) : 0,
            ];
        });
    } else {
        // Tranches standard
        $groupe = $etudiant->etudiantGroups->first();
        if ($groupe && $groupe->niveau_id) {
            $fraisScolarite = FraisScolarite::where('niveau_id', $groupe->niveau_id)
                ->where('annee_scolaire_id', $this->anneeScolaireId)
                ->first();

            if ($fraisScolarite) {
                $echeances = $fraisScolarite->tranchepaiement->map(function($tranche) use ($etudiant) {
                    $paye = Paiement::where('etudiant_id', $etudiant->id)
                        ->where('payable_type', TranchePaiement::class)
                        ->where('payable_id', $tranche->id)
                        ->where('status', 'valide')
                        ->sum('montant');

                    return [
                        'type' => 'tranche',
                        'libelle' => $tranche->libelle,
                        'date_limite' =>date_format(date_create($tranche->date_limite),"Y-m-d"),
                        'date_limite_formatted' =>date_format(date_create($tranche->date_limite),'d/m/Y'),
                        'montant' => $tranche->montant,
                        'montant_formatted' => $this->formatMontant($tranche->montant),
                        'paye' => $paye,
                        'paye_formatted' => $this->formatMontant($paye),
                        'reste' => $tranche->montant - $paye,
                        'reste_formatted' => $this->formatMontant($tranche->montant - $paye),
                        'statut' => $paye >= $tranche->montant ? 'paye' : (Carbon::parse($tranche->date_limite)->isPast() ? 'en_retard' : 'en_attente'),
                        'jours_retard' => Carbon::parse($tranche->date_limite)->isPast() && $paye < $tranche->montant ? 
                            now()->diffInDays(Carbon::parse($tranche->date_limite)) : 0,
                    ];
                });
            }
        }
    }

    return $echeances->sortBy('date_limite')->values();
}

    /**
     * Détermine le statut détaillé d'un étudiant
     */
    protected function determinerStatutAvecDetails($etudiant, $echeances, $montantPaye, $montantAPayer)
    {
        if ($montantAPayer == 0) {
            return [
                'statut' => 'aucun_frais',
                'en_retard' => false,
                'jours_retard_max' => 0,
                'prochaine_echeance' => null,
                'montant_prochaine_echeance' => null,
                'details_retards' => []
            ];
        }

        if ($montantPaye >= $montantAPayer) {
            return [
                'statut' => 'solde',
                'en_retard' => false,
                'jours_retard_max' => 0,
                'prochaine_echeance' => null,
                'montant_prochaine_echeance' => null,
                'details_retards' => []
            ];
        }

        $aujourdhui = now();
        $detailsRetards = [];
        $joursRetardMax = 0;
        $prochaineEcheance = null;
        $montantProchaineEcheance = null;
        $aDesRetards = false;

        foreach ($echeances as $echeance) {
            if ($echeance['reste'] > 0) {
                if ($echeance['date_limite'] < $aujourdhui->format('Y-m-d')) {
                    $aDesRetards = true;
                    $joursRetard = now()->diffInDays(Carbon::parse($echeance['date_limite']));
                    
                    if ($joursRetard > $joursRetardMax) {
                        $joursRetardMax = $joursRetard;
                    }

                    $detailsRetards[] = [
                        'libelle' => $echeance['libelle'],
                        'date_limite' => $echeance['date_limite_formatted'],
                        'jours_retard' => $joursRetard,
                        'montant_restant' => $echeance['reste'],
                        'montant_restant_formatted' => $this->formatMontant($echeance['reste']),
                    ];
                } elseif (is_null($prochaineEcheance) || $echeance['date_limite'] < $prochaineEcheance) {
                    $prochaineEcheance = $echeance['date_limite'];
                    $montantProchaineEcheance = $echeance['reste'];
                }
            }
        }

        if ($aDesRetards) {
            return [
                'statut' => 'en_retard',
                'en_retard' => true,
                'jours_retard_max' => $joursRetardMax,
                'prochaine_echeance' => $prochaineEcheance,
                'montant_prochaine_echeance' => $montantProchaineEcheance,
                'details_retards' => $detailsRetards
            ];
        }

        return [
            'statut' => 'en_cours',
            'en_retard' => false,
            'jours_retard_max' => 0,
            'prochaine_echeance' => $prochaineEcheance,
            'montant_prochaine_echeance' => $montantProchaineEcheance,
            'details_retards' => []
        ];
    }

    /**
     * Récupère les statistiques globales des étudiants
     */
    public function getStatistiquesGlobales()
    {
        $tous = $this->getSituationEtudiants();
        
        $stats = [
            'total' => count($tous),
            'par_statut' => [
                'solde' => 0,
                'en_cours' => 0,
                'en_retard' => 0,
                'aucun_frais' => 0,
            ],
            'montants' => [
                'total_a_payer' => 0,
                'total_paye' => 0,
                'total_restant' => 0,
            ],
            'retards' => [
                'total_etudiants_retard' => 0,
                'montant_total_impaye' => 0,
                'jours_retard_moyen' => 0,
            ]
        ];

        $sommeJoursRetard = 0;

        foreach ($tous as $etudiant) {
            $stats['par_statut'][$etudiant['statut']]++;
            $stats['montants']['total_a_payer'] += $etudiant['montant_total_a_payer'];
            $stats['montants']['total_paye'] += $etudiant['montant_paye'];
            $stats['montants']['total_restant'] += $etudiant['montant_restant'];

            if ($etudiant['en_retard']) {
                $stats['retards']['total_etudiants_retard']++;
                $stats['retards']['montant_total_impaye'] += $etudiant['montant_restant'];
                $sommeJoursRetard += $etudiant['jours_retard_max'];
            }
        }

        $stats['retards']['jours_retard_moyen'] = $stats['retards']['total_etudiants_retard'] > 0 ? 
            round($sommeJoursRetard / $stats['retards']['total_etudiants_retard'], 2) : 0;

        // Formater les montants
        $stats['montants']['total_a_payer_formatted'] = $this->formatMontant($stats['montants']['total_a_payer']);
        $stats['montants']['total_paye_formatted'] = $this->formatMontant($stats['montants']['total_paye']);
        $stats['montants']['total_restant_formatted'] = $this->formatMontant($stats['montants']['total_restant']);
        $stats['retards']['montant_total_impaye_formatted'] = $this->formatMontant($stats['retards']['montant_total_impaye']);

        // Ajouter les pourcentages
        foreach ($stats['par_statut'] as $key => $value) {
            $stats['par_statut'][$key . '_pourcentage'] = $stats['total'] > 0 ? 
                round(($value / $stats['total']) * 100, 2) : 0;
        }

        return $stats;
    }

    /**
     * Trie les résultats selon les critères
     */
    protected function trierResultats($resultats, $critere, $ordre = 'desc')
    {
        usort($resultats, function($a, $b) use ($critere, $ordre) {
            $valeurA = $a[$critere] ?? 0;
            $valeurB = $b[$critere] ?? 0;

            if ($valeurA == $valeurB) {
                return 0;
            }

            if ($ordre === 'asc') {
                return ($valeurA < $valeurB) ? -1 : 1;
            } else {
                return ($valeurA > $valeurB) ? -1 : 1;
            }
        });

        return $resultats;
    }

    /**
     * Formate un montant en FCFA
     */
    protected function formatMontant($montant)
    {
        if (is_null($montant)) return '-';
        return number_format($montant, 0, ',', ' ') . ' FCFA';
    }

    /**
     * Récupère le libellé d'un statut
     */
    protected function getStatutLibelle($statut)
    {
        $libelles = [
            'solde' => 'Solde',
            'en_cours' => 'En cours',
            'en_retard' => 'En retard',
            'aucun_frais' => 'Aucun frais'
        ];

        return $libelles[$statut] ?? $statut;
    }

    /**
     * Récupère la couleur d'un statut
     */
    protected function getStatutCouleur($statut)
    {
        $couleurs = [
            'solde' => 'emerald',
            'en_cours' => 'blue',
            'en_retard' => 'red',
            'aucun_frais' => 'gray'
        ];

        return $couleurs[$statut] ?? 'gray';
    }

    /**
     * Récupère le libellé d'un payable
     */
    protected function getLibellePayable($payable)
    {
        if (!$payable) return 'Paiement direct';

        if ($payable instanceof Echeance) {
            return $payable->libelle;
        }

        if ($payable instanceof TranchePaiement) {
            return $payable->libelle;
        }

        return 'Paiement';
    }

    /**
     * Exporte les données en CSV
     */
    public function exportCSV($filtres = [])
    {
        $etudiants = $this->getSituationEtudiants($filtres);
        
        $csv = [];
        
        // En-têtes
        $csv[] = [
            'Matricule',
            'Nom',
            'Prénom',
            'Email',
            'Téléphone',
            'Filière',
            'Niveau',
            'Statut',
            'Montant total',
            'Montant payé',
            'Montant restant',
            'Taux progression',
            'En retard',
            'Jours retard max',
            'Prochaine échéance',
            'Montant prochaine échéance',
        ];

        // Données
        foreach ($etudiants as $etudiant) {
            $csv[] = [
                $etudiant['matricule'],
                $etudiant['nom'],
                $etudiant['prenom'],
                $etudiant['email'],
                $etudiant['telephone'],
                $etudiant['filiere'],
                $etudiant['niveau'],
                $etudiant['statut_libelle'],
                $etudiant['montant_total_a_payer'],
                $etudiant['montant_paye'],
                $etudiant['montant_restant'],
                $etudiant['taux_progression'] . '%',
                $etudiant['en_retard'] ? 'Oui' : 'Non',
                $etudiant['jours_retard_max'],
                $etudiant['prochaine_echeance_formatted'] ?? '',
                $etudiant['montant_prochaine_echeance'] ?? '',
            ];
        }

        return $csv;
    }
}