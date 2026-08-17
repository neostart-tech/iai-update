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
            $groupe = $etudiant->etudiantGroups()->where('annee_scolaire_id', $anneeId)->first();
            if (!$groupe) {
                $groupe = $etudiant->etudiantGroups()->latest('id')->first();
            }
            $modeFormation = ($groupe && $groupe->mode_formation instanceof \UnitEnum) 
                                ? $groupe->mode_formation->value 
                                : ($groupe->mode_formation ?? 'Présentiel');

            $results[$etudiant->id]['dash'] = $montant;
            $results[$etudiant->id]['nom'] = $etudiant->nom . ' ' . $etudiant->prenom;
            $results[$etudiant->id]['slug'] = $etudiant->slug;
            $results[$etudiant->id]['mode_formation'] = $modeFormation;
            $results[$etudiant->id]['niveau'] = $groupe?->niveau?->libelle ?? 'Non assigné';
        }

        // 2. Calcul via logique Situation
        $etudiantsSit = Etudiant::all();
        $totalSit = 0;
        foreach ($etudiantsSit as $etudiant) {
            $sitInfo = $this->getMontantSitInfo($etudiant, $anneeId);
            $montant = $sitInfo['montant'];
            $totalSit += $montant;
            $results[$etudiant->id]['sit'] = $montant;
            $results[$etudiant->id]['tarif_existant'] = $sitInfo['tarif_existant'];
            if(!isset($results[$etudiant->id]['nom'])) {
                $groupe = $etudiant->etudiantGroups()->where('annee_scolaire_id', $anneeId)->first();
                if (!$groupe) {
                    $groupe = $etudiant->etudiantGroups()->latest('id')->first();
                }
                $modeFormation = ($groupe && $groupe->mode_formation instanceof \UnitEnum) 
                                    ? $groupe->mode_formation->value 
                                    : ($groupe->mode_formation ?? 'Présentiel');

                $results[$etudiant->id]['nom'] = $etudiant->nom . ' ' . $etudiant->prenom;
                $results[$etudiant->id]['slug'] = $etudiant->slug;
                $results[$etudiant->id]['mode_formation'] = $modeFormation;
                $results[$etudiant->id]['niveau'] = $groupe?->niveau?->libelle ?? 'Non assigné';
            }
        }

        // 3. Comparaison
        $differences = [];
        foreach ($results as $id => $data) {
            $dash = $data['dash'] ?? 0;
            $sit = $data['sit'] ?? 0;
            $tarifExistant = $data['tarif_existant'] ?? true;

            // Une anomalie est soit un écart de montant, soit une grille non paramétrée
            if ($dash != $sit || !$tarifExistant) {
                $differences[] = [
                    'id' => $id,
                    'slug' => $data['slug'],
                    'nom' => $data['nom'],
                    'mode_formation' => $data['mode_formation'] ?? 'Présentiel',
                    'niveau' => $data['niveau'] ?? 'Non assigné',
                    'dash' => $dash,
                    'sit' => $sit,
                    'diff' => $sit - $dash,
                    'tarif_existant' => $tarifExistant
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

    public function verifierAnomalieEtudiant($etudiant, $anneeId) {
        // Un étudiant sans contrat spécifique (frais_etudiants) suit la scolarité académique standard par défaut.
        $fraisEtudiant = $etudiant->fraisEtudiant()->where('annee_scolaire_id', $anneeId)->first();
        if (!$fraisEtudiant) {
            return ['has_anomalie' => false];
        }

        $dash = (float) $fraisEtudiant->montant_apres_bourse;
        $sitInfo = $this->getMontantSitInfo($etudiant, $anneeId);
        
        if (!$sitInfo['tarif_existant']) {
            if ($dash == 0) {
                return ['has_anomalie' => false];
            }
            return [
                'has_anomalie' => true,
                'type_anomalie' => 'tarif_manquant',
                'dash' => $dash,
                'sit' => 0,
                'diff' => 0 - $dash,
                'tarif_existant' => false,
                'message' => 'Aucun tarif officiel paramétré dans la grille pour ce profil (' . ($etudiant->mode_formation ?? 'Mode non défini') . ').'
            ];
        }

        if (abs($dash - (float)$sitInfo['montant']) > 0.01) {
            return [
                'has_anomalie' => true,
                'type_anomalie' => 'incoherence_tarif',
                'dash' => $dash,
                'sit' => (float) $sitInfo['montant'],
                'diff' => (float) ($sitInfo['montant'] - $dash),
                'tarif_existant' => true,
            ];
        }
        
        return ['has_anomalie' => false];
    }

    private function getMontantDash($etudiant, $anneeId) {
        $fraisEtudiant = $etudiant->fraisEtudiant()->where('annee_scolaire_id', $anneeId)->first();
        if ($fraisEtudiant) {
            return (float) $fraisEtudiant->montant_apres_bourse;
        }
        $sitInfo = $this->getMontantSitInfo($etudiant, $anneeId);
        return (float) $sitInfo['montant'];
    }

    public function getMontantSitInfo($etudiant, $anneeId) {
        $groupe = $etudiant->etudiantGroups()->where('annee_scolaire_id', $anneeId)->first();
        if (!$groupe) {
            $groupe = $etudiant->etudiantGroups()->latest('id')->first();
        }
        if (!$groupe || !$groupe->niveau_id) {
            return ['montant' => 0, 'tarif_existant' => false];
        }
        
        $modeFormation = null;
        if ($groupe->mode_formation) {
            $modeFormation = ($groupe->mode_formation instanceof \UnitEnum) 
                                ? $groupe->mode_formation->value 
                                : $groupe->mode_formation;
        }
        if (!$modeFormation || $modeFormation === 'Tous') {
            if (isset($etudiant->mode_formation)) {
                $modeFormation = ($etudiant->mode_formation instanceof \UnitEnum)
                                    ? $etudiant->mode_formation->value
                                    : $etudiant->mode_formation;
            }
        }
        $modeFormation = $modeFormation ?: 'Présentiel';
                            
        $genreValue = ($etudiant->genre instanceof \UnitEnum) ? $etudiant->genre->value : ($etudiant->genre ?? 'Tous');

        $fraisScolarite = FraisScolarite::getFraisForEtudiant($groupe->niveau_id, $genreValue, $groupe->filiere_id, $anneeId, $modeFormation);
        
        if ($fraisScolarite) {
            $baseMontant = (float) $fraisScolarite->montant;
            
            // Verifier s'il y a une vraie bourse
            $bourseEtudiant = \App\Models\BourseEtudiant::where('etudiant_id', $etudiant->id)
                ->where('annee_scolaire_id', $anneeId)
                ->with('bourse')
                ->first();

            if ($bourseEtudiant && $bourseEtudiant->bourse) {
                $bourse = $bourseEtudiant->bourse;
                if ($bourse->type === 'pourcentage') {
                    $montant = max(0, round($baseMontant * (1 - $bourse->valeur / 100)));
                } else {
                    $montant = max(0, $baseMontant - $bourse->valeur);
                }
                return ['montant' => $montant, 'tarif_existant' => true];
            }

            return ['montant' => $baseMontant, 'tarif_existant' => true];
        }

        // Aucun tarif trouvé dans la grille pour ce profil
        return ['montant' => 0, 'tarif_existant' => false];
    }

    private function getMontantSit($etudiant, $anneeId) {
        $info = $this->getMontantSitInfo($etudiant, $anneeId);
        return $info['montant'];
    }

    /**
     * Detecte les etudiants dont le montant facturé ne correspond pas au tarif académique attendu
     * (tarif officiel de la grille moins bourse officielle).
     */
    public function detecterAnomaliesTarif()
    {
        $anneeId = getAnneeScolaireId();
        $anomalies = [];

        $fraisEtudiants = FraisEtudiant::with(['etudiant'])
            ->where('annee_scolaire_id', $anneeId)
            ->get();

        foreach ($fraisEtudiants as $fe) {
            $etudiant = $fe->etudiant;
            if (!$etudiant) continue;

            $groupe = $etudiant->etudiantGroups()
                ->where('annee_scolaire_id', $anneeId)
                ->first();

            if (!$groupe || !$groupe->niveau_id) continue;

            $genreValue = ($etudiant->genre instanceof \UnitEnum) ? $etudiant->genre->value : ($etudiant->genre ?? 'Tous');
            $modeFormation = ($groupe->mode_formation instanceof \UnitEnum) ? $groupe->mode_formation->value : ($groupe->mode_formation ?? 'Tous');

            $tarifApplicable = FraisScolarite::getFraisForEtudiant(
                $groupe->niveau_id,
                $genreValue,
                $groupe->filiere_id,
                $anneeId,
                $modeFormation
            );

            if (!$tarifApplicable) continue;

            // Calcul du montant officiel attendu (tarif grille - bourse officielle)
            $bourseEtudiant = \App\Models\BourseEtudiant::where('etudiant_id', $etudiant->id)
                ->where('annee_scolaire_id', $anneeId)
                ->with('bourse')
                ->first();

            $montantAttendu = (float) $tarifApplicable->montant;
            if ($bourseEtudiant && $bourseEtudiant->bourse) {
                $bourse = $bourseEtudiant->bourse;
                if ($bourse->type === 'pourcentage') {
                    $montantAttendu = max(0, round($tarifApplicable->montant * (1 - $bourse->valeur / 100)));
                } else {
                    $montantAttendu = max(0, $tarifApplicable->montant - $bourse->valeur);
                }
            }

            // Toute divergence entre le montant facturé et le montant académique attendu est une alerte
            if ($fe->montant_apres_bourse != $montantAttendu) {
                $anomalies[] = [
                    'etudiant_id' => $etudiant->id,
                    'slug' => $etudiant->slug,
                    'nom' => $etudiant->nom . ' ' . $etudiant->prenom,
                    'matricule' => $etudiant->matricule,
                    'montant_stocke' => $fe->montant_initial,
                    'montant_apres_bourse_stocke' => $fe->montant_apres_bourse,
                    'tarif_applicable' => $tarifApplicable->montant,
                    'montant_attendu' => $montantAttendu,
                    'ecart' => $fe->montant_apres_bourse - $montantAttendu,
                    'frais_scolarite_id_stocke' => $fe->frais_scolarite_id,
                    'frais_scolarite_id_applicable' => $tarifApplicable->id,
                ];
            }
        }

        return [
            'nb_anomalies' => count($anomalies),
            'anomalies' => $anomalies,
        ];
    }

    /**
     * Corrige automatiquement toutes les anomalies de tarif detectees.
     */
    public function corrigerAnomalies()
    {
        $diagnostic = $this->detecterAnomaliesTarif();
        $corriges = 0;
        $erreurs = [];

        $fraisService = new \App\Services\FraisEtudiantService();

        foreach ($diagnostic['anomalies'] as $anomalie) {
            try {
                $etudiant = Etudiant::find($anomalie['etudiant_id']);
                if ($etudiant) {
                    $fraisService->synchroniserApresModificationProfil($etudiant);
                    $corriges++;
                }
            } catch (\Exception $e) {
                $erreurs[] = [
                    'etudiant_id' => $anomalie['etudiant_id'],
                    'nom' => $anomalie['nom'],
                    'erreur' => $e->getMessage(),
                ];
            }
        }

        return [
            'nb_anomalies_detectees' => $diagnostic['nb_anomalies'],
            'nb_corrigees' => $corriges,
            'nb_erreurs' => count($erreurs),
            'erreurs' => $erreurs,
        ];
    }
}
