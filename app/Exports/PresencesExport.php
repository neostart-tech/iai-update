<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;

class PresencesExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, WithEvents, ShouldAutoSize
{
    protected $presences;
    protected $emploi;

    public function __construct($presences, $emploi)
    {
        $this->presences = $presences;
        $this->emploi = $emploi;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return $this->presences;
    }

    /**
    * @return array
    */
    public function headings(): array
    {
        return [
            'N°',
            'Matricule',
            'Nom',
            'Prénom',
            'Email',
            'Date du cours',
            'Heure d\'arrivée',
            'Statut',
            'Commentaire',
            'Sanction',
            'Validation',
            'Validé par',
            'Date validation',
        ];
    }

    /**
    * @param mixed $presence
    * @return array
    */
    public function map($presence): array
    {
        static $index = 0;
        $index++;

        $etudiant = $presence->etudiant;
        
        return [
            $index,
            $etudiant->matricule ?? 'N/A',
            $etudiant->nom ?? 'N/A',
            $etudiant->prenom ?? 'N/A',
            $etudiant->email ?? 'N/A',
            $presence->date ? date('d/m/Y', strtotime($presence->date)) : 'N/A',
            $presence->heure_arrivee ?? '—',
            $this->getStatutLabel($presence->statut),
            $presence->commentaire ?? '—',
            $presence->sanction ?? '—',
            $presence->needs_validation ? 'À valider' : 'Validé',
            $presence->validated_by ?? '—',
            $presence->validated_at ? date('d/m/Y H:i', strtotime($presence->validated_at)) : '—',
        ];
    }

    /**
    * @return string
    */
    public function title(): string
    {
        return 'Présences';
    }

    /**
    * @param Worksheet $sheet
    * @return array
    */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style pour les en-têtes
            4 => [
                'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E3A8A'] // Bleu foncé
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'FFFFFF']
                    ]
                ]
            ],
        ];
    }

    /**
    * @return array
    */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;
                $rowCount = $this->presences->count() + 4; // +4 pour les lignes d'en-tête et info
                
                // 1. TITRE PRINCIPAL
                $sheet->mergeCells('A1:M1');
                $sheet->setCellValue('A1', 'LISTE DES PRÉSENCES');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 18,
                        'color' => ['rgb' => '1E3A8A']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(30);

                // 2. INFORMATIONS DU COURS
                $sheet->mergeCells('A2:M2');
                $sheet->setCellValue('A2', 'COURS : ' . strtoupper($this->emploi->uv->nom ?? 'N/A'));
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                        'color' => ['rgb' => 'FFFFFF']
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '2563EB']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(25);

                // 3. DÉTAILS DU COURS
$sheet->mergeCells('A3:F3'); // 6 colonnes pour le groupe
$sheet->setCellValue('A3', 'GROUPE : ' . ($this->emploi->group->nom ?? 'N/A'));

$sheet->mergeCells('G3:I3'); // 3 colonnes pour la salle
$sheet->setCellValue('G3', 'SALLE : ' . ($this->emploi->salle->nom ?? 'N/A'));

$sheet->mergeCells('J3:L3'); // 3 colonnes pour l'horaire
$dateDebut = $this->emploi->debut ? date('d/m/Y', strtotime($this->emploi->debut)) : 'N/A';
$heureDebut = $this->emploi->debut ? date('H:i', strtotime($this->emploi->debut)) : 'N/A';
$heureFin = $this->emploi->fin ? date('H:i', strtotime($this->emploi->fin)) : 'N/A';
$sheet->setCellValue('J3', 'HORAIRE : ' . $dateDebut . ' ' . $heureDebut . ' - ' . $heureFin);

$sheet->mergeCells('M3:M3'); // 1 colonne pour l'effectif
$sheet->setCellValue('M3', 'EFFECTIF : ' . $this->presences->count());
                // Style pour la ligne 3
                $sheet->getStyle('A3:M3')->applyFromArray([
                    'font' => [
                        'size' => 11,
                        'color' => ['rgb' => '333333']
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F3F4F6']
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'E5E7EB']
                        ]
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ],
                ]);
                $sheet->getRowDimension(3)->setRowHeight(25);

                // 4. STATISTIQUES RAPIDES - CORRECTION DIVISION PAR ZÉRO
                $sheet->insertNewRowBefore(4, 1);
                
                $presents = $this->presences->where('statut', 'present')->count();
                $absents = $this->presences->where('statut', 'absent')->count();
                $retards = $this->presences->where('statut', 'retard')->count();
                $justifies = $this->presences->where('statut', 'justifie')->count();
                $total = $this->presences->count();
                
                // Éviter la division par zéro
                $pourcentagePresents = $total > 0 ? round(($presents/$total)*100) : 0;
                $pourcentageAbsents = $total > 0 ? round(($absents/$total)*100) : 0;
                $pourcentageRetards = $total > 0 ? round(($retards/$total)*100) : 0;
                $pourcentageJustifies = $total > 0 ? round(($justifies/$total)*100) : 0;
                
                $sheet->mergeCells('A4:C4');
                $sheet->setCellValue('A4', 'PRÉSENTS : ' . $presents . ' (' . $pourcentagePresents . '%)');
                $sheet->mergeCells('D4:F4');
                $sheet->setCellValue('D4', 'ABSENTS : ' . $absents . ' (' . $pourcentageAbsents . '%)');
                $sheet->mergeCells('G4:I4');
                $sheet->setCellValue('G4', 'RETARDS : ' . $retards . ' (' . $pourcentageRetards . '%)');
                $sheet->mergeCells('J4:L4');
                $sheet->setCellValue('J4', 'JUSTIFIÉS : ' . $justifies . ' (' . $pourcentageJustifies . '%)');
                
                $sheet->getStyle('A4:L4')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'EFF6FF']
                    ],
                    'borders' => [
                        'bottom' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color' => ['rgb' => '2563EB']
                        ]
                    ],
                ]);

                // 5. COLORATION CONDITIONNELLE DES LIGNES SELON LE STATUT
                for ($i = 5; $i <= $rowCount; $i++) {
                    $statut = $sheet->getCell('H' . $i)->getValue();
                    
                    switch ($statut) {
                        case 'Présent':
                            $color = 'E8F5E9'; // Vert très clair
                            $fontColor = '2E7D32';
                            break;
                        case 'Absent':
                            $color = 'FFEBEE'; // Rouge très clair
                            $fontColor = 'C62828';
                            break;
                        case 'En retard':
                            $color = 'FFF8E1'; // Jaune très clair
                            $fontColor = 'F57F17';
                            break;
                        case 'Justifié':
                            $color = 'E3F2FD'; // Bleu très clair
                            $fontColor = '0D47A1';
                            break;
                        default:
                            $color = 'FFFFFF';
                            $fontColor = '000000';
                    }
                    
                    $sheet->getStyle('A' . $i . ':M' . $i)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => $color]
                        ],
                        'font' => [
                            'color' => ['rgb' => $fontColor],
                            'bold' => true
                        ]
                    ]);
                }

                // 6. BORDURES POUR TOUT LE TABLEAU
                $sheet->getStyle('A5:M' . $rowCount)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'E5E7EB']
                        ]
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER
                    ],
                ]);

                // 7. ZÉBRURE ALTERNÉE POUR LES LIGNES NON COLORÉES
                for ($i = 5; $i <= $rowCount; $i += 2) {
                    $cellStyle = $sheet->getStyle('A' . $i)->getFill();
                    if ($cellStyle->getFillType() === Fill::FILL_NONE || 
                        $cellStyle->getStartColor()->getRGB() === 'FFFFFF') {
                        $sheet->getStyle('A' . $i . ':M' . $i)->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'FAFAFA']
                            ]
                        ]);
                    }
                }

                // 8. LARGEURS SPÉCIFIQUES POUR CERTAINES COLONNES
                $sheet->getColumnDimension('A')->setWidth(8);   // N°
                $sheet->getColumnDimension('B')->setWidth(15);  // Matricule
                $sheet->getColumnDimension('C')->setWidth(20);  // Nom
                $sheet->getColumnDimension('D')->setWidth(20);  // Prénom
                $sheet->getColumnDimension('E')->setWidth(30);  // Email
                $sheet->getColumnDimension('F')->setWidth(15);  // Date
                $sheet->getColumnDimension('G')->setWidth(12);  // Heure
                $sheet->getColumnDimension('H')->setWidth(15);  // Statut
                $sheet->getColumnDimension('I')->setWidth(25);  // Commentaire
                $sheet->getColumnDimension('J')->setWidth(20);  // Sanction
                $sheet->getColumnDimension('K')->setWidth(12);  // Validation
                $sheet->getColumnDimension('L')->setWidth(15);  // Validé par
                $sheet->getColumnDimension('M')->setWidth(20);  // Date validation

                // 9. PIED DE PAGE
                $footerRow = $rowCount + 2;
                $sheet->mergeCells('A' . $footerRow . ':M' . $footerRow);
                $sheet->setCellValue('A' . $footerRow, 'Document généré le ' . date('d/m/Y à H:i') . ' - ' . config('app.name'));
                $sheet->getStyle('A' . $footerRow)->applyFromArray([
                    'font' => [
                        'italic' => true,
                        'size' => 10,
                        'color' => ['rgb' => '666666']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_RIGHT
                    ],
                ]);

                // 10. LIGNE DE TOTAL
                $totalRow = $rowCount + 1;
                $sheet->mergeCells('A' . $totalRow . ':G' . $totalRow);
                $sheet->setCellValue('A' . $totalRow, 'TOTAL GÉNÉRAL');
                $sheet->mergeCells('H' . $totalRow . ':M' . $totalRow);
                $sheet->setCellValue('H' . $totalRow, $this->presences->count() . ' étudiants');
                $sheet->getStyle('A' . $totalRow . ':M' . $totalRow)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'E8EAF6']
                    ],
                    'borders' => [
                        'top' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color' => ['rgb' => '1E3A8A']
                        ]
                    ],
                ]);
            },
        ];
    }

    private function getStatutLabel($statut)
    {
        $labels = [
            'present' => 'Présent',
            'absent' => 'Absent',
            'retard' => 'En retard',
            'justifie' => 'Justifié',
        ];
        return $labels[$statut] ?? $statut;
    }
}