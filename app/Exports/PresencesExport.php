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

class PresencesExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, WithEvents, ShouldAutoSize
{
    protected $presences;
    protected $emploi;
    protected $seance; // NOUVEAU : La séance concernée

    // MODIFICATION : Ajout de $seance dans le constructeur
    public function __construct($presences, $emploi, $seance = null)
    {
        $this->presences = $presences;
        $this->emploi = $emploi;
        $this->seance = $seance;
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
            'Heure de départ', // NOUVEAU
            'Minutes de retard', // NOUVEAU
            'Statut',
            'Participation', // NOUVEAU
            'Attitude', // NOUVEAU
            'Points d\'attention', // NOUVEAU
            'Commentaire',
            'Signalement', // NOUVEAU
            'À remonter conseil', // NOUVEAU
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
            $presence->heure_depart ?? '—', // NOUVEAU
            $presence->minutes_retard ?? '—', // NOUVEAU
            $this->getStatutLabel($presence->statut),
            $this->getParticipationLabel($presence->participation), // NOUVEAU
            $this->getAttitudeLabel($presence->attitude), // NOUVEAU
            $this->formatPointsAttention($presence->points_attention), // NOUVEAU
            $presence->commentaire ?? '—',
            $presence->a_signalement ? 'OUI' : 'NON', // NOUVEAU
            $presence->a_remonter_conseil ? 'OUI' : 'NON', // NOUVEAU
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
        // MODIFICATION : Inclure la date de la séance dans le titre
        if ($this->seance) {
            $dateSeance = date('d-m-Y', strtotime($this->seance->date_seance));
            return 'Présences_' . $dateSeance;
        }
        return 'Présences';
    }

    /**
    * @param Worksheet $sheet
    * @return array
    */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style pour les en-têtes (ligne 5 maintenant)
            5 => [
                'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E3A8A']
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
                
                // Déterminer le nombre de colonnes (adapté à vos nouveaux champs)
                $lastColumn = 'T'; // 20 colonnes (A à T)
                $rowCount = $this->presences->count() + 5; // +5 pour les lignes d'en-tête et info
                
                // 1. TITRE PRINCIPAL
                $sheet->mergeCells('A1:' . $lastColumn . '1');
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
                $sheet->mergeCells('A2:' . $lastColumn . '2');
                $coursNom = strtoupper($this->emploi->uv->nom ?? 'N/A');
                $sheet->setCellValue('A2', 'COURS : ' . $coursNom);
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

                // 3. DÉTAILS DU COURS AVEC LA SÉANCE
                $sheet->mergeCells('A3:C3');
                $sheet->setCellValue('A3', 'GROUPE : ' . ($this->emploi->group->nom ?? 'N/A'));
                
                $sheet->mergeCells('D3:F3');
                $sheet->setCellValue('D3', 'SALLE : ' . ($this->emploi->salle->nom ?? 'N/A'));
                
                $sheet->mergeCells('G3:I3');
                $dateDebut = $this->emploi->debut ? date('d/m/Y', strtotime($this->emploi->debut)) : 'N/A';
                $heureDebut = $this->emploi->debut ? date('H:i', strtotime($this->emploi->debut)) : 'N/A';
                $heureFin = $this->emploi->fin ? date('H:i', strtotime($this->emploi->fin)) : 'N/A';
                $sheet->setCellValue('G3', 'HORAIRE : ' . $dateDebut . ' ' . $heureDebut . ' - ' . $heureFin);
                
                // NOUVEAU : Informations de la séance
                if ($this->seance) {
                    $sheet->mergeCells('J3:L3');
                    $dateSeance = date('d/m/Y', strtotime($this->seance->date_seance));
                    $sheet->setCellValue('J3', 'SÉANCE DU : ' . $dateSeance);
                    
                    $sheet->mergeCells('M3:O3');
                    $sheet->setCellValue('M3', 'STATUT SÉANCE : ' . $this->getSeanceStatutLabel($this->seance->statut));
                    
                    $sheet->mergeCells('P3:R3');
                    $sheet->setCellValue('P3', 'NB PRÉSENCES : ' . $this->presences->count());
                } else {
                    $sheet->mergeCells('J3:' . $lastColumn . '3');
                    $sheet->setCellValue('J3', 'NB PRÉSENCES : ' . $this->presences->count());
                }
                
                // Style pour la ligne 3
                $sheet->getStyle('A3:' . $lastColumn . '3')->applyFromArray([
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

                // 4. STATISTIQUES RAPIDES
                $presents = $this->presences->where('statut', 'present')->count();
                $absents = $this->presences->whereIn('statut', ['absent', 'absent_justifie'])->count();
                $retards = $this->presences->whereIn('statut', ['retard', 'retard_justifie'])->count();
                $justifies = $this->presences->whereIn('statut', ['absent_justifie', 'retard_justifie'])->count();
                $signalements = $this->presences->where('a_signalement', true)->count();
                $total = $this->presences->count();
                
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
                $sheet->mergeCells('M4:O4');
                $sheet->setCellValue('M4', 'SIGNALEMENTS : ' . $signalements);
                
                $sheet->getStyle('A4:O4')->applyFromArray([
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
                for ($i = 6; $i <= $rowCount; $i++) {
                    $statut = $sheet->getCell('J' . $i)->getValue(); // Colonne J = Statut
                    
                    switch ($statut) {
                        case 'Présent':
                            $color = 'E8F5E9';
                            $fontColor = '2E7D32';
                            break;
                        case 'Absent':
                            $color = 'FFEBEE';
                            $fontColor = 'C62828';
                            break;
                        case 'En retard':
                            $color = 'FFF8E1';
                            $fontColor = 'F57F17';
                            break;
                        case 'Justifié':
                        case 'Absent justifié':
                        case 'Retard justifié':
                            $color = 'E3F2FD';
                            $fontColor = '0D47A1';
                            break;
                        default:
                            $color = 'FFFFFF';
                            $fontColor = '000000';
                    }
                    
                    $sheet->getStyle('A' . $i . ':' . $lastColumn . $i)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => $color]
                        ],
                        'font' => [
                            'color' => ['rgb' => $fontColor]
                        ]
                    ]);
                }

                // 6. BORDURES POUR TOUT LE TABLEAU
                $sheet->getStyle('A6:' . $lastColumn . $rowCount)->applyFromArray([
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

                // 7. LARGEURS SPÉCIFIQUES
                $sheet->getColumnDimension('A')->setWidth(8);   // N°
                $sheet->getColumnDimension('B')->setWidth(15);  // Matricule
                $sheet->getColumnDimension('C')->setWidth(20);  // Nom
                $sheet->getColumnDimension('D')->setWidth(20);  // Prénom
                $sheet->getColumnDimension('E')->setWidth(30);  // Email
                $sheet->getColumnDimension('F')->setWidth(15);  // Date
                $sheet->getColumnDimension('G')->setWidth(12);  // Heure arrivée
                $sheet->getColumnDimension('H')->setWidth(12);  // Heure départ
                $sheet->getColumnDimension('I')->setWidth(15);  // Minutes retard
                $sheet->getColumnDimension('J')->setWidth(15);  // Statut
                $sheet->getColumnDimension('K')->setWidth(15);  // Participation
                $sheet->getColumnDimension('L')->setWidth(15);  // Attitude
                $sheet->getColumnDimension('M')->setWidth(20);  // Points attention
                $sheet->getColumnDimension('N')->setWidth(25);  // Commentaire
                $sheet->getColumnDimension('O')->setWidth(12);  // Signalement
                $sheet->getColumnDimension('P')->setWidth(18);  // À remonter conseil
                $sheet->getColumnDimension('Q')->setWidth(15);  // Sanction
                $sheet->getColumnDimension('R')->setWidth(12);  // Validation
                $sheet->getColumnDimension('S')->setWidth(15);  // Validé par
                $sheet->getColumnDimension('T')->setWidth(20);  // Date validation

                // 8. PIED DE PAGE
                $footerRow = $rowCount + 2;
                $sheet->mergeCells('A' . $footerRow . ':' . $lastColumn . $footerRow);
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

                // 9. LIGNE DE TOTAL
                $totalRow = $rowCount + 1;
                $sheet->mergeCells('A' . $totalRow . ':H' . $totalRow);
                $sheet->setCellValue('A' . $totalRow, 'TOTAL GÉNÉRAL');
                $sheet->mergeCells('I' . $totalRow . ':' . $lastColumn . $totalRow);
                $sheet->setCellValue('I' . $totalRow, $this->presences->count() . ' étudiants');
                $sheet->getStyle('A' . $totalRow . ':' . $lastColumn . $totalRow)->applyFromArray([
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
            'absent_justifie' => 'Absent justifié',
            'retard' => 'Retard',
            'retard_justifie' => 'Retard justifié',
            'dispense' => 'Dispensé',
            'exclu_temporairement' => 'Exclu temporairement',
            'malade' => 'Malade',
            'sortie_anticipee' => 'Sortie anticipée',
        ];
        return $labels[$statut] ?? $statut;
    }

    private function getParticipationLabel($participation)
    {
        $labels = [
            'excellente' => 'Excellente',
            'bonne' => 'Bonne',
            'moyenne' => 'Moyenne',
            'faible' => 'Faible',
            'nulle' => 'Nulle',
            'non_concerné' => 'Non concerné',
        ];
        return $labels[$participation] ?? '—';
    }

    private function getAttitudeLabel($attitude)
    {
        $labels = [
            'exemplaire' => 'Exemplaire',
            'correcte' => 'Correcte',
            'a_surveiller' => 'À surveiller',
            'problematique' => 'Problématique',
            'perturbateur' => 'Perturbateur',
        ];
        return $labels[$attitude] ?? '—';
    }

    private function formatPointsAttention($points)
    {
        if (!$points || empty($points)) {
            return '—';
        }
        
        $pointsArray = is_array($points) ? $points : json_decode($points, true);
        if (empty($pointsArray)) {
            return '—';
        }
        
        $labels = [
            'telephone' => ' Téléphone',
            'bavardage' => 'Bavardage',
            'tenue' => 'Tenue',
        ];
        
        $formatted = [];
        foreach ($pointsArray as $point) {
            $formatted[] = $labels[$point] ?? $point;
        }
        
        return implode(', ', $formatted);
    }

    private function getSeanceStatutLabel($statut)
    {
        $labels = [
            'planifie' => 'Planifiée',
            'en_cours' => 'En cours',
            'termine' => 'Terminée',
            'annule' => 'Annulée',
            'reporte' => 'Reportée',
            'rattrapage' => 'Rattrapage',
        ];
        return $labels[$statut] ?? $statut;
    }
}