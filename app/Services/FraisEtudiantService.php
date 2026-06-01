<?php

namespace App\Services;

use App\Models\Etudiant;
use App\Models\FraisScolarite;
use App\Models\AnneeScolaire;
use App\Models\FraisEtudiant;
use App\Models\Echeancier;
use App\Models\Echeance;
use Illuminate\Support\Facades\DB;
use Str;

class FraisEtudiantService
{
    /**
     * Assigne automatiquement les frais de scolarité à un étudiant
     * et génère son échéancier individuel en tenant compte des bourses.
     */
    public function assignDefaultFrais(Etudiant $etudiant, $anneeScolaireId = null)
    {
        return DB::transaction(function () use ($etudiant, $anneeScolaireId) {
            $anneeId = $anneeScolaireId ?? AnneeScolaire::where('active', true)->value('id');

            // 1. Récupérer le groupe actuel de l'étudiant
            $groupInfo = DB::table('etudiant_group')
                ->where('etudiant_id', $etudiant->id)
                ->where('annee_scolaire_id', $anneeId)
                ->first();

            if (!$groupInfo) {
                \Log::warning("Assignation frais échouée: Pas de groupe trouvé pour l'étudiant ID: {$etudiant->id}");
                return null;
            }

            // 2. Trouver le tarif correspondant
            $fraisBase = FraisScolarite::getFraisForEtudiant(
                $groupInfo->niveau_id,
                $etudiant->genre?->value ?? 'Tous',
                $groupInfo->filiere_id,
                $anneeId,
                $groupInfo->mode_formation ?? 'Tous'
            );

            if (!$fraisBase) {
                \Log::warning("Assignation frais échouée: Aucun tarif global trouvé pour l'étudiant ID: {$etudiant->id}");
                return null;
            }

            // 3. Vérifier s'il y a une bourse spécifique pour cet étudiant cette année
            $bourseEtudiant = \App\Models\BourseEtudiant::where('etudiant_id', $etudiant->id)
                ->where('annee_scolaire_id', $anneeId)
                ->with('bourse')
                ->first();

            $montantInitial = $fraisBase->montant;
            $montantNet = $montantInitial;
            $bourseId = null;

            if ($bourseEtudiant && $bourseEtudiant->bourse) {
                $bourse = $bourseEtudiant->bourse;
                $bourseId = $bourseEtudiant->id;
                
                if ($bourse->type === 'pourcentage') {
                    $reduction = ($montantInitial * $bourse->valeur) / 100;
                    $montantNet = max(0, $montantInitial - $reduction);
                } else {
                    $montantNet = max(0, $montantInitial - $bourse->valeur);
                }
            }

            // Charger les tranches pour plus tard
            $fraisBase->load('tranchepaiement');

            // 4. Créer ou Mettre à jour le contrat FraisEtudiant
            $fraisEtudiant = FraisEtudiant::updateOrCreate(
                [
                    'etudiant_id' => $etudiant->id,
                    'annee_scolaire_id' => $anneeId,
                ],
                [
                    'slug' => (string) Str::uuid(),
                    'frais_scolarite_id' => $fraisBase->id,
                    'bourse_etudiant_id' => $bourseId,
                    'montant_initial' => $montantInitial,
                    'montant_apres_bourse' => $montantNet,
                    'frequence_paiement' => $fraisBase->frequence ?? 'trimestriel',
                    'statut' => 'en_cours',
                ]
            );

            // 5. Générer l'échéancier si nécessaire
            if ($fraisEtudiant->wasRecentlyCreated || $fraisEtudiant->echeances()->count() == 0) {
                $this->generateEcheancierFromFraisScolarite($fraisEtudiant, $fraisBase);
            }

            return $fraisEtudiant;
        });
    }

    /**
     * Copie les tranches du tarif global vers l'échéancier personnel de l'étudiant,
     * en ajustant les montants au prorata s'il y a une bourse.
     */
    private function generateEcheancierFromFraisScolarite(FraisEtudiant $fraisEtudiant, FraisScolarite $fraisBase)
    {
        // Créer l'entête de l'échéancier
        $echeancier = Echeancier::updateOrCreate(
            ['frais_etudiant_id' => $fraisEtudiant->id],
            [
                'slug' => (string) Str::uuid(),
                'date_creation' => now(),
                'commentaire' => 'Généré automatiquement lors de l\'inscription.' . ($fraisEtudiant->bourse_etudiant_id ? ' (Tarif réduit avec bourse)' : '')
            ]
        );

        // Supprimer les anciennes échéances s'il y en a (sécurité)
        $fraisEtudiant->echeances()->delete();

        // Calculer le ratio de réduction (si montant Net < montant Initial)
        $ratio = 1;
        if ($fraisEtudiant->montant_initial > 0) {
            $ratio = $fraisEtudiant->montant_apres_bourse / $fraisEtudiant->montant_initial;
        }

        // Copier les tranches en appliquant le ratio
        $tranches = $fraisBase->tranchepaiement;
        $idx = 0;
        foreach ($tranches as $tranche) {
            Echeance::create([
                'slug' => (string) Str::uuid(),
                'echeancier_id' => $echeancier->id,
                'frais_etudiant_id' => $fraisEtudiant->id,
                'libelle' => $tranche->libelle,
                'montant' => round($tranche->montant * $ratio), // Appliquer le ratio de bourse
                'date_limite' => $tranche->date_limite,
                'ordre' => $idx++,
                'statut' => 'en_attente'
            ]);
        }
    }
}
