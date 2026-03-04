<?php

namespace App\Services;

use App\Models\Etudiant;
use App\Models\FraisEtudiant;
use App\Models\Echeance;
use App\Models\TranchePaiement;
use App\Models\Paiement;
use App\Models\FraisScolarite;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class PaiementIntelligentService
{
    /**
     * Traiter un paiement pour un étudiant
     */
    public function traiterPaiement($etudiantId, $montant, $data = [])
    {
        return DB::transaction(function () use ($etudiantId, $montant, $data) {
            // Récupérer l'année scolaire en cours
            $anneeScolaireId = $this->getAnneeScolaireId();
            
            // Vérifier si l'étudiant a une négociation pour l'année en cours
            $fraisEtudiant = $this->getNegociationActive($etudiantId, $anneeScolaireId);
            
            if ($fraisEtudiant) {
                // Cas 1: L'étudiant a une négociation
                return $this->traiterPaiementNegociation($fraisEtudiant, $montant, $data);
            } else {
                // Cas 2: L'étudiant n'a pas de négociation → utiliser les tranches globales
                return $this->traiterPaiementGlobal($etudiantId, $anneeScolaireId, $montant, $data);
            }
        });
    }

    /**
     * Récupérer l'année scolaire active
     */
    private function getAnneeScolaireId()
    {
        $annee = \App\Models\AnneeScolaire::where('active', true)->first();
        return $annee ? $annee->id : null;
    }

    /**
     * Récupérer la négociation active d'un étudiant
     */
    private function getNegociationActive($etudiantId, $anneeScolaireId)
    {
        return FraisEtudiant::where('etudiant_id', $etudiantId)
            ->where('annee_scolaire_id', $anneeScolaireId)
            ->where('type_paiement', 'negociation')
            ->first();
    }

    /**
     * Formater le montant pour les messages
     */
    private function formatMontant($montant)
    {
        return number_format($montant, 0, ',', ' ') . ' FCFA';
    }

    /**
     * Traiter un paiement pour une négociation existante
     */
    private function traiterPaiementNegociation($fraisEtudiant, $montant, $data)
    {
        // Récupérer les échéances non soldées dans l'ordre
        $echeances = Echeance::where('frais_etudiant_id', $fraisEtudiant->id)
            ->where('statut', '!=', 'paye')
            ->orderBy('ordre')
            ->get();

        if ($echeances->isEmpty()) {
            throw new Exception("Aucune échéance à payer pour cette négociation");
        }

        $montantRestant = $montant;
        $paiementsEffectues = [];
        $detailsEcheances = [];

        // Répartir le paiement sur les échéances
        foreach ($echeances as $echeance) {
            if ($montantRestant <= 0) break;

            $resteEcheance = $echeance->reste_a_payer;
            
            if ($resteEcheance <= 0) continue;

            $montantAEcheance = min($montantRestant, $resteEcheance);

            // Créer le paiement pour cette échéance
            $paiement = $this->creerPaiement([
                'etudiant_id' => $fraisEtudiant->etudiant_id,
                'montant' => $montantAEcheance,
                'mode_paiement' => $data['mode_paiement'] ?? 'especes',
                'reference' => $data['reference'] ?? null,
                'justificatif' => $data['justificatif'] ?? null,
                'date_paiement' => $data['date_paiement'] ?? now(),
                'payable_type' => 'App\\Models\\Echeance',
                'payable_id' => $echeance->id
            ]);

            $paiementsEffectues[] = $paiement;
            $montantRestant -= $montantAEcheance;

            $detailsEcheances[] = [
                'echeance_id' => $echeance->id,
                'libelle' => $echeance->libelle,
                'montant_paye' => $montantAEcheance,
                'nouveau_reste' => $echeance->reste_a_payer - $montantAEcheance
            ];

            // Mettre à jour l'échéance
            $echeance->updateMontantPaye();
        }

        // Mettre à jour le statut global du frais étudiant
        $fraisEtudiant->updateStatut();
        
        // Recharger le frais étudiant avec ses relations
        $fraisEtudiant->load(['echeances', 'etudiant']);

        return [
            'success' => true,
            'type' => 'negociation',
            'frais_etudiant' => [
                'id' => $fraisEtudiant->id,
                'etudiant' => [
                    'id' => $fraisEtudiant->etudiant->id,
                    'nom' => $fraisEtudiant->etudiant->nom,
                    'prenom' => $fraisEtudiant->etudiant->prenom,
                    'matricule' => $fraisEtudiant->etudiant->matricule
                ],
                'montant_total' => $fraisEtudiant->montant_apres_bourse,
                'total_paye' => $fraisEtudiant->total_paye,
                'reste_a_payer' => $fraisEtudiant->reste_a_payer,
                'statut' => $fraisEtudiant->statut
            ],
            'paiements' => collect($paiementsEffectues)->map(function($p) {
                return [
                    'id' => $p->id,
                    'montant' => $p->montant,
                    'mode_paiement' => $p->mode_paiement,
                    'reference' => $p->reference,
                    'date_paiement' => $p->date_paiement->format('Y-m-d H:i:s'),
                    'payable_type' => class_basename($p->payable_type),
                    'payable_id' => $p->payable_id
                ];
            }),
            'details_echeances' => $detailsEcheances,
            'montant_total' => $montant,
            'montant_utilise' => $montant - $montantRestant,
            'montant_restant' => $montantRestant,
            'message' => $this->genererMessage(count($paiementsEffectues), $montant - $montantRestant, $montantRestant)
        ];
    }

    /**
     * Traiter un paiement pour un étudiant sans négociation (tranches globales)
     */
    private function traiterPaiementGlobal($etudiantId, $anneeScolaireId, $montant, $data)
    {
        // Récupérer le frais de scolarité de l'étudiant pour l'année en cours
        $fraisEtudiant = FraisEtudiant::where('etudiant_id', $etudiantId)
            ->where('annee_scolaire_id', $anneeScolaireId)
            ->where('type_paiement', 'tranches_globales')
            ->first();

        // Si aucun frais étudiant n'existe, le créer automatiquement
        if (!$fraisEtudiant) {
            $fraisEtudiant = $this->creerFraisEtudiantGlobal($etudiantId, $anneeScolaireId);
        }

        // Récupérer les échéances non soldées dans l'ordre
        $echeances = Echeance::where('frais_etudiant_id', $fraisEtudiant->id)
            ->where('statut', '!=', 'paye')
            ->orderBy('ordre')
            ->get();

        if ($echeances->isEmpty()) {
            throw new Exception("Aucune échéance à payer");
        }

        $montantRestant = $montant;
        $paiementsEffectues = [];
        $detailsEcheances = [];

        // Répartir le paiement sur les échéances
        foreach ($echeances as $echeance) {
            if ($montantRestant <= 0) break;

            $resteEcheance = $echeance->reste_a_payer;
            
            if ($resteEcheance <= 0) continue;

            $montantAEcheance = min($montantRestant, $resteEcheance);

            // Créer le paiement pour cette échéance
            $paiement = $this->creerPaiement([
                'etudiant_id' => $etudiantId,
                'montant' => $montantAEcheance,
                'mode_paiement' => $data['mode_paiement'] ?? 'especes',
                'reference' => $data['reference'] ?? null,
                'justificatif' => $data['justificatif'] ?? null,
                'date_paiement' => $data['date_paiement'] ?? now(),
                'payable_type' => 'App\\Models\\Echeance',
                'payable_id' => $echeance->id
            ]);

            $paiementsEffectues[] = $paiement;
            $montantRestant -= $montantAEcheance;

            $detailsEcheances[] = [
                'echeance_id' => $echeance->id,
                'libelle' => $echeance->libelle,
                'montant_paye' => $montantAEcheance,
                'nouveau_reste' => $echeance->reste_a_payer - $montantAEcheance
            ];

            // Mettre à jour l'échéance
            $echeance->updateMontantPaye();
        }

        // Mettre à jour le statut global du frais étudiant
        $fraisEtudiant->updateStatut();
        
        // Recharger le frais étudiant avec ses relations
        $fraisEtudiant->load(['echeances', 'etudiant']);

        return [
            'success' => true,
            'type' => 'global',
            'frais_etudiant' => [
                'id' => $fraisEtudiant->id,
                'etudiant' => [
                    'id' => $fraisEtudiant->etudiant->id,
                    'nom' => $fraisEtudiant->etudiant->nom,
                    'prenom' => $fraisEtudiant->etudiant->prenom,
                    'matricule' => $fraisEtudiant->etudiant->matricule
                ],
                'montant_total' => $fraisEtudiant->montant_apres_bourse,
                'total_paye' => $fraisEtudiant->total_paye,
                'reste_a_payer' => $fraisEtudiant->reste_a_payer,
                'statut' => $fraisEtudiant->statut
            ],
            'paiements' => collect($paiementsEffectues)->map(function($p) {
                return [
                    'id' => $p->id,
                    'montant' => $p->montant,
                    'mode_paiement' => $p->mode_paiement,
                    'reference' => $p->reference,
                    'date_paiement' => $p->date_paiement->format('Y-m-d H:i:s'),
                    'payable_type' => class_basename($p->payable_type),
                    'payable_id' => $p->payable_id
                ];
            }),
            'details_echeances' => $detailsEcheances,
            'montant_total' => $montant,
            'montant_utilise' => $montant - $montantRestant,
            'montant_restant' => $montantRestant,
            'message' => $this->genererMessage(count($paiementsEffectues), $montant - $montantRestant, $montantRestant)
        ];
    }

    /**
     * Créer un frais étudiant pour le mode global
     */
    private function creerFraisEtudiantGlobal($etudiantId, $anneeScolaireId)
    {
        $etudiant = Etudiant::with(['niveau'])->findOrFail($etudiantId);
        
        // Récupérer le frais de scolarité approprié selon le niveau
        $fraisScolarite = FraisScolarite::where('niveau_id', $etudiant->niveau_id)
            ->where('annee_scolaire_id', $anneeScolaireId)
            ->first();

        if (!$fraisScolarite) {
            throw new Exception("Aucun frais de scolarité trouvé pour cet étudiant");
        }

        // Vérifier si des tranches existent
        $tranches = TranchePaiement::where('frais_scolarite_id', $fraisScolarite->id)->count();
        if ($tranches === 0) {
            throw new Exception("Aucune tranche de paiement définie pour ce niveau");
        }

        // Créer le frais étudiant
        $fraisEtudiant = FraisEtudiant::create([
            'etudiant_id' => $etudiantId,
            'frais_scolarite_id' => $fraisScolarite->id,
            'annee_scolaire_id' => $anneeScolaireId,
            'montant_initial' => $fraisScolarite->montant,
            'montant_apres_bourse' => $fraisScolarite->montant,
            'type_paiement' => 'tranches_globales',
            'frequence_paiement' => 'annuel',
            'statut' => 'en_cours'
        ]);

        // Créer les échéances à partir des tranches globales
        $fraisEtudiant->creerEcheancesDepuisTranchesGlobales();

        return $fraisEtudiant;
    }

    /**
     * Créer un paiement
     */
    private function creerPaiement($data)
    {
        $paiement = new Paiement();
        $paiement->etudiant_id = $data['etudiant_id'];
        $paiement->montant = $data['montant'];
        $paiement->mode_paiement = $data['mode_paiement'];
        $paiement->reference = $data['reference'];
        $paiement->justificatif = $data['justificatif'];
        $paiement->status = 'valide';
        $paiement->date_paiement = $data['date_paiement'];
        $paiement->payable_type = $data['payable_type'];
        $paiement->payable_id = $data['payable_id'];
        $paiement->save();

        return $paiement;
    }

    /**
     * Générer un message récapitulatif
     */
    private function genererMessage($nbPaiements, $montantUtilise, $montantRestant)
    {
        if ($montantRestant == 0) {
            if ($nbPaiements == 1) {
                return "Paiement de " . $this->formatMontant($montantUtilise) . " enregistré avec succès";
            } else {
                return "Paiement réparti sur $nbPaiements échéances avec succès";
            }
        } else {
            return "Paiement partiel de " . $this->formatMontant($montantUtilise) . " enregistré. Reste à payer: " . $this->formatMontant($montantRestant);
        }
    }
}