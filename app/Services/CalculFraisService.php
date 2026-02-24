<?php

namespace App\Services;

use App\Models\Etudiant;

class CalculFraisService
{
    public function calculer(Etudiant $etudiant, float $montantInitial): float
    {
        $montant = $montantInitial;

        foreach ($etudiant->bourses as $bourse) {
            if ($bourse->type === 'pourcentage') {
                $montant -= ($montant * $bourse->valeur / 100);
            } else {
                $montant -= $bourse->valeur;
            }
        }

        return max($montant, 0);
    }
}
