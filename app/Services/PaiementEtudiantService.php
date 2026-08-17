<?php
// app/Services/PaiementEtudiantService.php

namespace App\Services;

use App\Models\AnneeScolaire;
use App\Models\Etudiant;
use App\Models\FraisEtudiant;
use App\Models\FraisScolarite;
use App\Models\Paiement;
use App\Models\TranchePaiement;
use App\Models\BourseEtudiant;
use App\Models\FraisInscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class PaiementEtudiantService
{
    /**
     * Récupère les informations de paiement pour un étudiant
     */
    public function getInfosPaiement($etudiantId, $anneeScolaireId = null)
    {
        try {
            $anneeScolaireId = $anneeScolaireId ?? AnneeScolaire::courante()->id;

            // Charger l'étudiant avec ses relations correctes via etudiantGroups
            $etudiant = Etudiant::with([
                'etudiantGroups' => function ($query) use ($anneeScolaireId) {
                    $query->where('annee_scolaire_id', $anneeScolaireId)
                        ->with(['niveau', 'filiere', 'group']);
                }
            ])->find($etudiantId);

            if (!$etudiant) {
                throw new Exception("Profil étudiant introuvable (ID: $etudiantId). Veuillez contacter l'administration.");
            }

           

            // Vérifier d'abord s'il a un frais négocié
            $fraisEtudiant = $this->getFraisNegocie($etudiantId, $anneeScolaireId);

            $infos = null;
            if ($fraisEtudiant) {
                $infos = $this->formatInfosFraisNegocie($fraisEtudiant, $etudiant);
            } else {
                // Sinon, récupérer les frais par défaut
                $infos = $this->getInfosFraisParDefaut($etudiant, $anneeScolaireId);
            }

            // Anomaly Check
            $diagnosticService = new \App\Services\DiagnosticFinancierService();
            $anomalie = $diagnosticService->verifierAnomalieEtudiant($etudiant, $anneeScolaireId);
            $infos['anomalie'] = $anomalie;

            return $infos;
        } catch (Exception $e) {
            Log::error('Erreur getInfosPaiement: ' . $e->getMessage(), [
                'etudiant_id' => $etudiantId,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Récupère le frais négocié d'un étudiant
     */
    private function getFraisNegocie($etudiantId, $anneeScolaireId)
    {
        return FraisEtudiant::with([
            'echeances' => function ($query) {
                $query->orderBy('ordre');
            },
            'bourseEtudiant.bourse'
        ])
            ->where('etudiant_id', $etudiantId)
            ->where('annee_scolaire_id', $anneeScolaireId)
            ->first();
    }

    /**
     * Formate les informations pour un frais négocié
     */
    private function formatInfosFraisNegocie($fraisEtudiant, $etudiant)
    {
        // Récupérer le dernier groupe pour les infos
        $dernierGroupe = $etudiant->etudiantGroups->first();
        $niveau = $dernierGroupe && $dernierGroupe->niveau ? $dernierGroupe->niveau->libelle : null;
        $filiere = $dernierGroupe && $dernierGroupe->filiere ? $dernierGroupe->filiere->nom : null;
        $modeFormation = null;
        if ($dernierGroupe && $dernierGroupe->mode_formation) {
            $modeFormation = $dernierGroupe->mode_formation instanceof \UnitEnum 
                ? $dernierGroupe->mode_formation->value 
                : $dernierGroupe->mode_formation;
        }
        if (!$modeFormation || $modeFormation === 'Tous') {
            if (isset($etudiant->mode_formation)) {
                $modeFormation = $etudiant->mode_formation instanceof \UnitEnum 
                    ? $etudiant->mode_formation->value 
                    : $etudiant->mode_formation;
            }
        }
        $modeFormation = $modeFormation ?: 'Présentiel';

        // Anomaly Check
        $diagnosticService = new \App\Services\DiagnosticFinancierService();
        $anomalie = $diagnosticService->verifierAnomalieEtudiant($etudiant, $fraisEtudiant->annee_scolaire_id);

        // Recalculer les échéances pour s'assurer que les paiements sont bien ventilés
        if ($fraisEtudiant->echeances->isNotEmpty()) {
            $this->recalculerEcheancesNegociees($fraisEtudiant, $etudiant->id);
            $fraisEtudiant->refresh();
            $fraisEtudiant->unsetRelation('echeances');
            $fraisEtudiant->load(['echeances' => function ($query) {
                $query->orderBy('ordre');
            }]);
        }

        $totalPaye = $fraisEtudiant->echeances->sum('montant_paye');
        $montantTotal = $fraisEtudiant->montant_apres_bourse;

        return [
            'type' => 'negocie',
            'source' => 'frais_etudiant',
            'etudiant' => [
                'id' => $etudiant->id,
                'nom' => $etudiant->nom,
                'prenom' => $etudiant->prenom,
                'nom_complet' => $etudiant->nom . ' ' . $etudiant->prenom,
                'matricule' => $etudiant->matricule,
                'niveau' => $niveau,
                'filiere' => $filiere,
                'mode_formation' => $modeFormation,
                'telephone' => $etudiant->tel,
                'slug' => $etudiant->slug,
            ],
            'anomalie' => $anomalie,
            'frais' => [
                'id' => $fraisEtudiant->id,
                'montant_initial' => (float) $fraisEtudiant->montant_initial,
                'montant_apres_bourse' => (float) $fraisEtudiant->montant_apres_bourse,
                'type_paiement' => $fraisEtudiant->type_paiement,
                'frequence_paiement' => $fraisEtudiant->frequence_paiement,
                'statut' => $fraisEtudiant->statut,
            ],
            'bourse' => $fraisEtudiant->bourseEtudiant ? [
                'id' => $fraisEtudiant->bourseEtudiant->id,
                'nom' => $fraisEtudiant->bourseEtudiant->bourse->nom,
                'type' => $fraisEtudiant->bourseEtudiant->bourse->type,
                'valeur' => (float) $fraisEtudiant->bourseEtudiant->bourse->valeur,
            ] : null,
            'echeances' => $fraisEtudiant->echeances->map(function ($echeance) {
                return [
                    'id' => $echeance->id,
                    'libelle' => $echeance->libelle,
                    'montant' => (float) $echeance->montant,
                    'montant_paye' => (float) $echeance->montant_paye,
                    'reste' => (float) $echeance->reste_a_payer,
                    'date_limite' => $echeance->date_limite->format('Y-m-d'),
                    'date_limite_formatted' => $echeance->date_limite->format('d/m/Y'),
                    'ordre' => $echeance->ordre,
                    'statut' => $echeance->statut,
                    'progression' => $echeance->progression,
                ];
            })->values()->toArray(),
            'total' => [
                'montant_total' => (float) $montantTotal,
                'total_paye' => (float) $totalPaye,
                'reste_a_payer' => (float) ($montantTotal - $totalPaye),
            ]
        ];
    }

    /**
     * Récupère les informations pour un étudiant sans frais négocié
     */
    private function getInfosFraisParDefaut($etudiant, $anneeScolaireId)
    {
        // Récupérer le dernier groupe de l'étudiant via etudiantGroups
        $dernierGroupe = $etudiant->etudiantGroups->first();

        if (!$dernierGroupe) {
            throw new Exception("L'étudiant n'est assigné à aucun groupe pour l'année en cours");
        }

        // Récupérer les informations depuis le pivot
        $niveau = $dernierGroupe->niveau;
        $filiere = $dernierGroupe->filiere;
        $groupe = $dernierGroupe->group;

        $niveauId = $niveau->id ?? null;
        $filiereId = $filiere->id ?? null;

        if (!$niveauId) {
            throw new Exception("L'étudiant n'a pas de niveau assigné dans son groupe actuel");
        }

        $modeFormation = null;
        if ($dernierGroupe && $dernierGroupe->mode_formation) {
            $modeFormation = $dernierGroupe->mode_formation instanceof \UnitEnum 
                ? $dernierGroupe->mode_formation->value 
                : $dernierGroupe->mode_formation;
        }
        if (!$modeFormation || $modeFormation === 'Tous') {
            if (isset($etudiant->mode_formation)) {
                $modeFormation = $etudiant->mode_formation instanceof \UnitEnum 
                    ? $etudiant->mode_formation->value 
                    : $etudiant->mode_formation;
            }
        }
        $modeFormation = $modeFormation ?: 'Présentiel';

        $genreValue = ($etudiant->genre instanceof \UnitEnum) ? $etudiant->genre->value : ($etudiant->genre ?? 'Tous');

        // Récupérer le frais de scolarité correspondant au profil exact
        $fraisScolarite = FraisScolarite::getFraisForEtudiant(
            $niveauId,
            $genreValue,
            $filiereId,
            $anneeScolaireId,
            $modeFormation
        );

        if (!$fraisScolarite) {
            return [
                'type' => 'standard',
                'source' => 'frais_scolarite',
                'etudiant' => [
                    'id' => $etudiant->id,
                    'nom' => $etudiant->nom,
                    'prenom' => $etudiant->prenom,
                    'nom_complet' => $etudiant->nom . ' ' . $etudiant->prenom,
                    'matricule' => $etudiant->matricule,
                    'niveau' => $niveau->libelle ?? null,
                    'filiere' => $filiere->nom ?? null,
                    'mode_formation' => $modeFormation,
                    'genre' => $etudiant->genre,
                    'telephone' => $etudiant->tel,
                    'slug' => $etudiant->slug ?? null,
                ],
                'frais_scolarite' => null,
                'bourse' => null,
                'tranches' => [],
                'frais_inscription' => $this->getFraisInscriptionInfos($etudiant->id, $anneeScolaireId),
                'total' => [
                    'montant_total' => 0,
                    'total_paye' => 0,
                    'reste_a_payer' => 0,
                ]
            ];
        }

        // Récupérer les tranches de paiement
        $tranches = $this->getTranchesWithPaiements($fraisScolarite->id, $etudiant->id);

        // Récupérer la bourse de l'étudiant
        $bourseEtudiant = $this->getBourseEtudiant($etudiant->id, $anneeScolaireId);

        // Calculer le coefficient de bourse
        $coefficient = $this->calculerCoefficientBourse($bourseEtudiant);

        // Formater les tranches avec les montants ajustés
        $tranchesFormatted = $this->formatTranches($tranches, $coefficient);

        // Calculer les totaux
        $totaux = $this->calculerTotaux($tranchesFormatted);

        return [
            'type' => 'standard',
            'source' => 'frais_scolarite',
            'etudiant' => [
                'id' => $etudiant->id,
                'nom' => $etudiant->nom,
                'prenom' => $etudiant->prenom,
                'nom_complet' => $etudiant->nom . ' ' . $etudiant->prenom,
                'matricule' => $etudiant->matricule,
                'niveau' => $niveau->libelle ?? null,
                'filiere' => $filiere->nom ?? null,
                'mode_formation' => $modeFormation,
                'genre' => $etudiant->genre,
                'telephone' => $etudiant->tel,
                'slug' => $etudiant->slug ?? null,
            ],
            'frais_scolarite' => [
                'id' => $fraisScolarite->id,
                'niveau_id' => $fraisScolarite->niveau_id,
                'filiere_id' => $fraisScolarite->filiere_id,
                'montant_total' => (float) $fraisScolarite->montant,
                'genre' => $fraisScolarite->genre,
            ],
            'bourse' => $bourseEtudiant ? [
                'id' => $bourseEtudiant->id,
                'nom' => $bourseEtudiant->bourse->nom,
                'type' => $bourseEtudiant->bourse->type,
                'valeur' => (float) $bourseEtudiant->bourse->valeur,
            ] : null,
            'tranches' => $tranchesFormatted,
            'frais_inscription' => $this->getFraisInscriptionInfos($etudiant->id, $anneeScolaireId),
            'total' => $totaux
        ];
    }

    /**
     * Récupère le frais de scolarité approprié pour un étudiant
     */
    private function getFraisScolariteForEtudiant($niveauId, $filiereId, $genre, $anneeScolaireId, $modeFormation = 'Présentiel')
    {
        $genreValue = ($genre instanceof \UnitEnum) ? $genre->value : ($genre ?? 'Tous');
        $modeValue = ($modeFormation instanceof \UnitEnum) ? $modeFormation->value : ($modeFormation ?? 'Présentiel');

        return FraisScolarite::getFraisForEtudiant($niveauId, $genreValue, $filiereId, $anneeScolaireId, $modeValue);
    }
    /**
     * Récupère les tranches avec les paiements déjà effectués
     */
    private function getTranchesWithPaiements($fraisScolariteId, $etudiantId)
    {
        // Sum all valid scolarite payments made by the student
        $totalPayeScolarite = Paiement::where('etudiant_id', $etudiantId)
            ->where('status', 'valide')
            ->where(function($q) {
                $q->where('payable_type', TranchePaiement::class)
                  ->orWhere('nature_paiement', 'scolarite');
            })
            ->sum('montant');

        $resteVentilation = $totalPayeScolarite;

        return TranchePaiement::where('frais_scolarite_id', $fraisScolariteId)
            ->orderBy('id')
            ->get()
            ->map(function ($tranche) use (&$resteVentilation) {
                $paye = min($resteVentilation, (float)$tranche->montant);
                $resteVentilation = max(0, $resteVentilation - $paye);

                $tranche->total_paye = $paye;
                return $tranche;
            });
    }

    /**
     * Récupère la bourse active d'un étudiant
     */
    private function getBourseEtudiant($etudiantId, $anneeScolaireId)
    {
        return BourseEtudiant::with('bourse')
            ->where('etudiant_id', $etudiantId)
            ->where('annee_scolaire_id', $anneeScolaireId)
            ->first();
    }

    /**
     * Calcule le coefficient d'application de la bourse
     */
    private function calculerCoefficientBourse($bourseEtudiant)
    {
        if (!$bourseEtudiant || !$bourseEtudiant->bourse) {
            return 1;
        }

        $bourse = $bourseEtudiant->bourse;

        if ($bourse->type === 'pourcentage') {
            return (100 - $bourse->valeur) / 100;
        }

        // Pour une bourse en montant fixe, on ne peut pas appliquer de coefficient
        // Ce sera traité séparément
        return 1;
    }

    /**
     * Formate les tranches avec les montants après bourse
     */
    private function formatTranches($tranches, $coefficient)
    {
        return $tranches->map(function ($tranche) use ($coefficient) {
        $montantApresBourse = round($tranche->montant * $coefficient);

        // Déterminer le statut de la tranche
        $statut = $this->determinerStatutTranche(
            $tranche->total_paye,
            $montantApresBourse,
            $tranche->date_limite
        );

        // Gérer la date limite (peut être une string ou un objet Carbon)
        $dateLimite = $tranche->date_limite;
        $dateFormatted = '';
        $dateForDisplay = '';
        
        if ($dateLimite) {
            if (is_object($dateLimite) && method_exists($dateLimite, 'format')) {
                // C'est un objet Carbon/DateTime
                $dateFormatted = $dateLimite->format('Y-m-d');
                $dateForDisplay = $dateLimite->format('d/m/Y');
            } else {
                // C'est une string, on la convertit
                try {
                    $dateObj = \Carbon\Carbon::parse($dateLimite);
                    $dateFormatted = $dateObj->format('Y-m-d');
                    $dateForDisplay = $dateObj->format('d/m/Y');
                } catch (\Exception $e) {
                    // Si la date est invalide, on met une chaîne vide
                    $dateFormatted = '';
                    $dateForDisplay = '';
                }
            }
        }

        return [
            'id' => $tranche->id,
            'libelle' => $tranche->libelle,
            'montant_initial' => (float) $tranche->montant,
            'montant_apres_bourse' => (float) $montantApresBourse,
            'paye' => (float) $tranche->total_paye,
            'reste' => (float) ($montantApresBourse - $tranche->total_paye),
            'date_limite' => $dateFormatted,
            'date_limite_formatted' => $dateForDisplay,
            'ordre' => $tranche->ordre ?? $tranche->id,
            'statut' => $statut,
        ];
    })->values()->toArray();
}

    /**
     * Détermine le statut d'une tranche
     */
    private function determinerStatutTranche($paye, $montantTotal, $dateLimite)
    {
        if ($paye >= $montantTotal) {
            return 'paye';
        }

        if ($paye > 0) {
            return 'partiel';
        }

        if (now()->gt($dateLimite)) {
            return 'en_retard';
        }

        return 'en_attente';
    }

    /**
     * Calcule les totaux généraux
     */
    private function calculerTotaux($tranches)
    {
        $montantTotal = collect($tranches)->sum('montant_apres_bourse');
        $totalPaye = collect($tranches)->sum('paye');

        return [
            'montant_total' => (float) $montantTotal,
            'total_paye' => (float) $totalPaye,
            'reste_a_payer' => (float) ($montantTotal - $totalPaye),
        ];
    }

    /**
     * Calcule le récapitulatif pour un étudiant
     */
    public function getRecap($etudiantId, $anneeScolaireId = null)
    {
        try {
            $infos = $this->getInfosPaiement($etudiantId, $anneeScolaireId);

            $montantTotal = $infos['total']['montant_total'];
            $totalPaye = $infos['total']['total_paye'];
            $pourcentage = $montantTotal > 0 ? round(($totalPaye / $montantTotal) * 100) : 0;

            if ($infos['type'] === 'negocie') {
                $echeances = collect($infos['echeances']);
                
                // 1. Chercher d'abord dans la table dédiée frais_inscriptions
                $fraisInscTable = $this->getFraisInscriptionForEtudiant($etudiantId, $anneeScolaireId);
                
                $montantInscription = $fraisInscTable ? (float)$fraisInscTable->montant : 0;

                // 2. Vérifier si l'inscription est payée via les échéances
                $echeancesInsc = $echeances->filter(function($e) {
                    $lib = strtolower($e['libelle']);
                    $lib = str_replace(['é', 'è', 'ê', 'à'], ['e', 'e', 'e', 'a'], $lib);
                    return str_contains($lib, 'inscrip') || str_contains($lib, 'admis');
                });

                if ($echeancesInsc->isEmpty() && $echeances->isNotEmpty() && $montantInscription <= 0) {
                    $echeancesInsc = collect([$echeances->first()]);
                }

                $inscriptionPayeeParEcheance = $echeancesInsc->isNotEmpty() && $echeancesInsc->every(fn($e) => $e['statut'] === 'paye');

                // 2b. Vérifier s'il y a un paiement direct sur FraisInscription ou nature 'inscription'
                $paiementsInscQuery = \App\Models\Paiement::where('etudiant_id', $etudiantId)
                    ->where(function($q) use ($fraisInscTable) {
                        $q->where('nature_paiement', 'inscription')
                          ->orWhere('reference', 'LIKE', 'REG-%');
                        if ($fraisInscTable) {
                            $q->orWhere(function($q2) use ($fraisInscTable) {
                                $q2->where('payable_type', \App\Models\FraisInscription::class)
                                   ->where('payable_id', $fraisInscTable->id);
                            });
                        } else {
                            $q->orWhere('payable_type', \App\Models\FraisInscription::class);
                        }
                    });

                $countPaiementsInsc = (clone $paiementsInscQuery)->count();
                $sumPaiementsInsc = (clone $paiementsInscQuery)->sum('montant');

                if ($montantInscription > 0) {
                    $inscriptionPayeeDirect = $sumPaiementsInsc >= $montantInscription;
                } else {
                    $inscriptionPayeeDirect = $countPaiementsInsc > 0;
                }

                $etudiantObj = Etudiant::find($etudiantId);
                $candidatureObj = null;
                if ($etudiantObj) {
                    $candidatureObj = \App\Models\Candidature::where('etudiant_id', $etudiantId)
                        ->when($etudiantObj->email, fn($q) => $q->orWhere('email', $etudiantObj->email))
                        ->when($etudiantObj->user_id, fn($q) => $q->orWhere('user_id', $etudiantObj->user_id))
                        ->first();
                } else {
                    $candidatureObj = \App\Models\Candidature::where('id', $etudiantId)
                        ->orWhere('etudiant_id', $etudiantId)
                        ->first();
                }

                $paiementsInscQuery = \App\Models\Paiement::where(function($q) use ($etudiantId, $candidatureObj) {
                    $q->where('etudiant_id', $etudiantId);
                    if ($candidatureObj) {
                        $q->orWhere(function($q2) use ($candidatureObj) {
                            $q2->where('payable_type', \App\Models\Candidature::class)
                               ->where('payable_id', $candidatureObj->id);
                        });
                    }
                });

                $countPaiementsTotal = (clone $paiementsInscQuery)->count();
                $sumPaiementsTotal = (clone $paiementsInscQuery)->sum('montant');

                $paiementsInscFiltre = (clone $paiementsInscQuery)->where(function($q) use ($fraisInscTable) {
                    $q->where('nature_paiement', 'inscription')
                      ->orWhere('reference', 'LIKE', 'REG-%')
                      ->orWhere('reference', 'LIKE', 'INS-%')
                      ->orWhere('reference', 'LIKE', 'CAN-%');
                    if ($fraisInscTable) {
                        $q->orWhere(function($q2) use ($fraisInscTable) {
                            $q2->where('payable_type', \App\Models\FraisInscription::class)
                               ->where('payable_id', $fraisInscTable->id);
                        });
                    } else {
                        $q->orWhere('payable_type', \App\Models\FraisInscription::class);
                    }
                });

                $countPaiementsInsc = (clone $paiementsInscFiltre)->count();
                $sumPaiementsInsc = (clone $paiementsInscFiltre)->sum('montant');

                if ($montantInscription > 0) {
                    $inscriptionPayeeDirect = $sumPaiementsInsc >= $montantInscription || $sumPaiementsTotal >= $montantInscription;
                } else {
                    $inscriptionPayeeDirect = $countPaiementsInsc > 0 || $countPaiementsTotal > 0;
                }

                // La validation exige un paiement effectif dans la table `paiements` ou via les échéances
                $inscriptionPayee = $inscriptionPayeeParEcheance || $inscriptionPayeeDirect;

                if ($montantInscription <= 0 && !$inscriptionPayee) {
                    $echeanceInsc = $echeancesInsc->firstWhere('statut', '!=', 'paye');
                    if ($echeanceInsc) {
                        $montantInscription = $echeanceInsc['reste'];
                    }
                }

                return [
                    'montant_total' => $montantTotal,
                    'total_paye' => $totalPaye,
                    'reste_a_payer' => $montantTotal - $totalPaye,
                    'pourcentage' => $pourcentage,
                    'inscription_payee' => $inscriptionPayee,
                    'frais_inscription_paye' => $inscriptionPayee,
                    'montant_inscription' => $montantInscription,
                    'nombre_echeances' => $echeances->count(),
                    'echeances_payees' => $echeances->where('statut', 'paye')->count(),
                    'echeances_partiellement_payees' => $echeances->where('statut', 'partiel')->count(),
                    'echeances_en_attente' => $echeances->where('statut', 'en_attente')->count(),
                    'echeances_en_retard' => $echeances->where('statut', 'en_retard')->count(),
                ];
            } else {
                $tranches = collect($infos['tranches']);
                
                // 1. Chercher d'abord dans la table dédiée frais_inscriptions
                $fraisInscTable = $this->getFraisInscriptionForEtudiant($etudiantId, $anneeScolaireId);
                
                $montantInscription = $fraisInscTable ? (float)$fraisInscTable->montant : 0;

                // 2. Vérifier si l'inscription est payée (via les tranches)
                $tranchesInsc = $tranches->filter(function($t) {
                    $lib = strtolower($t['libelle']);
                    $lib = str_replace(['é', 'è', 'ê', 'à'], ['e', 'e', 'e', 'a'], $lib);
                    return str_contains($lib, 'inscrip') || str_contains($lib, 'admis');
                });

                if ($tranchesInsc->isEmpty() && $tranches->isNotEmpty() && $montantInscription <= 0) {
                    $tranchesInsc = collect([$tranches->first()]);
                }

                $inscriptionPayeeParTranche = $tranchesInsc->isNotEmpty() && $tranchesInsc->every(fn($t) => $t['statut'] === 'paye');

                $etudiantObj = Etudiant::find($etudiantId);
                $candidatureObj = null;
                if ($etudiantObj) {
                    $candidatureObj = \App\Models\Candidature::where('etudiant_id', $etudiantId)
                        ->when($etudiantObj->email, fn($q) => $q->orWhere('email', $etudiantObj->email))
                        ->when($etudiantObj->user_id, fn($q) => $q->orWhere('user_id', $etudiantObj->user_id))
                        ->first();
                } else {
                    $candidatureObj = \App\Models\Candidature::where('id', $etudiantId)
                        ->orWhere('etudiant_id', $etudiantId)
                        ->first();
                }

                $paiementsInscQuery = \App\Models\Paiement::where(function($q) use ($etudiantId, $candidatureObj) {
                    $q->where('etudiant_id', $etudiantId);
                    if ($candidatureObj) {
                        $q->orWhere(function($q2) use ($candidatureObj) {
                            $q2->where('payable_type', \App\Models\Candidature::class)
                               ->where('payable_id', $candidatureObj->id);
                        });
                    }
                });

                $countPaiementsTotal = (clone $paiementsInscQuery)->count();
                $sumPaiementsTotal = (clone $paiementsInscQuery)->sum('montant');

                $paiementsInscFiltre = (clone $paiementsInscQuery)->where(function($q) use ($fraisInscTable) {
                    $q->where('nature_paiement', 'inscription')
                      ->orWhere('reference', 'LIKE', 'REG-%')
                      ->orWhere('reference', 'LIKE', 'INS-%')
                      ->orWhere('reference', 'LIKE', 'CAN-%');
                    if ($fraisInscTable) {
                        $q->orWhere(function($q2) use ($fraisInscTable) {
                            $q2->where('payable_type', \App\Models\FraisInscription::class)
                               ->where('payable_id', $fraisInscTable->id);
                        });
                    } else {
                        $q->orWhere('payable_type', \App\Models\FraisInscription::class);
                    }
                });

                $countPaiementsInsc = (clone $paiementsInscFiltre)->count();
                $sumPaiementsInsc = (clone $paiementsInscFiltre)->sum('montant');

                if ($montantInscription > 0) {
                    $inscriptionPayeeDirect = $sumPaiementsInsc >= $montantInscription || $sumPaiementsTotal >= $montantInscription;
                } else {
                    $inscriptionPayeeDirect = $countPaiementsInsc > 0 || $countPaiementsTotal > 0;
                }

                // La validation exige un paiement effectif dans la table `paiements` ou via les tranches
                $inscriptionPayee = $inscriptionPayeeParTranche || $inscriptionPayeeDirect;

                if ($montantInscription <= 0 && !$inscriptionPayee) {
                    $trancheInsc = $tranchesInsc->firstWhere('statut', '!=', 'paye');
                    if ($trancheInsc) {
                        $montantInscription = $trancheInsc['reste'];
                    }
                }

                return [
                    'montant_total' => $montantTotal,
                    'total_paye' => $totalPaye,
                    'reste_a_payer' => $montantTotal - $totalPaye,
                    'pourcentage' => $pourcentage,
                    'inscription_payee' => $inscriptionPayee,
                    'frais_inscription_paye' => $inscriptionPayee,
                    'montant_inscription' => $montantInscription,
                    'nombre_tranches' => $tranches->count(),
                    'tranches_payees' => $tranches->where('statut', 'paye')->count(),
                    'tranches_partiellement_payees' => $tranches->where('statut', 'partiel')->count(),
                    'tranches_en_attente' => $tranches->where('statut', 'en_attente')->count(),
                    'tranches_en_retard' => $tranches->where('statut', 'en_retard')->count(),
                ];
            }
        } catch (Exception $e) {
            Log::error('Erreur getRecap: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Récupère l'historique des paiements d'un étudiant
     */
    public function getHistorique($etudiantId, $anneeScolaireId = null)
    {
        try {
            $query = Paiement::where('etudiant_id', $etudiantId)
                ->where(function($q) {
                    $q->where('status', 'valide')
                      ->orWhereNull('status');
                });

            if ($anneeScolaireId) {
                $query->where(function($q) use ($anneeScolaireId) {
                    $q->where('annee_scolaire_id', $anneeScolaireId)
                      ->orWhereNull('annee_scolaire_id');
                });
            }

            $paiements = $query->with(['payable'])
                ->orderBy('created_at', 'desc')
                ->get();

            return $paiements->map(function ($paiement) {
                $libelle = $this->getLibellePayable($paiement->payable);
                $typePayable = $this->getTypePayable($paiement->payable);

                return [
                    'id' => $paiement->id,
                    'date_formatted' => $paiement->created_at->format('d/m/Y H:i'),
                    'montant' => (float) $paiement->montant,
                    'mode_paiement' => $paiement->mode_paiement,
                    'mode_label' => Paiement::MODES_PAIEMENT[$paiement->mode_paiement] ?? $paiement->mode_paiement,
                    'reference' => $paiement->reference,
                    'recu' => $paiement->recu, // On ajoute le champ recu ici !
                    'commentaire' => $paiement->commentaire,
                    'libelle' => $libelle,
                    'type_payable' => $typePayable,
                    'nature_paiement' => $paiement->nature_paiement,
                    'payable_id' => $paiement->payable_id,
                    'frais_retrait_mm' => (float) ($paiement->frais_retrait_mm ?? 0),
                    'status' => $paiement->status,
                    'status_label' => Paiement::STATUTS[$paiement->status] ?? $paiement->status,
                    'justificatif' => $paiement->justificatif ? asset('storage/' . $paiement->justificatif) : null,
                ];
            })->values()->toArray();
        } catch (Exception $e) {
            Log::error('Erreur getHistorique: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Récupère le libellé d'un payable
     */
    private function getLibellePayable($payable)
    {
        if (!$payable) return 'Paiement';

        if ($payable instanceof \App\Models\Echeance) {
            return $payable->libelle;
        }

        if ($payable instanceof \App\Models\TranchePaiement) {
            return $payable->libelle;
        }

        if ($payable instanceof \App\Models\FraisInscription) {
            return $payable->libelle ?? "Frais d'inscription";
        }

        return 'Paiement';
    }

    /**
     * Récupère le type d'un payable
     */
    private function getTypePayable($payable)
    {
        if (!$payable) return 'inconnu';

        if ($payable instanceof \App\Models\Echeance) {
            return 'echeance';
        }

        if ($payable instanceof \App\Models\TranchePaiement) {
            return 'tranche';
        }

        if ($payable instanceof \App\Models\FraisInscription) {
            return 'frais_inscription';
        }

        return 'autre';
    }

    /**
     * Crée un paiement en attente (pour initialisation mobile money/carte)
     */
    public function creerPaiementEnAttente($etudiantId, $montant, $modePaiement, $naturePaiement = 'scolarite', $payableId = null, $payableType = null, $commentaire = null)
    {
        $anneeScolaireId = getAnneeScolaireId();
        $etudiant = Etudiant::findOrFail($etudiantId);
        $payable = $this->determinerPayable($etudiantId, $payableId, $payableType, $anneeScolaireId, $naturePaiement);

        if (!$payable) {
            throw new Exception("Impossible de déterminer l'élément à payer.");
        }

        $finalAnneeId = $anneeScolaireId;
        if ($payable instanceof \App\Models\Echeance && $payable->fraisEtudiant) {
            $finalAnneeId = $payable->fraisEtudiant->annee_scolaire_id;
        } elseif ($payable instanceof \App\Models\FraisInscription) {
            $finalAnneeId = $payable->annee_scolaire_id;
        } elseif ($payable instanceof \App\Models\TranchePaiement) {
            $finalAnneeId = $payable->annee_scolaire_id ?? $finalAnneeId;
        }

        $paiement = new Paiement();
        $paiement->etudiant_id = $etudiantId;
        $paiement->annee_scolaire_id = $finalAnneeId;
        $paiement->montant = $montant;
        $paiement->mode_paiement = $modePaiement;
        $paiement->nature_paiement = $naturePaiement;
        $paiement->commentaire = $commentaire;
        $paiement->status = 'en_attente';
        $paiement->date_paiement = now();
        $paiement->payable_type = get_class($payable);
        $paiement->payable_id = $payable->id;
        // On génère une référence temporaire qui sera remplacée par celle de SEMOA
        $paiement->reference = 'PEND-' . strtoupper(uniqid());
        $paiement->save();

        return $paiement;
    }

    /**
     * Traite un nouveau paiement
     */
    public function traiterPaiement($etudiantId, $montant, $modePaiement, $reference = null, $payableId = null, $payableType = null, $naturePaiement = 'scolarite', $fraisRetraitMM = 0, $commentaire = null, $justificatif = null, $anneeScolaireId = null)
    {
        DB::beginTransaction();

        try {
            $anneeScolaireId = $anneeScolaireId ?? getAnneeScolaireId();

            // Vérifier l'étudiant
            $etudiant = Etudiant::findOrFail($etudiantId);

            // Déterminer le payable
            $payable = $this->determinerPayable($etudiantId, $payableId, $payableType, $anneeScolaireId, $naturePaiement);

            if (!$payable) {
                if ($naturePaiement === 'inscription') {
                    throw new Exception("L'étudiant est déjà à jour pour ses frais d'inscription ou aucun frais d'inscription actif n'a été trouvé.");
                }
                throw new Exception("Impossible de déterminer l'élément à payer (tous les frais sont peut-être déjà soldés).");
            }

            // Vérifier que le montant ne dépasse pas le reste à payer global ou de l'élément
            if ($naturePaiement === 'scolarite') {
                $infos = $this->getInfosPaiement($etudiantId, $anneeScolaireId);
                $resteGlobalScolarite = $infos['total']['reste_a_payer'] ?? 0;

                if ($resteGlobalScolarite <= 0) {
                    throw new Exception("Tous les frais de scolarité de cet étudiant sont déjà entièrement réglés.");
                }

                if ($montant > $resteGlobalScolarite) {
                    throw new Exception("Le montant saisi (" . number_format($montant, 0, ',', ' ') . " FCFA) dépasse le reste total à payer pour la scolarité (" . number_format($resteGlobalScolarite, 0, ',', ' ') . " FCFA).");
                }
            } elseif ($naturePaiement === 'inscription') {
                $recap = $this->getRecap($etudiantId, $anneeScolaireId);
                if (!empty($recap['inscription_payee'])) {
                    throw new Exception("Les frais d'inscription pour cet étudiant ont déjà été entièrement réglés pour cette année scolaire.");
                }

                $resteAPayer = $this->calculerResteAPayer($payable, $etudiantId, $anneeScolaireId);

                if ($resteAPayer <= 0) {
                    throw new Exception("Les frais d'inscription de cet étudiant sont déjà entièrement réglés.");
                }

                if ($montant > $resteAPayer) {
                    throw new Exception("Le montant saisi (" . number_format($montant, 0, ',', ' ') . " FCFA) dépasse le reste à payer pour l'inscription (" . number_format($resteAPayer, 0, ',', ' ') . " FCFA)");
                }
            } else {
                $resteAPayer = $this->calculerResteAPayer($payable, $etudiantId, $anneeScolaireId);

                if ($resteAPayer <= 0) {
                    $libelle = $this->getLibellePayable($payable);
                    throw new Exception("L'élément \"$libelle\" est déjà entièrement payé.");
                }

                if ($montant > $resteAPayer) {
                    throw new Exception("Le montant saisi (" . number_format($montant, 0, ',', ' ') . " FCFA) dépasse le reste à payer (" . number_format($resteAPayer, 0, ',', ' ') . " FCFA)");
                }
            }

            $finalAnneeId = $anneeScolaireId;
            if ($payable instanceof \App\Models\Echeance && $payable->fraisEtudiant) {
                $finalAnneeId = $payable->fraisEtudiant->annee_scolaire_id;
            } elseif ($payable instanceof \App\Models\FraisInscription) {
                $finalAnneeId = $payable->annee_scolaire_id;
            } elseif ($payable instanceof \App\Models\TranchePaiement) {
                $finalAnneeId = $payable->annee_scolaire_id ?? $finalAnneeId;
            }

            if (!$finalAnneeId) {
                $finalAnneeId = getAnneeScolaireId();
            }

            // Créer le paiement
            $paiement = new Paiement();
            $paiement->etudiant_id = $etudiantId;
            $paiement->annee_scolaire_id = $finalAnneeId;
            $paiement->montant = $montant;
            $paiement->mode_paiement = $modePaiement;
            $paiement->nature_paiement = $naturePaiement;
            $paiement->frais_retrait_mm = $fraisRetraitMM ?? 0;
            $paiement->commentaire = $commentaire;
            $paiement->reference = $reference;
            $paiement->justificatif = $justificatif;
            $paiement->status = 'valide';
            $paiement->date_paiement = now();
            $paiement->payable_type = get_class($payable);
            $paiement->payable_id = $payable->id;
            $paiement->save();

            // Mettre à jour le statut du payable si nécessaire
            $this->mettreAJourStatutPayable($payable, $etudiantId);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Paiement effectué avec succès',
                'paiement' => [
                    'id' => $paiement->id,
                    'montant' => (float) $paiement->montant,
                    'mode_paiement' => $paiement->mode_paiement,
                    'nature_paiement' => $paiement->nature_paiement,
                    'reference' => $paiement->reference,
                    'date_paiement' => $paiement->date_paiement->format('Y-m-d H:i:s'),
                ],
                'infos' => $this->getInfosPaiement($etudiantId, $anneeScolaireId),
                'recap' => $this->getRecap($etudiantId, $anneeScolaireId),
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur traiterPaiement: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Détermine le payable concerné par le paiement
     */
    private function determinerPayable($etudiantId, $payableId, $payableType, $anneeScolaireId, $naturePaiement = 'scolarite')
    {
        // 1. Si un payableId et payableType sont spécifiés
        if ($payableId && $payableType) {
            if ($payableType === 'echeance' || $payableType === \App\Models\Echeance::class) {
                $e = \App\Models\Echeance::find($payableId);
                if ($e) return $e;
            }
            if ($payableType === 'tranche' || $payableType === \App\Models\TranchePaiement::class) {
                $t = \App\Models\TranchePaiement::find($payableId);
                if ($t) return $t;
            }
            if ($payableType === 'frais_inscription' || $payableType === \App\Models\FraisInscription::class) {
                $f = \App\Models\FraisInscription::find($payableId);
                if ($f) return $f;
            }
        }

        // 2. Si la nature est inscription
        if ($naturePaiement === 'inscription') {
            $fraisInsc = $this->getFraisInscriptionForEtudiant($etudiantId, $anneeScolaireId);
            
            if ($fraisInsc) {
                return $fraisInsc;
            }
        }

        // 3. Fallback sur la première échéance ou tranche non payée
        $fraisEtudiant = $this->getFraisNegocie($etudiantId, $anneeScolaireId);

        if ($fraisEtudiant) {
            return $fraisEtudiant->echeances()
                ->where('statut', '!=', 'paye')
                ->orderBy('date_limite')
                ->first();
        } else {
            $etudiant = Etudiant::with([
                'etudiantGroups' => function ($query) use ($anneeScolaireId) {
                    $query->where('annee_scolaire_id', $anneeScolaireId)
                        ->with(['niveau', 'filiere']);
                }
            ])->find($etudiantId);

            if (!$etudiant) return null;

            $infos = $this->getInfosFraisParDefaut($etudiant, $anneeScolaireId);
            if (!empty($infos['tranches'])) {
                $premiereTranche = collect($infos['tranches'])->firstWhere('statut', '!=', 'paye') ?? $infos['tranches'][0] ?? null;
                if ($premiereTranche) {
                    return \App\Models\TranchePaiement::find($premiereTranche['id']);
                }
            }
        }

        return null;
    }

    /**
     * Calcule le reste à payer pour un payable
     */
    private function calculerResteAPayer($payable, $etudiantId, $anneeScolaireId = null)
    {
        if ($payable instanceof \App\Models\Echeance) {
            return $payable->reste_a_payer;
        }

        if ($payable instanceof \App\Models\TranchePaiement) {
            $totalPaye = Paiement::where('payable_type', TranchePaiement::class)
                ->where('payable_id', $payable->id)
                ->where('etudiant_id', $etudiantId)
                ->where('status', 'valide')
                ->sum('montant');

            // Récupérer la bourse pour ajuster le montant
            $anneeScolaireId = $anneeScolaireId ?? getAnneeScolaireId();
            $bourseEtudiant = $this->getBourseEtudiant($etudiantId, $anneeScolaireId);
            $coefficient = $this->calculerCoefficientBourse($bourseEtudiant);
            $montantApresBourse = round($payable->montant * $coefficient);

            return $montantApresBourse - $totalPaye;
        }

        if ($payable instanceof \App\Models\FraisInscription) {
            $totalPaye = Paiement::where('payable_type', FraisInscription::class)
                ->where('payable_id', $payable->id)
                ->where('etudiant_id', $etudiantId)
                ->where('status', 'valide')
                ->sum('montant');
            
            return (float)($payable->montant - $totalPaye);
        }

        return 0;
    }

    /**
     * Récupère le paramétrage des frais d'inscription approprié pour un étudiant
     */
    private function getFraisInscriptionForEtudiant($etudiantId, $anneeScolaireId = null)
    {
        $anneeScolaireId = $anneeScolaireId ?? getAnneeScolaireId();
        
        $etudiant = Etudiant::with(['etudiantGroups.niveau', 'etudiantGroups.filiere'])->find($etudiantId);
        $niveauId = null;
        $filiereId = null;
        
        if ($etudiant && $etudiant->etudiantGroups->isNotEmpty()) {
            $dernierGroupe = $etudiant->etudiantGroups->first();
            $niveauId = $dernierGroupe->niveau->id ?? null;
            $filiereId = $dernierGroupe->filiere->id ?? null;
        }

        $query = FraisInscription::where('annee_scolaire_id', $anneeScolaireId)->where('active', true);

        // Priorité 1: Niveau ET Filière
        if ($niveauId && $filiereId) {
            $frais = (clone $query)->where('niveau_id', $niveauId)->where('filiere_id', $filiereId)->first();
            if ($frais) return $frais;
        }

        // Priorité 2: Niveau uniquement
        if ($niveauId) {
            $frais = (clone $query)->where('niveau_id', $niveauId)->whereNull('filiere_id')->first();
            if ($frais) return $frais;
        }

        // Priorité 3: Filière uniquement
        if ($filiereId) {
            $frais = (clone $query)->whereNull('niveau_id')->where('filiere_id', $filiereId)->first();
            if ($frais) return $frais;
        }

        // Priorité 4: Global (aucun niveau, aucune filière)
        $fraisGlobal = (clone $query)->whereNull('niveau_id')->whereNull('filiere_id')->first();
        if ($fraisGlobal) return $fraisGlobal;

        // Fallback ultime: le premier tarif global (sans filtre spécifique)
        return FraisInscription::where('active', true)->whereNull('niveau_id')->whereNull('filiere_id')->first()
            ?? FraisInscription::where('annee_scolaire_id', $anneeScolaireId)->whereNull('niveau_id')->whereNull('filiere_id')->first()
            ?? FraisInscription::whereNull('niveau_id')->whereNull('filiere_id')->latest()->first();
    }

    /**
     * Met à jour le statut d'un payable après paiement
     */
    private function mettreAJourStatutPayable($payable, $etudiantId)
    {
        if ($payable instanceof \App\Models\Echeance) {
            if ($payable->fraisEtudiant) {
                $this->recalculerEcheancesNegociees($payable->fraisEtudiant, $etudiantId);
            } else {
                $payable->updateMontantPaye();
            }
        } elseif ($payable instanceof \App\Models\FraisInscription) {
            // Frais d'inscription
        }
    }

    /**
     * Recalcule et ventile automatiquement les paiements sur les échéances négociées
     */
    private function recalculerEcheancesNegociees($fraisEtudiant, $etudiantId)
    {
        $totalPayeScolarite = Paiement::where('etudiant_id', $etudiantId)
            ->where('status', 'valide')
            ->where(function($q) {
                $q->where('payable_type', \App\Models\Echeance::class)
                  ->orWhere('nature_paiement', 'scolarite');
            })
            ->sum('montant');

        $resteVentilation = $totalPayeScolarite;

        $echeances = $fraisEtudiant->echeances()->orderBy('ordre')->orderBy('id')->get();

        foreach ($echeances as $echeance) {
            $du = (float) $echeance->montant;
            $paye = min($resteVentilation, $du);
            $resteVentilation = max(0, $resteVentilation - $paye);

            $echeance->montant_paye = $paye;
            $echeance->updateStatut();
            $echeance->save();
        }

        $fraisEtudiant->updateStatut();
    }

    /**
     * Récupère les informations du frais d'inscription actif
     */
    private function getFraisInscriptionInfos($etudiantId, $anneeScolaireId)
    {
        $frais = $this->getFraisInscriptionForEtudiant($etudiantId, $anneeScolaireId);

        if (!$frais) return null;

        $totalPaye = Paiement::where('payable_type', FraisInscription::class)
            ->where('payable_id', $frais->id)
            ->where('etudiant_id', $etudiantId)
            ->where('status', 'valide')
            ->sum('montant');

        return [
            'id' => $frais->id,
            'libelle' => $frais->libelle ?? "Frais d'inscription",
            'montant' => (float) $frais->montant,
            'total_paye' => (float) $totalPaye,
            'reste' => (float) ($frais->montant - $totalPaye),
            'statut' => $totalPaye >= $frais->montant ? 'paye' : ($totalPaye > 0 ? 'partiel' : 'en_attente'),
        ];
    }
    /**
     * Modifie un paiement existant
     */
    public function modifierPaiement($paiementId, $data, $justificatif = null)
    {
        DB::beginTransaction();

        try {
            $paiement = Paiement::findOrFail($paiementId);
            $etudiantId = $paiement->etudiant_id;
            $payable = $paiement->payable;

            // Mettre à jour les champs autorisés
            if (isset($data['montant'])) {
                // Vérifier que le nouveau montant ne fait pas dépasser le total
                // On calcule le reste sans compter ce paiement là
                $totalSaufCePaiement = Paiement::where('etudiant_id', $etudiantId)
                    ->where('payable_type', get_class($payable))
                    ->where('payable_id', $payable->id)
                    ->where('id', '!=', $paiementId)
                    ->where('status', 'valide')
                    ->sum('montant');
                
                // Montant max autorisé
                $montantMaxTotal = $this->getMontantTotalPayable($payable, $etudiantId);
                $nouveauReste = $montantMaxTotal - $totalSaufCePaiement;

                if ($data['montant'] > $nouveauReste) {
                     throw new Exception("Le nouveau montant dépasse le reste à payer autorisé (" . number_format($nouveauReste, 0, ',', ' ') . " FCFA)");
                }

                $paiement->montant = $data['montant'];
            }

            if (isset($data['mode_paiement'])) {
                $paiement->mode_paiement = $data['mode_paiement'];
            }

            if (isset($data['reference'])) {
                $paiement->reference = $data['reference'];
            }

            if (isset($data['commentaire'])) {
                $paiement->commentaire = $data['commentaire'];
            }

            if (isset($data['frais_retrait_mm'])) {
                $paiement->frais_retrait_mm = $data['frais_retrait_mm'];
            }

            if ($justificatif) {
                $paiement->justificatif = $justificatif;
            }

            $paiement->save();

            // Mettre à jour le statut du payable
            $this->mettreAJourStatutPayable($payable, $etudiantId);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Paiement modifié avec succès',
                'paiement' => $paiement
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur modifierPaiement: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Récupère le montant total autorisé pour un payable
     */
    private function getMontantTotalPayable($payable, $etudiantId)
    {
        if ($payable instanceof \App\Models\Echeance) {
            return $payable->montant;
        }

        if ($payable instanceof \App\Models\TranchePaiement) {
            $anneeScolaireId = AnneeScolaire::courante()->id;
            $bourseEtudiant = $this->getBourseEtudiant($etudiantId, $anneeScolaireId);
            $coefficient = $this->calculerCoefficientBourse($bourseEtudiant);
            return round($payable->montant * $coefficient);
        }

        if ($payable instanceof \App\Models\FraisInscription) {
            return $payable->montant;
        }

        return 0;
    }
}
