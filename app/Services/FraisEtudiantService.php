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

    /**
     * Synchronise le contrat financier d'un étudiant après affectation ou retrait d'une bourse.
     */
    public function synchroniserBourse(FraisEtudiant $fraisEtudiant)
    {
        return DB::transaction(function () use ($fraisEtudiant) {
            $montantInitial = $fraisEtudiant->montant_initial;
            
            $bourseEtudiant = \App\Models\BourseEtudiant::where('etudiant_id', $fraisEtudiant->etudiant_id)
                ->where('annee_scolaire_id', $fraisEtudiant->annee_scolaire_id)
                ->with('bourse')
                ->first();

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

            $fraisEtudiant->update([
                'montant_apres_bourse' => $montantNet,
                'bourse_etudiant_id' => $bourseId
            ]);

            // Verrouiller les échéances partiellement ou totalement payées
            $echeancesPayees = $fraisEtudiant->echeances()
                ->where('montant_paye', '>', 0)
                ->whereRaw('montant > montant_paye')
                ->get();

            foreach ($echeancesPayees as $echeance) {
                $echeance->update([
                    'montant' => $echeance->montant_paye,
                    'statut' => 'paye'
                ]);
            }

            // Calcul du reste à payer
            $totalPaye = $fraisEtudiant->echeances()->sum('montant_paye');
            $resteAPayerGlobal = max(0, $montantNet - $totalPaye);

            // Répartir le reste sur les échéances non payées
            $echeancesNonPayees = $fraisEtudiant->echeances()
                ->where('montant_paye', 0)
                ->orderBy('ordre')
                ->get();

            if ($echeancesNonPayees->count() > 0) {
                if ($resteAPayerGlobal <= 0) {
                    // S'il n'y a plus rien à payer, on supprime les échéances futures
                    foreach ($echeancesNonPayees as $echeance) {
                        $echeance->delete();
                    }
                } else {
                    // Sinon, on lisse le nouveau montant sur les échéances restantes
                    $nouveauMontantParEcheance = $resteAPayerGlobal / $echeancesNonPayees->count();
                    foreach ($echeancesNonPayees as $echeance) {
                        $echeance->update(['montant' => $nouveauMontantParEcheance]);
                    }
                }
            }

            $fraisEtudiant->updateStatut();
            
            return $fraisEtudiant;
        });
    }

    /**
     * Synchronise les frais d'un etudiant apres modification de son profil
     * (genre, mode_formation, groupe/niveau/filiere).
     * Detecte si le tarif applicable a change et recalcule en protegeant les paiements existants.
     */
    public function synchroniserApresModificationProfil(Etudiant $etudiant, $anneeScolaireId = null)
    {
        return DB::transaction(function () use ($etudiant, $anneeScolaireId) {
            $anneeId = $anneeScolaireId ?? \App\Models\AnneeScolaire::where('active', true)->value('id');

            // 1. Recuperer le groupe actuel de l'etudiant
            $groupInfo = DB::table('etudiant_group')
                ->where('etudiant_id', $etudiant->id)
                ->where('annee_scolaire_id', $anneeId)
                ->first();

            if (!$groupInfo) {
                \Log::info("Sync profil: Pas de groupe pour l'etudiant ID: {$etudiant->id}");
                return null;
            }

            // 2. Trouver le nouveau tarif applicable
            $genreValue = ($etudiant->genre instanceof \UnitEnum) ? $etudiant->genre->value : ($etudiant->genre ?? 'Tous');
            $modeFormation = ($groupInfo->mode_formation instanceof \UnitEnum) ? $groupInfo->mode_formation->value : ($groupInfo->mode_formation ?? 'Tous');

            $nouveauFraisBase = FraisScolarite::getFraisForEtudiant(
                $groupInfo->niveau_id,
                $genreValue,
                $groupInfo->filiere_id,
                $anneeId,
                $modeFormation
            );

            if (!$nouveauFraisBase) {
                \Log::info("Sync profil: Aucun tarif trouve pour l'etudiant ID: {$etudiant->id}");
                return null;
            }

            // 3. Recuperer le contrat financier existant
            $fraisEtudiant = FraisEtudiant::where('etudiant_id', $etudiant->id)
                ->where('annee_scolaire_id', $anneeId)
                ->first();

            if (!$fraisEtudiant) {
                // Pas de contrat existant -> en creer un depuis zero
                return $this->assignDefaultFrais($etudiant, $anneeId);
            }

            // 4. Comparer : si le tarif de base n'a pas change, rien a faire
            if ($fraisEtudiant->frais_scolarite_id == $nouveauFraisBase->id 
                && $fraisEtudiant->montant_initial == $nouveauFraisBase->montant) {
                \Log::info("Sync profil: Aucun changement de tarif pour l'etudiant ID: {$etudiant->id}");
                return $fraisEtudiant;
            }

            \Log::info("Sync profil: Changement de tarif detecte pour l'etudiant ID: {$etudiant->id}", [
                'ancien_tarif' => $fraisEtudiant->montant_initial,
                'nouveau_tarif' => $nouveauFraisBase->montant,
                'ancien_frais_scolarite_id' => $fraisEtudiant->frais_scolarite_id,
                'nouveau_frais_scolarite_id' => $nouveauFraisBase->id,
            ]);

            // 5. Recalculer le montant apres bourse avec le NOUVEAU tarif de base
            $nouveauMontantInitial = $nouveauFraisBase->montant;
            $nouveauMontantNet = $nouveauMontantInitial;

            $bourseEtudiant = \App\Models\BourseEtudiant::where('etudiant_id', $etudiant->id)
                ->where('annee_scolaire_id', $anneeId)
                ->with('bourse')
                ->first();

            $bourseId = null;
            if ($bourseEtudiant && $bourseEtudiant->bourse) {
                $bourse = $bourseEtudiant->bourse;
                $bourseId = $bourseEtudiant->id;
                if ($bourse->type === 'pourcentage') {
                    $reduction = ($nouveauMontantInitial * $bourse->valeur) / 100;
                    $nouveauMontantNet = max(0, $nouveauMontantInitial - $reduction);
                } else {
                    $nouveauMontantNet = max(0, $nouveauMontantInitial - $bourse->valeur);
                }
            }

            // 6. Mettre a jour le contrat
            $fraisEtudiant->update([
                'frais_scolarite_id' => $nouveauFraisBase->id,
                'montant_initial' => $nouveauMontantInitial,
                'montant_apres_bourse' => $nouveauMontantNet,
                'bourse_etudiant_id' => $bourseId,
            ]);

            // 7. Reequilibrer les echeances en protegeant les paiements existants
            // Verrouiller les echeances partiellement payees
            $echeancesPartielles = $fraisEtudiant->echeances()
                ->where('montant_paye', '>', 0)
                ->whereRaw('montant > montant_paye')
                ->get();

            foreach ($echeancesPartielles as $echeance) {
                $echeance->update([
                    'montant' => $echeance->montant_paye,
                    'statut' => 'paye'
                ]);
            }

            // Calculer le reste a payer
            $totalPaye = $fraisEtudiant->echeances()->sum('montant_paye');
            $resteAPayerGlobal = max(0, $nouveauMontantNet - $totalPaye);

            // Repartir le reste sur les echeances non payees
            $echeancesNonPayees = $fraisEtudiant->echeances()
                ->where('montant_paye', 0)
                ->orderBy('ordre')
                ->get();

            if ($echeancesNonPayees->count() > 0) {
                if ($resteAPayerGlobal <= 0) {
                    foreach ($echeancesNonPayees as $echeance) {
                        $echeance->delete();
                    }
                } else {
                    $nouveauMontantParEcheance = $resteAPayerGlobal / $echeancesNonPayees->count();
                    foreach ($echeancesNonPayees as $echeance) {
                        $echeance->update(['montant' => $nouveauMontantParEcheance]);
                    }
                }
            }

            $fraisEtudiant->updateStatut();

            return $fraisEtudiant;
        });
    }
}
