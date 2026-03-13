<?php
// app/Exports/EtudiantSituationExport.php

namespace App\Exports;

use App\Services\EtudiantSituationService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class EtudiantSituationExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, ShouldAutoSize
{
    protected $etudiants;
    protected $service;

    public function __construct(array $filtres = [])
    {
        $this->service = new EtudiantSituationService();
        $this->etudiants = $this->service->getSituationEtudiants($filtres);
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return collect($this->etudiants);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Matricule',
            'Nom',
            'Prénom',
            'Email',
            'Téléphone',
            'Filière',
            'Niveau',
            'Statut',
            'Montant Total',
            'Montant Payé',
            'Montant Restant',
            'Taux Progression',
            'En Retard',
            'Jours Retard Max',
            'Prochaine Échéance',
            'Montant Prochaine Échéance',
            'Dernier Paiement',
            'Nombre d\'échéances',
            'Nombre de paiements'
        ];
    }

    /**
     * @param mixed $etudiant
     * @return array
     */
    public function map($etudiant): array
    {
        return [
            $etudiant['matricule'],
            $etudiant['nom'],
            $etudiant['prenom'],
            $etudiant['email'] ?? '',
            $etudiant['telephone'] ?? '',
            $etudiant['filiere'],
            $etudiant['niveau'],
            $etudiant['statut_libelle'],
            $etudiant['montant_total_a_payer'],
            $etudiant['montant_paye'],
            $etudiant['montant_restant'],
            $etudiant['taux_progression'] . '%',
            $etudiant['en_retard'] ? 'Oui' : 'Non',
            $etudiant['jours_retard_max'] ?? 0,
            $etudiant['prochaine_echeance_formatted'] ?? '',
            $etudiant['montant_prochaine_echeance'] ?? '',
            $etudiant['dernier_paiement_formatted'] ?? '',
            count($etudiant['echeances'] ?? []),
            count($etudiant['paiements'] ?? [])
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        // Styles pour l'en-tête
        $sheet->getStyle('A1:S1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F46E5'], // Indigo
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'E5E7EB'],
                ],
            ],
        ]);

        // Récupérer le nombre total de lignes
        $lastRow = $sheet->getHighestRow();

        // Appliquer les couleurs selon le statut
        for ($row = 2; $row <= $lastRow; $row++) {
            $statut = $sheet->getCell('H' . $row)->getValue(); // Colonne H = Statut
            
            $color = $this->getColorForStatut($statut);
            
            $sheet->getStyle('A' . $row . ':S' . $row)->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $color],
                ],
                'font' => [
                    'color' => ['rgb' => $this->getTextColorForStatut($statut)],
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'E5E7EB'],
                    ],
                ],
            ]);
        }

        // Alignement pour toutes les cellules
        $sheet->getStyle('A1:S' . $lastRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A1:S' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        
        // Alignement spécial pour les montants (droite)
        $sheet->getStyle('I1:K' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('P1:P' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        return [];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 15, // Matricule
            'B' => 20, // Nom
            'C' => 20, // Prénom
            'D' => 30, // Email
            'E' => 15, // Téléphone
            'F' => 25, // Filière
            'G' => 15, // Niveau
            'H' => 12, // Statut
            'I' => 15, // Montant Total
            'J' => 15, // Montant Payé
            'K' => 15, // Montant Restant
            'L' => 12, // Taux Progression
            'M' => 10, // En Retard
            'N' => 12, // Jours Retard Max
            'O' => 15, // Prochaine Échéance
            'P' => 15, // Montant Prochaine Échéance
            'Q' => 18, // Dernier Paiement
            'R' => 12, // Nombre d'échéances
            'S' => 12, // Nombre de paiements
        ];
    }

    /**
     * Détermine la couleur de fond selon le statut
     */
    private function getColorForStatut($statut)
    {
        switch ($statut) {
            case 'Solde':
                return 'D1FAE5'; // Vert clair
            case 'En cours':
                return 'FEF3C7'; // Jaune clair
            case 'En retard':
                return 'FEE2E2'; // Rouge clair
            default:
                return 'F3F4F6'; // Gris clair
        }
    }

    /**
     * Détermine la couleur du texte selon le statut
     */
    private function getTextColorForStatut($statut)
    {
        switch ($statut) {
            case 'Solde':
                return '065F46'; // Vert foncé
            case 'En cours':
                return '92400E'; // Jaune foncé
            case 'En retard':
                return '991B1B'; // Rouge foncé
            default:
                return '1F2937'; // Gris foncé
        }
    }
}