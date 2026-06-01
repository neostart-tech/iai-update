<?php

namespace App\Services;

use App\Models\Etudiant;
use App\Models\FraisEtudiant;
use App\Models\FraisScolarite;
use App\Models\AnneeScolaire;
use Illuminate\Support\Facades\DB;

class DiagnosticFinancierService
{
    public function comparerCalculs()
    {
        $anneeId = getAnneeScolaireId();
        $results = [];

        // 1. Calcul via logique Dashboard
        $etudiantsDash = Etudiant::whereHas('etudiantGroups', function($q) use ($anneeId) {
            $q->where('annee_scolaire_id', $anneeId);
        })->get();

        $totalDash = 0;
        foreach ($etudiantsDash as $etudiant) {
            $montant = $this->getMontantDash($etudiant, $anneeId);
            $totalDash += $montant;
            $results[$etudiant->id]['dash'] = $montant;
            $results[$etudiant->id]['nom'] = $etudiant->nom . ' ' . $etudiant->prenom;
        }

        // 2. Calcul via logique Situation
        $etudiantsSit = Etudiant::all();
        $totalSit = 0;
        foreach ($etudiantsSit as $etudiant) {
            $montant = $this->getMontantSit($etudiant, $anneeId);
            $totalSit += $montant;
            $results[$etudiant->id]['sit'] = $montant;
            if(!isset($results[$etudiant->id]['nom'])) $results[$etudiant->id]['nom'] = $etudiant->nom . ' ' . $etudiant->prenom;
        }

        // 3. Comparaison
        $differences = [];
        foreach ($results as $id => $data) {
            $dash = $data['dash'] ?? 0;
            $sit = $data['sit'] ?? 0;
            if ($dash != $sit) {
                $differences[] = [
                    'id' => $id,
                    'nom' => $data['nom'],
                    'dash' => $dash,
                    'sit' => $sit,
                    'diff' => $sit - $dash
                ];
            }
        }

        return [
            'total_dash' => $totalDash,
            'total_sit' => $totalSit,
            'ecart_total' => $totalSit - $totalDash,
            'nb_differences' => count($differences),
            'details' => $differences
        ];
    }

    private function getMontantDash($etudiant, $anneeId) {
        $groupe = $etudiant->etudiantGroups()->where('annee_scolaire_id', $anneeId)->first();
        if (!$groupe) return 0;
        $fraisEtudiant = $etudiant->fraisEtudiant()->where('annee_scolaire_id', $anneeId)->first();
        $genreValue = ($etudiant->genre instanceof \UnitEnum) ? $etudiant->genre->value : ($etudiant->genre ?? 'Tous');
        $fraisBase = FraisScolarite::getFraisForEtudiant($groupe->niveau_id, $genreValue, $groupe->filiere_id, $anneeId, $groupe->mode_formation ?? 'Tous');
        if ($fraisBase) {
            $montant = $fraisBase->montant;
            if ($fraisEtudiant && $fraisEtudiant->montant_initial > 0) {
                $reduction = $fraisEtudiant->montant_initial - $fraisEtudiant->montant_apres_bourse;
                $montant = max(0, $montant - $reduction);
            }
            return $montant;
        }
        return $fraisEtudiant ? $fraisEtudiant->montant_apres_bourse : 0;
    }

    private function getMontantSit($etudiant, $anneeId) {
        $groupe = $etudiant->etudiantGroups()->where('annee_scolaire_id', $anneeId)->first();
        if (!$groupe || !$groupe->niveau_id) return 0;
        $fraisEtudiant = $etudiant->fraisEtudiant()->where('annee_scolaire_id', $anneeId)->first();
        $fraisScolarite = FraisScolarite::getFraisForEtudiant($groupe->niveau_id, $etudiant->genre?->value ?? 'Tous', $groupe->filiere_id, $anneeId, $groupe->mode_formation ?? 'Tous');
        if ($fraisScolarite) {
            $baseMontant = $fraisScolarite->montant;
            if ($fraisEtudiant && $fraisEtudiant->montant_initial > 0) {
                $reduction = $fraisEtudiant->montant_initial - $fraisEtudiant->montant_apres_bourse;
                if ($reduction > 0) return max(0, $baseMontant - $reduction);
            }
            return $baseMontant;
        }
        if ($fraisEtudiant) return $fraisEtudiant->montant_apres_bourse;
        return 0;
    }
}
