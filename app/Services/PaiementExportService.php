<?php
// app/Services/PaiementExportService.php

namespace App\Services;

use App\Exports\PaiementExport;
use App\Models\Etudiant;
use App\Models\Niveau;
use App\Models\Filiere;
use App\Traits\PaiementCalculTrait;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class PaiementExportService
{
    use PaiementCalculTrait;

    /**
     * Exporte les données selon les critères
     */
    public function export($type, $id = null, $periodeDebut = null, $periodeFin = null)
    {
        $export = new PaiementExport($type, $id, $periodeDebut, $periodeFin);
        
        $nomFichier = $this->genererNomFichier($type, $id);
        
        return Excel::download($export, $nomFichier);
    }

    /**
     * Génère le nom du fichier
     */
    private function genererNomFichier($type, $id = null)
    {
        $date = Carbon::now()->format('Y-m-d_H-i');
        
        switch ($type) {
            case 'etudiant':
                $etudiant = Etudiant::find($id);
                $nom = $etudiant ? $etudiant->nom . '_' . $etudiant->prenom : 'etudiant';
                return "paiement_{$nom}_{$date}.xlsx";
            
            case 'niveau':
                $niveau = Niveau::find($id);
                $nom = $niveau ? $niveau->libelle : 'niveau';
                return "paiements_niveau_{$nom}_{$date}.xlsx";
            
            case 'filiere':
                $filiere = Filiere::find($id);
                $nom = $filiere ? $filiere->nom : 'filiere';
                return "paiements_filiere_{$nom}_{$date}.xlsx";
            
            case 'global':
                return "paiements_tous_{$date}.xlsx";
            
            default:
                return "paiements_{$date}.xlsx";
        }
    }

    /**
     * Récupère les données pour le PDF
     */
    public function getExportDataForPDF($type, $id = null, $periodeDebut = null, $periodeFin = null)
    {
        // Récupérer les étudiants
        $query = Etudiant::with([
            'etudiantGroups.niveau',
            'etudiantGroups.filiere',
            'etudiantGroups.group'
        ]);

        // Filtre par type
        switch ($type) {
            case 'etudiant':
                $query->where('id', $id);
                break;
            case 'niveau':
                $query->whereHas('etudiantGroups', function($q) use ($id) {
                    $q->where('niveau_id', $id);
                });
                break;
            case 'filiere':
                $query->whereHas('etudiantGroups', function($q) use ($id) {
                    $q->where('filiere_id', $id);
                });
                break;
            case 'global':
                // Pas de filtre
                break;
        }

        // Exclure les abandons
        $query->whereDoesntHave('fraisEtudiant', function($q) {
            $q->where('est_en_abandon', true);
        });

        $etudiants = $query->get();
        
        $etudiantsData = [];
        $totaux = [
            'montant_initial' => 0,
            'montant_apres_bourse' => 0,
            'total_paye' => 0,
            'reste_a_payer' => 0
        ];

        foreach ($etudiants as $etudiant) {
            // Maintenant cette méthode est disponible grâce au Trait
            $infos = $this->getInfosPaiementEtudiant($etudiant);
            
            $etudiantsData[] = [
                'id' => $etudiant->id,
                'matricule' => $etudiant->matricule,
                'nom' => $etudiant->nom,
                'prenom' => $etudiant->prenom,
                'niveau' => $infos['niveau'],
                'filiere' => $infos['filiere'],
                'montant_initial' => $infos['montant_initial'],
                'montant_apres_bourse' => $infos['montant_apres_bourse'],
                'total_paye' => $infos['total_paye'],
                'reste_a_payer' => $infos['reste_a_payer'],
                'statut' => $infos['statut'],
                'dernier_paiement' => $infos['dernier_paiement'],
            ];

            // Accumuler les totaux
            $totaux['montant_initial'] += $infos['montant_initial'];
            $totaux['montant_apres_bourse'] += $infos['montant_apres_bourse'];
            $totaux['total_paye'] += $infos['total_paye'];
            $totaux['reste_a_payer'] += $infos['reste_a_payer'];
        }

        return [
            'etudiants' => $etudiantsData,
            'totaux' => $totaux
        ];
    }
}