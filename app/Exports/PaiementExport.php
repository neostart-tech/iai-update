<?php
// app/Exports/PaiementExport.php

namespace App\Exports;

use App\Models\Etudiant;
use App\Models\FraisEtudiant;
use App\Models\FraisScolarite;
use App\Models\Niveau;
use App\Models\Filiere;
use App\Traits\PaiementCalculTrait;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;

class PaiementExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents
{
    use PaiementCalculTrait;

    protected $type;
    protected $id;
    protected $periodeDebut;
    protected $periodeFin;
    protected $etudiants;
    protected $totaux;

    public function __construct($type, $id = null, $periodeDebut = null, $periodeFin = null)
    {
        $this->type = $type;
        $this->id = $id;
        $this->periodeDebut = $periodeDebut ? Carbon::parse($periodeDebut) : null;
        $this->periodeFin = $periodeFin ? Carbon::parse($periodeFin) : null;
        $this->totaux = [
            'montant_initial' => 0,
            'montant_apres_bourse' => 0,
            'total_paye' => 0,
            'reste_a_payer' => 0
        ];
    }

    /**
     * Récupère la collection d'étudiants selon les critères
     */
    public function collection()
    {
        $query = Etudiant::with([
            'etudiantGroups.niveau',
            'etudiantGroups.filiere',
            'etudiantGroups.group'
        ]);

        // Filtre par type
        switch ($this->type) {
            case 'etudiant':
                $query->where('id', $this->id);
                break;
            case 'niveau':
                $query->whereHas('etudiantGroups', function($q) {
                    $q->where('niveau_id', $this->id);
                });
                break;
            case 'filiere':
                $query->whereHas('etudiantGroups', function($q) {
                    $q->where('filiere_id', $this->id);
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

        $this->etudiants = $query->get();
        
        return $this->etudiants;
    }

    /**
     * En-têtes du fichier Excel
     */
    public function headings(): array
    {
        $titre = $this->getTitre();
        
        return [
            [$titre],
            ['Généré le ' . Carbon::now()->format('d/m/Y H:i')],
            [],
            [
                'Matricule',
                'Nom complet',
                'Niveau',
                'Filière',
                'Montant initial',
                'Montant après bourse',
                'Total payé',
                'Reste à payer',
                'Statut',
                'Dernier paiement',
                'Date limite prochaine',
                'Commentaire (Dernier)'
            ]
        ];
    }

    /**
     * Mapping des données pour chaque ligne
     */
    public function map($etudiant): array
    {
        $infos = $this->getInfosPaiementEtudiant($etudiant);
        
        // Accumuler les totaux
        $this->totaux['montant_initial'] += $infos['montant_initial'];
        $this->totaux['montant_apres_bourse'] += $infos['montant_apres_bourse'];
        $this->totaux['total_paye'] += $infos['total_paye'];
        $this->totaux['reste_a_payer'] += $infos['reste_a_payer'];

        // Formater les montants en texte (évite l'abréviation "K" dans Excel)
        $fmt = fn($v) => number_format((float)$v, 0, ',', ' ') . ' F CFA';

        return [
            $etudiant->matricule,
            $etudiant->nom . ' ' . $etudiant->prenom,
            $infos['niveau'],
            $infos['filiere'],
            $fmt($infos['montant_initial']),
            $fmt($infos['montant_apres_bourse']),
            $fmt($infos['total_paye']),
            $fmt($infos['reste_a_payer']),
            $infos['statut'],
            $infos['dernier_paiement'] ? Carbon::parse($infos['dernier_paiement'])->format('d/m/Y') : '-',
            $infos['prochaine_date_limite'] ? Carbon::parse($infos['prochaine_date_limite'])->format('d/m/Y') : '-',
            $infos['dernier_commentaire'] ?: '-',
        ];
    }

    /**
     * Styles du fichier Excel
     */
    public function styles(Worksheet $sheet)
    {
        // Style pour le titre
        $sheet->mergeCells('A1:L1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

        // Style pour la date
        $sheet->mergeCells('A2:L2');
        $sheet->getStyle('A2')->getFont()->setItalic(true);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

        // Style pour les en-têtes
        $sheet->getStyle('A4:L4')->getFont()->setBold(true);
        $sheet->getStyle('A4:L4')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF4F81BD');
        $sheet->getStyle('A4:L4')->getFont()->getColor()->setARGB(Color::COLOR_WHITE);

        return [];
    }

    /**
     * Largeurs des colonnes
     */
    public function columnWidths(): array
    {
        return [
            'A' => 15, // Matricule
            'B' => 45, // Nom complet
            'C' => 15, // Niveau
            'D' => 35, // Filière
            'E' => 22, // Montant initial
            'F' => 24, // Montant après bourse
            'G' => 20, // Total payé
            'H' => 20, // Reste à payer
            'I' => 14, // Statut
            'J' => 15, // Dernier paiement
            'K' => 22, // Date limite prochaine
            'L' => 30, // Commentaire
        ];
    }

    /**
     * Événements pour ajouter les totaux et la coloration
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $this->etudiants->count() + 4;

                // Ajouter la ligne des totaux
                $totalRow = $lastRow + 2;
                $sheet->setCellValue('A' . $totalRow, 'TOTAUX GÉNÉRAUX');
                $sheet->mergeCells('A' . $totalRow . ':D' . $totalRow);
                $sheet->getStyle('A' . $totalRow)->getFont()->setBold(true);

                $fmt = fn($v) => number_format((float)$v, 0, ',', ' ') . ' F CFA';

                $sheet->setCellValue('E' . $totalRow, $fmt($this->totaux['montant_initial']));
                $sheet->setCellValue('F' . $totalRow, $fmt($this->totaux['montant_apres_bourse']));
                $sheet->setCellValue('G' . $totalRow, $fmt($this->totaux['total_paye']));
                $sheet->setCellValue('H' . $totalRow, $fmt($this->totaux['reste_a_payer']));

                // Style pour la ligne des totaux
                $sheet->getStyle('A' . $totalRow . ':L' . $totalRow)->getFont()->setBold(true);
                $sheet->getStyle('A' . $totalRow . ':L' . $totalRow)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFD9E1F2');

                // Colorer les cellules selon le statut
                for ($row = 5; $row <= $lastRow; $row++) {
                    $statut = $sheet->getCell('I' . $row)->getValue();
                    
                    switch ($statut) {
                        case 'solde':
                            $color = 'FFC6EFCE';
                            break;
                        case 'en_retard':
                            $color = 'FFFFC7CE';
                            break;
                        case 'en_cours':
                            $color = 'FFFFEB9C';
                            break;
                        default:
                            $color = 'FFE0E0E0';
                    }

                    $sheet->getStyle('A' . $row . ':L' . $row)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB($color);
                }

                // Ajouter des bordures
                $sheet->getStyle('A4:L' . ($totalRow))
                    ->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);
            },
        ];
    }

    /**
     * Génère le titre du rapport
     */
    private function getTitre()
    {
        switch ($this->type) {
            case 'etudiant':
                $etudiant = Etudiant::find($this->id);
                return "RAPPORT DE PAIEMENT - ÉTUDIANT : " . ($etudiant ? $etudiant->nom . ' ' . $etudiant->prenom : '');
            case 'niveau':
                $niveau = Niveau::find($this->id);
                return "RAPPORT DE PAIEMENT - NIVEAU : " . ($niveau ? $niveau->libelle : '');
            case 'filiere':
                $filiere = Filiere::find($this->id);
                return "RAPPORT DE PAIEMENT - FILIÈRE : " . ($filiere ? $filiere->nom : '');
            case 'global':
                return "RAPPORT DE PAIEMENT - TOUS LES ÉTUDIANTS";
            default:
                return "RAPPORT DE PAIEMENT";
        }
    }
}