<?php
// app/Exports/EnseignantPresencesExport.php

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
use Carbon\Carbon;

class EnseignantPresencesExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, WithEvents, ShouldAutoSize
{
    protected $presences;
    protected $enseignant;
    protected $emploi;
    protected $filters;

    /**
     * @param Collection $presences Les présences à exporter
     * @param User $enseignant L'enseignant concerné
     * @param EmploiDuTemp $emploi Le cours concerné
     * @param array $filters Filtres appliqués (optionnel)
     */
    public function __construct($presences, $enseignant, $emploi, $filters = [])
    {
        $this->presences = $presences;
        $this->enseignant = $enseignant;
        $this->emploi = $emploi;
        $this->filters = $filters;
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
            'Date du cours',
            'Heure début prévue',
            'Heure fin prévue',
            'Heure arrivée',
            'Heure départ',
            'Heure départ réelle',
            'Statut',
            'Durée réelle',
            'Durée comptée',
            'Type de pointage',
            'Volume horaire avant',
            'Volume horaire après',
            'Commentaire',
            'Date enregistrement',
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

        // Récupérer les métadonnées
        $metaData = $presence->meta_data ?? [];
        $volumeHoraire = $metaData['volume_horaire'] ?? [];
        
        // Calculer le volume avant/après
        $volumeAvant = isset($volumeHoraire['effectue_avant']) 
            ? round($volumeHoraire['effectue_avant'], 2) . 'h' 
            : '—';
        
        $volumeApres = isset($volumeHoraire['restant_apres']) 
            ? round($volumeHoraire['restant_apres'], 2) . 'h' 
            : '—';
        
        return [
            $index,
            $presence->date_cours ? Carbon::parse($presence->date_cours)->format('d/m/Y') : '—',
            $presence->heure_debut_prevue ?? '—',
            $presence->heure_fin_prevue ?? '—',
            $presence->heure_arrivee ?? '—',
            $presence->heure_depart ?? '—',
            $presence->heure_depart_reelle ?? '—',
            $this->getStatutLabel($presence->statut),
            $presence->duree_reelle_heures ? $presence->duree_reelle_heures . 'h' : '—',
            $presence->duree_calculee_heures ? $presence->duree_calculee_heures . 'h' : '—',
            $this->getTypePointageLabel($presence->type_pointage),
            $volumeAvant,
            $volumeApres,
            $presence->commentaire ?? '—',
            $presence->created_at ? $presence->created_at->format('d/m/Y H:i') : '—',
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Présences Enseignant';
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style pour les en-têtes (ligne 4 après insertion des titres)
            4 => [
                'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '7B1FA2'] // Violet pour distinguer des étudiants
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
                $sheet->mergeCells('A1:O1');
                $sheet->setCellValue('A1', 'PRÉSENCES ENSEIGNANT');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 18,
                        'color' => ['rgb' => '7B1FA2']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(30);

                // 2. INFORMATIONS DE L'ENSEIGNANT
                $sheet->mergeCells('A2:O2');
                $sheet->setCellValue('A2', 'ENSEIGNANT : ' . strtoupper(($this->enseignant->prenom ?? '') . ' ' . ($this->enseignant->nom ?? '')));
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                        'color' => ['rgb' => 'FFFFFF']
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '9C27B0']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(25);

                // 3. INFORMATIONS DU COURS
                $sheet->mergeCells('A3:F3'); // UV et groupe
                $uvNom = $this->emploi->uv->nom ?? 'N/A';
                $groupeNom = $this->emploi->group->nom ?? 'N/A';
                $sheet->setCellValue('A3', 'COURS : ' . $uvNom . ' - GROUPE : ' . $groupeNom);

                $sheet->mergeCells('G3:K3'); // Période
                $dateDebut = $this->emploi->debut ? date('d/m/Y', strtotime($this->emploi->debut)) : 'N/A';
                $heureDebut = $this->emploi->debut ? date('H:i', strtotime($this->emploi->debut)) : 'N/A';
                $heureFin = $this->emploi->fin ? date('H:i', strtotime($this->emploi->fin)) : 'N/A';
                
                // Ajouter info récurrence si applicable
                $recurrenceInfo = '';
                if ($this->emploi->recurrence_type === 'hebdomadaire') {
                    $jours = $this->formatJoursRecurrence($this->emploi->recurrence_days);
                    $recurrenceInfo = ' | Récurrent: ' . $jours;
                }
                
                $sheet->setCellValue('G3', 'HORAIRE : ' . $dateDebut . ' ' . $heureDebut . ' - ' . $heureFin . $recurrenceInfo);

                $sheet->mergeCells('L3:O3'); // Volume horaire
                $volumeTotal = $this->presenceService->getVolumeHoraireTotal($this->emploi->uv) ?? 0;
                $sheet->setCellValue('L3', 'VOLUME UV : ' . $volumeTotal . 'h');

                // Style pour la ligne 3
                $sheet->getStyle('A3:O3')->applyFromArray([
                    'font' => [
                        'size' => 11,
                        'color' => ['rgb' => '333333']
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F3E5F5'] // Violet très clair
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
                $sheet->insertNewRowBefore(4, 1);
                
                $totalPresences = $this->presences->count();
                $totalHeures = round($this->presences->sum('duree_calculee_minutes') / 60, 2);
                $presents = $this->presences->where('statut', 'present')->count();
                $retards = $this->presences->where('statut', 'retard')->count();
                
                $sheet->mergeCells('A4:C4');
                $sheet->setCellValue('A4', 'TOTAL SÉANCES : ' . $totalPresences);
                $sheet->mergeCells('D4:F4');
                $sheet->setCellValue('D4', 'TOTAL HEURES : ' . $totalHeures . 'h');
                $sheet->mergeCells('G4:I4');
                $sheet->setCellValue('G4', 'PRÉSENTS : ' . $presents);
                $sheet->mergeCells('J4:L4');
                $sheet->setCellValue('J4', 'RETARDS : ' . $retards);
                $sheet->mergeCells('M4:O4');
                $tronques = $this->presences->where('type_pointage', 'depart_tronque')->count();
                $sheet->setCellValue('M4', 'TRONQUÉS : ' . $tronques);
                
                $sheet->getStyle('A4:O4')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F3E5F5']
                    ],
                    'borders' => [
                        'bottom' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color' => ['rgb' => '7B1FA2']
                        ]
                    ],
                ]);

                // 5. COLORATION CONDITIONNELLE SELON LE STATUT
                for ($i = 5; $i <= $rowCount; $i++) {
                    $statut = $sheet->getCell('H' . $i)->getValue(); // Colonne H = Statut
                    
                    switch ($statut) {
                        case 'Présent':
                            $color = 'E8F5E9';
                            $fontColor = '2E7D32';
                            break;
                        case 'En retard':
                            $color = 'FFF8E1';
                            $fontColor = 'F57F17';
                            break;
                        case 'Absent':
                            $color = 'FFEBEE';
                            $fontColor = 'C62828';
                            break;
                        case 'Justifié':
                            $color = 'E3F2FD';
                            $fontColor = '0D47A1';
                            break;
                        default:
                            $color = 'FFFFFF';
                            $fontColor = '000000';
                    }
                    
                    $sheet->getStyle('A' . $i . ':O' . $i)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => $color]
                        ],
                        'font' => [
                            'color' => ['rgb' => $fontColor]
                        ]
                    ]);
                }

                // 6. SURBRILLANCE DES LIGNES TRONQUÉES
                for ($i = 5; $i <= $rowCount; $i++) {
                    $typePointage = $sheet->getCell('K' . $i)->getValue(); // Colonne K = Type pointage
                    
                    if (strpos($typePointage, 'Tronqué') !== false) {
                        $sheet->getStyle('A' . $i . ':O' . $i)->applyFromArray([
                            'font' => ['bold' => true],
                            'borders' => [
                                'left' => [
                                    'borderStyle' => Border::BORDER_MEDIUM,
                                    'color' => ['rgb' => 'F57C00']
                                ]
                            ]
                        ]);
                    }
                }

                // 7. BORDURES POUR TOUT LE TABLEAU
                $sheet->getStyle('A5:O' . $rowCount)->applyFromArray([
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

                // 8. LARGEURS SPÉCIFIQUES
                $sheet->getColumnDimension('A')->setWidth(8);   // N°
                $sheet->getColumnDimension('B')->setWidth(15);  // Date
                $sheet->getColumnDimension('C')->setWidth(12);  // Début prévu
                $sheet->getColumnDimension('D')->setWidth(12);  // Fin prévue
                $sheet->getColumnDimension('E')->setWidth(12);  // Arrivée
                $sheet->getColumnDimension('F')->setWidth(12);  // Départ
                $sheet->getColumnDimension('G')->setWidth(12);  // Départ réel
                $sheet->getColumnDimension('H')->setWidth(15);  // Statut
                $sheet->getColumnDimension('I')->setWidth(12);  // Durée réelle
                $sheet->getColumnDimension('J')->setWidth(12);  // Durée comptée
                $sheet->getColumnDimension('K')->setWidth(18);  // Type pointage
                $sheet->getColumnDimension('L')->setWidth(15);  // Volume avant
                $sheet->getColumnDimension('M')->setWidth(15);  // Volume après
                $sheet->getColumnDimension('N')->setWidth(25);  // Commentaire
                $sheet->getColumnDimension('O')->setWidth(18);  // Date enregistrement

                // 9. PIED DE PAGE
                $footerRow = $rowCount + 2;
                $sheet->mergeCells('A' . $footerRow . ':O' . $footerRow);
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

                // 10. RÉCAPITULATIF VOLUME HORAIRE
                $totalRow = $rowCount + 1;
                $totalHeuresReelles = round($this->presences->sum('duree_reelle_minutes') / 60, 2);
                $totalHeuresCalculees = round($this->presences->sum('duree_calculee_minutes') / 60, 2);
                $volumeTotalUV = $this->presenceService->getVolumeHoraireTotal($this->emploi->uv) ?? 0;
                
                $sheet->mergeCells('A' . $totalRow . ':H' . $totalRow);
                $sheet->setCellValue('A' . $totalRow, 'RÉCAPITULATIF VOLUME HORAIRE');
                $sheet->mergeCells('I' . $totalRow . ':O' . $totalRow);
                $sheet->setCellValue('I' . $totalRow, 'Total réel: ' . $totalHeuresReelles . 'h | Total compté: ' . $totalHeuresCalculees . 'h | Volume UV: ' . $volumeTotalUV . 'h');
                
                $sheet->getStyle('A' . $totalRow . ':O' . $totalRow)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'EDE7F6']
                    ],
                    'borders' => [
                        'top' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color' => ['rgb' => '7B1FA2']
                        ]
                    ],
                ]);
            },
        ];
    }

    /**
     * Formatter le statut
     */
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

    /**
     * Formatter le type de pointage
     */
    private function getTypePointageLabel($type)
    {
        $labels = [
            'arrivee' => 'Arrivée',
            'depart_complete' => 'Départ complet',
            'depart_tronque' => 'Départ tronqué',
        ];
        return $labels[$type] ?? $type;
    }

    /**
     * Formatter les jours de récurrence
     */
    private function formatJoursRecurrence($daysString)
    {
        if (!$daysString) return '';
        
        $joursMap = [
            'MO' => 'Lundi',
            'TU' => 'Mardi',
            'WE' => 'Mercredi',
            'TH' => 'Jeudi',
            'FR' => 'Vendredi',
            'SA' => 'Samedi',
            'SU' => 'Dimanche'
        ];
        
        $jours = explode(',', $daysString);
        return implode(', ', array_map(function($j) use ($joursMap) {
            return $joursMap[$j] ?? $j;
        }, $jours));
    }
}