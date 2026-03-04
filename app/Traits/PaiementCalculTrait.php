<?php
// app/Traits/PaiementCalculTrait.php

namespace App\Traits;

use App\Models\FraisEtudiant;
use App\Models\FraisScolarite;
use App\Models\BourseEtudiant;
use Carbon\Carbon;

trait PaiementCalculTrait
{
    /**
     * Récupère les informations de paiement pour un étudiant
     */
    public function getInfosPaiementEtudiant($etudiant)
    {
        $anneeScolaireId = $this->getAnneeScolaireId();
        
        // Vérifier si l'étudiant a un frais négocié
        $fraisEtudiant = FraisEtudiant::with(['echeances.paiements'])
            ->where('etudiant_id', $etudiant->id)
            ->where('annee_scolaire_id', $anneeScolaireId)
            ->first();

        if ($fraisEtudiant) {
            return $this->getInfosDepuisFraisNegocie($fraisEtudiant, $etudiant);
        }

        // Sinon, utiliser les frais standard
        return $this->getInfosDepuisFraisStandard($etudiant, $anneeScolaireId);
    }

    /**
     * Récupère l'ID de l'année scolaire active
     */
    public function getAnneeScolaireId()
    {
        // À adapter selon votre logique (session, config, etc.)
        // Par exemple, si vous stockez l'année scolaire en session:
        if (session()->has('annee_scolaire_id')) {
            return session('annee_scolaire_id');
        }
        
        // Sinon, prenez la dernière année scolaire active
        // return \App\Models\AnneeScolaire::where('is_active', true)->first()->id ?? 1;
        
        return 1; // Valeur par défaut
    }

    /**
     * Infos depuis un frais négocié
     */
    protected function getInfosDepuisFraisNegocie($fraisEtudiant, $etudiant)
    {
        $dernierGroupe = $etudiant->etudiantGroups->first();
        
        $totalPaye = 0;
        $dernierPaiement = null;
        $prochaineDateLimite = null;

        foreach ($fraisEtudiant->echeances as $echeance) {
            $totalPaye += $echeance->montant_paye;
            
            if ($echeance->paiements->isNotEmpty()) {
                $dernierPaiement = $echeance->paiements->sortByDesc('date_paiement')->first()->date_paiement;
            }

            if ($echeance->reste_a_payer > 0) {
                if (!$prochaineDateLimite || $echeance->date_limite < $prochaineDateLimite) {
                    $prochaineDateLimite = $echeance->date_limite;
                }
            }
        }

        // Déterminer le statut
        $statut = $this->determinerStatut($fraisEtudiant);

        return [
            'niveau' => $dernierGroupe && $dernierGroupe->niveau ? $dernierGroupe->niveau->libelle : 'N/A',
            'filiere' => $dernierGroupe && $dernierGroupe->filiere ? $dernierGroupe->filiere->nom : 'N/A',
            'montant_initial' => $fraisEtudiant->montant_initial,
            'montant_apres_bourse' => $fraisEtudiant->montant_apres_bourse,
            'total_paye' => $totalPaye,
            'reste_a_payer' => $fraisEtudiant->montant_apres_bourse - $totalPaye,
            'statut' => $statut,
            'dernier_paiement' => $dernierPaiement,
            'prochaine_date_limite' => $prochaineDateLimite,
        ];
    }

    /**
     * Infos depuis les frais standard
     */
    protected function getInfosDepuisFraisStandard($etudiant, $anneeScolaireId)
    {
        $dernierGroupe = $etudiant->etudiantGroups->first();
        
        if (!$dernierGroupe) {
            return $this->getInfosVide($etudiant);
        }

        $niveauId = $dernierGroupe->niveau_id ?? null;
        $filiereId = $dernierGroupe->filiere_id ?? null;
        $genre = $etudiant->genre;

        if (!$niveauId) {
            return $this->getInfosVide($etudiant);
        }

        // Récupérer le frais de scolarité
        $fraisScolarite = FraisScolarite::where('niveau_id', $niveauId)
            ->where('annee_scolaire_id', $anneeScolaireId)
            ->where(function($q) use ($genre, $filiereId) {
                $q->where('genre', $genre)->orWhere('genre', 'Tous');
                if ($filiereId) {
                    $q->where('filiere_id', $filiereId)->orWhereNull('filiere_id');
                }
            })
            ->orderBy('genre', 'desc')
            ->first();

        if (!$fraisScolarite) {
            return $this->getInfosVide($etudiant);
        }

        // Récupérer les tranches et paiements
        $tranches = $fraisScolarite->tranchepaiement;
        $totalPaye = 0;
        $dernierPaiement = null;
        $prochaineDateLimite = null;

        foreach ($tranches as $tranche) {
            $paiements = $tranche->paiements()
                ->where('etudiant_id', $etudiant->id)
                ->where('status', 'valide')
                ->get();
            
            $payeTranche = $paiements->sum('montant');
            $totalPaye += $payeTranche;

            if ($paiements->isNotEmpty()) {
                $dernierPaiement = $paiements->sortByDesc('date_paiement')->first()->date_paiement;
            }

            if ($tranche->montant > $payeTranche) {
                if (!$prochaineDateLimite || $tranche->date_limite < $prochaineDateLimite) {
                    $prochaineDateLimite = $tranche->date_limite;
                }
            }
        }

        $montantTotal = $tranches->sum('montant');
        
        // Appliquer la bourse si existante
        $bourseEtudiant = BourseEtudiant::with('bourse')
            ->where('etudiant_id', $etudiant->id)
            ->where('annee_scolaire_id', $anneeScolaireId)
            ->first();

        $montantApresBourse = $montantTotal;
        if ($bourseEtudiant && $bourseEtudiant->bourse) {
            if ($bourseEtudiant->bourse->type === 'pourcentage') {
                $montantApresBourse = $montantTotal * (1 - $bourseEtudiant->bourse->valeur / 100);
            } else {
                $montantApresBourse = max(0, $montantTotal - $bourseEtudiant->bourse->valeur);
            }
        }

        // Déterminer le statut
        $reste = $montantApresBourse - $totalPaye;
        $statut = 'en_cours';
        
        if ($reste <= 0) {
            $statut = 'solde';
        } elseif ($prochaineDateLimite && Carbon::now()->gt($prochaineDateLimite)) {
            $statut = 'en_retard';
        }

        return [
            'niveau' => $dernierGroupe->niveau ? $dernierGroupe->niveau->libelle : 'N/A',
            'filiere' => $dernierGroupe->filiere ? $dernierGroupe->filiere->nom : 'N/A',
            'montant_initial' => $montantTotal,
            'montant_apres_bourse' => $montantApresBourse,
            'total_paye' => $totalPaye,
            'reste_a_payer' => $montantApresBourse - $totalPaye,
            'statut' => $statut,
            'dernier_paiement' => $dernierPaiement,
            'prochaine_date_limite' => $prochaineDateLimite,
        ];
    }

    /**
     * Infos vides (quand l'étudiant n'a pas de frais)
     */
    protected function getInfosVide($etudiant)
    {
        $dernierGroupe = $etudiant->etudiantGroups->first();
        
        return [
            'niveau' => $dernierGroupe && $dernierGroupe->niveau ? $dernierGroupe->niveau->libelle : 'N/A',
            'filiere' => $dernierGroupe && $dernierGroupe->filiere ? $dernierGroupe->filiere->nom : 'N/A',
            'montant_initial' => 0,
            'montant_apres_bourse' => 0,
            'total_paye' => 0,
            'reste_a_payer' => 0,
            'statut' => 'aucun_frais',
            'dernier_paiement' => null,
            'prochaine_date_limite' => null,
        ];
    }

    /**
     * Détermine le statut d'un étudiant
     */
    protected function determinerStatut($fraisEtudiant)
    {
        if ($fraisEtudiant->statut === 'solde') return 'solde';
        
        $aDesRetards = false;
        foreach ($fraisEtudiant->echeances as $echeance) {
            if ($echeance->est_en_retard) {
                $aDesRetards = true;
                break;
            }
        }
        
        return $aDesRetards ? 'en_retard' : 'en_cours';
    }
}