<?php

namespace App\Services;

use App\Models\AnneeScolaire;
use App\Models\Echeancier;
use App\Models\Etudiant;
use App\Models\FraisScolarite;
use App\Models\PlanPaiement;
use Illuminate\Support\Facades\DB;

class GenererEcheancierService
{
    public function generer(Etudiant $etudiant, FraisScolarite $frais, PlanPaiement $plan)
    {
        return DB::transaction(function () use ($etudiant, $frais, $plan) {

            $calculService = new CalculFraisService();
            $montantFinal = $calculService->calculer($etudiant, $frais->montant);
            if ($etudiant->echeancier()->exists()) {
                throw new \Exception("Un échéancier existe déjà pour cet étudiant.");
            }
            $echeancier = Echeancier::create([
                'etudiant_id' => $etudiant->id,
                'frais_scolarite_id' => $frais->id,
                'plan_paiement_id' => $plan->id,
                'montant_total' => $montantFinal,
                'reste_a_payer' => $montantFinal,
            ]);

            if ($plan->type_plan === 'standard') {

                $dateDebut = AnneeScolaire::courante()->date->debut;

                $montantBase = floor($montantFinal / $plan->nombre_tranches);
                $totalCalcule = 0;

                for ($i = 1; $i <= $plan->nombre_tranches; $i++) {

                    $montant = $montantBase;

                    if ($i == $plan->nombre_tranches) {
                        $montant = $montantFinal - $totalCalcule;
                    }

                    $echeancier->echeances()->create([
                        'libelle' => "Tranche $i",
                        'montant' => $montant,
                        'date_limite' => $dateDebut->copy()->addMonths($i - 1),
                    ]);

                    $totalCalcule += $montant;
                }
            };
            // for ($i = 1; $i <= $plan->nombre_tranches; $i++) {
            //     $echeancier->echeances()->create([
            //         'libelle' => "Tranche $i",
            //         'montant' => $montantParTranche,
            //         'date_limite' => now()->addMonths($i),
            //     ]);
            // }
            if ($plan->type_plan === 'tranches_fixes') {

                $dateDebut = AnneeScolaire::courante()->date->debut;

                foreach ($plan->tranches as $tranche) {

                    $montant = ($montantFinal * $tranche->pourcentage) / 100;

                    $echeancier->echeances()->create([
                        'libelle' => "Tranche " . $tranche->ordre,
                        'montant' => $montant,
                        'date_limite' => $dateDebut->copy()->addMonths($tranche->mois_apres_debut),
                    ]);
                }
            }

            if ($plan->type_plan === 'negociation') {
                return $echeancier;
            }

            return $echeancier;
        });
    }
}
