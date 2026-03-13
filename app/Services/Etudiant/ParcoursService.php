<?php
// app/Services/Etudiant/ParcoursService.php

namespace App\Services\Etudiant;

use App\Models\Etudiant;
use App\Models\FraisEtudiant;
use App\Models\FraisScolarite;
use App\Models\Paiement;
use App\Models\BourseEtudiant;
use App\Models\Echeance;
use App\Models\TranchePaiement;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ParcoursService
{
    protected $etudiant;
    protected $anneeScolaireId;

    public function __construct(Etudiant $etudiant)
    {
        $this->etudiant = $etudiant;
        $this->anneeScolaireId = getAnneeScolaireId();
    }

    /**
     * Récupère toutes les informations du parcours de l'étudiant
     */
    public function getParcoursComplet()
    {
        return [
            'identite' => $this->getIdentite(),
            'parcours_academique' => $this->getParcoursAcademique(),
            'paiements_par_annee' => $this->getPaiementsParAnnee(),
            'bourses_obtenues' => $this->getBoursesObtenues(),
            'statut_financier' => $this->getStatutFinancier(),
            'historique_annees' => $this->getHistoriqueAnnees(),
            'statistiques' => $this->getStatistiquesGlobales(),
        ];
    }

    /**
     * Informations d'identité
     */
    protected function getIdentite()
    {
        $dateNaissance = null;
        if ($this->etudiant->date_naissance) {
            if (is_object($this->etudiant->date_naissance) && method_exists($this->etudiant->date_naissance, 'format')) {
                $dateNaissance = $this->etudiant->date_naissance->format('d/m/Y');
            } elseif (is_string($this->etudiant->date_naissance)) {
                try {
                    $dateNaissance = Carbon::parse($this->etudiant->date_naissance)->format('d/m/Y');
                } catch (\Exception $e) {
                    $dateNaissance = $this->etudiant->date_naissance;
                }
            }
        }

        return [
            'id' => $this->etudiant->id,
            'nom' => $this->etudiant->nom,
            'prenom' => $this->etudiant->prenom,
            'nom_complet' => $this->etudiant->nom . ' ' . $this->etudiant->prenom,
            'matricule' => $this->etudiant->matricule,
            'email' => $this->etudiant->email,
            'telephone' => $this->etudiant->tel,
            'photo' => $this->etudiant->image,
            'date_naissance' => $dateNaissance,
            'lieu_naissance' => $this->etudiant->lieu_naissance,
            'nationalite' => $this->etudiant->nationalite,
        ];
    }

    /**
     * Parcours académique complet
     */
    protected function getParcoursAcademique()
    {
        $parcours = [];
        
        $groupes = $this->etudiant->etudiantGroups()
            ->with(['niveau', 'filiere', 'group', 'anneeScolaire'])
            ->orderBy('annee_scolaire_id', 'desc')
            ->get();

        foreach ($groupes as $index => $groupe) {
            $anneeScolaire = $groupe->anneeScolaire;
            
            $parcours[] = [
                'annee_scolaire_id' => $groupe->annee_scolaire_id,
                'annee_scolaire' => $anneeScolaire ? $anneeScolaire->nom : 'N/A',
                'niveau' => $groupe->niveau ? $groupe->niveau->libelle : 'N/A',
                'filiere' => $groupe->filiere ? $groupe->filiere->nom : 'N/A',
                'groupe' => $groupe->group ? $groupe->group->nom : 'N/A',
                'est_actuel' => $groupe->annee_scolaire_id == $this->anneeScolaireId,
                'ordre' => $index + 1,
            ];
        }

        return $parcours;
    }

    /**
     * Paiements regroupés par année scolaire
     */
    protected function getPaiementsParAnnee()
    {
        $resultats = [];
        $annees = $this->getAnneesAvecFrais();

        foreach ($annees as $annee) {
            $anneeId = $annee['id'];
            
            $paiements = $this->getPaiementsPourAnnee($anneeId);
            $frais = $this->getFraisPourAnnee($anneeId);
            
            $totalAPayer = $frais['total_a_payer'] ?? 0;
            $totalPaye = $paiements->sum('montant');
            $resteAPayer = $totalAPayer - $totalPaye;
            
            $statut = $this->determinerStatutPourAnnee($anneeId, $totalAPayer, $totalPaye);
            
            $resultats[] = [
                'annee_scolaire_id' => $anneeId,
                'annee_scolaire' => $annee['nom'],
                'est_actuelle' => $anneeId == $this->anneeScolaireId,
                'frais' => $frais,
                'paiements' => $paiements,
                'total_a_payer' => $totalAPayer,
                'total_paye' => $totalPaye,
                'reste_a_payer' => $resteAPayer,
                'statut' => $statut,
                'progression' => $totalAPayer > 0 ? round(($totalPaye / $totalAPayer) * 100, 2) : 0,
            ];
        }

        return $resultats;
    }

    /**
     * Récupère les années où l'étudiant a eu des frais
     */
    protected function getAnneesAvecFrais()
    {
        $annees = [];
        
        $fraisNegocies = FraisEtudiant::where('etudiant_id', $this->etudiant->id)
            ->with('anneeScolaire')
            ->get();

        foreach ($fraisNegocies as $frais) {
            if ($frais->anneeScolaire) {
                $annees[$frais->annee_scolaire_id] = [
                    'id' => $frais->annee_scolaire_id,
                    'nom' => $frais->anneeScolaire->nom,
                ];
            }
        }

        $groupes = $this->etudiant->etudiantGroups()
            ->with('anneeScolaire')
            ->get();

        foreach ($groupes as $groupe) {
            if ($groupe->anneeScolaire && !isset($annees[$groupe->annee_scolaire_id])) {
                $annees[$groupe->annee_scolaire_id] = [
                    'id' => $groupe->annee_scolaire_id,
                    'nom' => $groupe->anneeScolaire->nom,
                ];
            }
        }

        return array_values($annees);
    }

    /**
     * Formate une date de manière sécurisée
     */
    private function formatDateSecurise($date, $format = 'd/m/Y')
    {
        if (!$date) {
            return null;
        }

        try {
            if (is_object($date) && method_exists($date, 'format')) {
                return $date->format($format);
            } elseif (is_string($date)) {
                return Carbon::parse($date)->format($format);
            }
        } catch (\Exception $e) {
            // Si erreur, retourner la date brute ou null
            return is_string($date) ? $date : null;
        }

        return null;
    }

    /**
     * Formate une date avec heure de manière sécurisée
     */
    private function formatDateTimeSecurise($date, $format = 'd/m/Y H:i')
    {
        if (!$date) {
            return null;
        }

        try {
            if (is_object($date) && method_exists($date, 'format')) {
                return $date->format($format);
            } elseif (is_string($date)) {
                return Carbon::parse($date)->format($format);
            }
        } catch (\Exception $e) {
            return is_string($date) ? $date : null;
        }

        return null;
    }

    /**
     * Récupère les frais pour une année spécifique
     */
    protected function getFraisPourAnnee($anneeId)
    {
        $fraisNegocie = FraisEtudiant::with(['echeances', 'bourseEtudiant.bourse'])
            ->where('etudiant_id', $this->etudiant->id)
            ->where('annee_scolaire_id', $anneeId)
            ->first();

        if ($fraisNegocie) {
            return [
                'type' => 'negocie',
                'id' => $fraisNegocie->id,
                'montant_initial' => $fraisNegocie->montant_initial,
                'montant_apres_bourse' => $fraisNegocie->montant_apres_bourse,
                'type_paiement' => $fraisNegocie->type_paiement,
                'frequence_paiement' => $fraisNegocie->frequence_paiement,
                'bourse' => $fraisNegocie->bourseEtudiant ? [
                    'nom' => $fraisNegocie->bourseEtudiant->bourse->nom,
                    'type' => $fraisNegocie->bourseEtudiant->bourse->type,
                    'valeur' => $fraisNegocie->bourseEtudiant->bourse->valeur,
                ] : null,
                'echeances' => $fraisNegocie->echeances->map(function($e) {
                    return [
                        'id' => $e->id,
                        'libelle' => $e->libelle,
                        'montant' => $e->montant,
                        'montant_paye' => $e->montant_paye,
                        'reste' => $e->reste_a_payer,
                        'date_limite' => $this->formatDateSecurise($e->date_limite),
                        'statut' => $e->statut,
                    ];
                }),
                'total_a_payer' => $fraisNegocie->montant_apres_bourse,
            ];
        }

        $groupe = $this->etudiant->etudiantGroups()
            ->where('annee_scolaire_id', $anneeId)
            ->first();

        if (!$groupe || !$groupe->niveau_id) {
            return null;
        }

        $fraisScolarite = FraisScolarite::with('tranchepaiement')
            ->where('niveau_id', $groupe->niveau_id)
            ->where('annee_scolaire_id', $anneeId)
            ->first();

        if (!$fraisScolarite) {
            return null;
        }

        $tranches = $fraisScolarite->tranchepaiement;
        $montantTotal = $tranches->sum('montant');

        $bourse = BourseEtudiant::with('bourse')
            ->where('etudiant_id', $this->etudiant->id)
            ->where('annee_scolaire_id', $anneeId)
            ->first();

        return [
            'type' => 'standard',
            'id' => $fraisScolarite->id,
            'montant_initial' => $montantTotal,
            'montant_apres_bourse' => $this->appliquerBourse($montantTotal, $bourse),
            'bourse' => $bourse ? [
                'nom' => $bourse->bourse->nom,
                'type' => $bourse->bourse->type,
                'valeur' => $bourse->bourse->valeur,
            ] : null,
            'tranches' => $tranches->map(function($t) use ($bourse) {
                $payeTranche = Paiement::where('etudiant_id', $this->etudiant->id)
                    ->where('payable_type', TranchePaiement::class)
                    ->where('payable_id', $t->id)
                    ->where('status', 'valide')
                    ->sum('montant');

                return [
                    'id' => $t->id,
                    'libelle' => $t->libelle,
                    'montant' => $t->montant,
                    'paye' => $payeTranche,
                    'reste' => $t->montant - $payeTranche,
                    'date_limite' => $this->formatDateSecurise($t->date_limite),
                    'statut' => $this->getStatutTranche($t->montant, $payeTranche, $t->date_limite),
                ];
            }),
            'total_a_payer' => $this->appliquerBourse($montantTotal, $bourse),
        ];
    }

    /**
     * Récupère les paiements pour une année spécifique
     */
    protected function getPaiementsPourAnnee($anneeId)
    {
        $paiementsEcheances = Paiement::where('etudiant_id', $this->etudiant->id)
            ->whereHasMorph('payable', [Echeance::class], function($q) use ($anneeId) {
                $q->whereHas('fraisEtudiant', function($q2) use ($anneeId) {
                    $q2->where('annee_scolaire_id', $anneeId);
                });
            })
            ->where('status', 'valide')
            ->with('payable')
            ->get();

        $paiementsTranches = Paiement::where('etudiant_id', $this->etudiant->id)
            ->whereHasMorph('payable', [TranchePaiement::class], function($q) use ($anneeId) {
                $q->whereHas('fraiscolarite', function($q2) use ($anneeId) {
                    $q2->where('annee_scolaire_id', $anneeId);
                });
            })
            ->where('status', 'valide')
            ->with('payable')
            ->get();

        $tousPaiements = $paiementsEcheances->concat($paiementsTranches)
            ->sortByDesc('date_paiement')
            ->values();

        return $tousPaiements->map(function($p) {
            return [
                'id' => $p->id,
                'montant' => $p->montant,
                'mode' => $p->mode_paiement,
                'reference' => $p->reference,
                'date' => $this->formatDateTimeSecurise($p->date_paiement),
                'libelle' => $this->getLibellePayable($p->payable),
                'type' => $p->payable_type == Echeance::class ? 'echeance' : 'tranche',
            ];
        });
    }

    /**
     * Récupère toutes les bourses obtenues
     */
    protected function getBoursesObtenues()
    {
        return BourseEtudiant::with(['bourse', 'anneeScolaire'])
            ->where('etudiant_id', $this->etudiant->id)
            ->orderBy('annee_scolaire_id', 'desc')
            ->get()
            ->map(function($b) {
                return [
                    'id' => $b->id,
                    'nom' => $b->bourse->nom,
                    'type' => $b->bourse->type,
                    'valeur' => $b->bourse->valeur,
                    'description' => $b->bourse->description,
                    'annee_scolaire' => $b->anneeScolaire ? $b->anneeScolaire->nom : 'N/A',
                    'est_active' => $b->annee_scolaire_id == $this->anneeScolaireId,
                ];
            });
    }

    /**
     * Statut financier global
     */
    protected function getStatutFinancier()
    {
        $totalAPayer = 0;
        $totalPaye = 0;

        $annees = $this->getAnneesAvecFrais();
        
        foreach ($annees as $annee) {
            $frais = $this->getFraisPourAnnee($annee['id']);
            if ($frais) {
                $totalAPayer += $frais['total_a_payer'];
            }
            
            $paiements = $this->getPaiementsPourAnnee($annee['id']);
            $totalPaye += $paiements->sum('montant');
        }

        $resteAPayer = $totalAPayer - $totalPaye;

        return [
            'total_a_payer' => $totalAPayer,
            'total_paye' => $totalPaye,
            'reste_a_payer' => $resteAPayer,
            'est_solde' => $resteAPayer <= 0,
            'progression' => $totalAPayer > 0 ? round(($totalPaye / $totalAPayer) * 100, 2) : 0,
            'nombre_annees' => count($annees),
            'nombre_paiements' => $this->getNombreTotalPaiements(),
        ];
    }

    /**
     * Historique par année
     */
    protected function getHistoriqueAnnees()
    {
        $historique = [];
        $annees = $this->getAnneesAvecFrais();

        foreach ($annees as $annee) {
            $frais = $this->getFraisPourAnnee($annee['id']);
            $paiements = $this->getPaiementsPourAnnee($annee['id']);
            $totalPaye = $paiements->sum('montant');
            $totalAPayer = $frais['total_a_payer'] ?? 0;

            $historique[] = [
                'annee_scolaire' => $annee['nom'],
                'annee_id' => $annee['id'],
                'montant_total' => $totalAPayer,
                'montant_paye' => $totalPaye,
                'montant_restant' => $totalAPayer - $totalPaye,
                'statut' => $this->determinerStatutPourAnnee($annee['id'], $totalAPayer, $totalPaye),
                'progression' => $totalAPayer > 0 ? round(($totalPaye / $totalAPayer) * 100, 2) : 0,
                'nombre_paiements' => $paiements->count(),
            ];
        }

        return $historique;
    }

    /**
     * Statistiques globales
     */
    protected function getStatistiquesGlobales()
    {
        $paiements = Paiement::where('etudiant_id', $this->etudiant->id)
            ->where('status', 'valide')
            ->get();

        $premierPaiement = $paiements->sortBy('date_paiement')->first();
        $dernierPaiement = $paiements->sortByDesc('date_paiement')->first();

        return [
            'total_paiements' => $paiements->count(),
            'montant_moyen' => $paiements->avg('montant'),
            'premier_paiement' => $this->formatDateSecurise($premierPaiement?->date_paiement),
            'dernier_paiement' => $this->formatDateSecurise($dernierPaiement?->date_paiement),
            'modes_paiement_utilises' => $paiements->groupBy('mode_paiement')->map->count(),
        ];
    }

    /**
     * Utilitaires
     */
    protected function getNombreTotalPaiements()
    {
        return Paiement::where('etudiant_id', $this->etudiant->id)
            ->where('status', 'valide')
            ->count();
    }

    protected function getLibellePayable($payable)
    {
        if (!$payable) return 'Paiement';
        if ($payable instanceof Echeance) return $payable->libelle;
        if ($payable instanceof TranchePaiement) return $payable->libelle;
        return 'Paiement';
    }

    protected function getStatutTranche($montant, $paye, $dateLimite)
    {
        if ($paye >= $montant) return 'paye';
        if ($paye > 0) return 'partiel';
        
        if ($dateLimite) {
            try {
                $dateLimiteObj = Carbon::parse($dateLimite);
                if ($dateLimiteObj->isPast()) return 'en_retard';
            } catch (\Exception $e) {
                // Ignorer si la date est invalide
            }
        }
        
        return 'en_attente';
    }

    protected function determinerStatutPourAnnee($anneeId, $totalAPayer, $totalPaye)
    {
        if ($totalAPayer == 0) return 'aucun_frais';
        if ($totalPaye >= $totalAPayer) return 'solde';
        
        $fraisNegocie = FraisEtudiant::where('etudiant_id', $this->etudiant->id)
            ->where('annee_scolaire_id', $anneeId)
            ->first();

        if ($fraisNegocie) {
            foreach ($fraisNegocie->echeances as $echeance) {
                if ($echeance->est_en_retard) {
                    return 'en_retard';
                }
            }
            return 'en_cours';
        }

        $groupe = $this->etudiant->etudiantGroups()
            ->where('annee_scolaire_id', $anneeId)
            ->first();

        if ($groupe && $groupe->niveau_id) {
            $fraisScolarite = FraisScolarite::where('niveau_id', $groupe->niveau_id)
                ->where('annee_scolaire_id', $anneeId)
                ->first();

            if ($fraisScolarite) {
                $aujourdhui = Carbon::now();
                foreach ($fraisScolarite->tranchepaiement as $tranche) {
                    $payeTranche = Paiement::where('etudiant_id', $this->etudiant->id)
                        ->where('payable_type', TranchePaiement::class)
                        ->where('payable_id', $tranche->id)
                        ->where('status', 'valide')
                        ->sum('montant');

                    if ($tranche->montant > $payeTranche) {
                        try {
                            $dateLimite = Carbon::parse($tranche->date_limite);
                            if ($aujourdhui->gt($dateLimite)) {
                                return 'en_retard';
                            }
                        } catch (\Exception $e) {
                            // Ignorer si date invalide
                        }
                    }
                }
            }
        }

        return 'en_cours';
    }

    protected function appliquerBourse($montant, $bourseEtudiant)
    {
        if (!$bourseEtudiant || !$bourseEtudiant->bourse) {
            return $montant;
        }

        $bourse = $bourseEtudiant->bourse;

        if ($bourse->type === 'pourcentage') {
            return round($montant * (1 - $bourse->valeur / 100));
        } else {
            return max(0, $montant - $bourse->valeur);
        }
    }
}