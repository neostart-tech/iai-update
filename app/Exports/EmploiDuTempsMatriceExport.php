<?php

namespace App\Exports;

use App\Models\EmploiDuTemp;
use App\Models\Group;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Hyperlink;
use Carbon\Carbon;

class EmploiDuTempsMatriceExport implements FromArray, WithTitle, WithStyles, ShouldAutoSize, WithEvents
{
    protected $group_id;
    protected $date_debut;
    protected $date_fin;
    protected $type_export;
    protected $groupe;
    protected $coursAvecLiens = []; // Pour stocker les cours avec leurs liens
    
    const COULEUR_COURS = 'E3F2FD';
    const COULEUR_EVAL = 'FFEBEE';
    const COULEUR_EVENEMENT = 'FFF3E0';
    const COULEUR_HEADER = '1E3A8A';
    const COULEUR_JOUR = 'F5F5F5';
    const COULEUR_LIEN = '2563EB'; // Bleu pour les liens

    public function __construct($group_id, $date_debut, $date_fin, $type_export = 'tous')
    {
        $this->group_id = $group_id;
        $this->date_debut = Carbon::parse($date_debut)->startOfDay();
        $this->date_fin = $date_fin ? Carbon::parse($date_fin)->endOfDay() : $this->date_debut->copy()->endOfDay();
        $this->type_export = $type_export;
        $this->groupe = Group::with('niveau')->find($group_id);
    }

    public function array(): array
    {
        $data = [];
        
        // En-têtes
        $headers = [
            'JOUR',
            'DATE',
            'HORAIRE',
            'MATIÈRE / ACTIVITÉ',
            'TYPE',
            'ENSEIGNANT',
            'SALLE / LIEN DE CONNEXION'
        ];
        $data[] = $headers;

        // Récupérer les cours GROUPÉS par date/heure/matière
        $coursOrganises = $this->getCoursGroupes();

        // Ajouter les données
        foreach ($coursOrganises as $jourData) {
            // En-tête de journée
            $data[] = [
                $jourData['jour_fr'],
                $jourData['date_formatted'],
                '',
                '',
                '',
                '',
                ''
            ];
            
            // Cours du jour (déjà groupés)
            foreach ($jourData['cours'] as $c) {
                $data[] = [
                    '', // Pas de répétition du jour
                    '',
                    $c['horaire'],
                    $c['matiere'],
                    $c['type'],
                    $c['professeur'] ?? 'À définir',
                    $c['salle_affichage'] // Texte affiché dans la cellule
                ];
                
                // Stocker le lien pour une utilisation ultérieure
                if ($c['est_virtuelle'] && $c['lien_salle']) {
                    $this->coursAvecLiens[] = [
                        'row' => count($data), // La ligne qui vient d'être ajoutée
                        'lien' => $c['lien_salle'],
                        'texte' => $c['salle_affichage']
                    ];
                }
            }
            
            // Ligne de séparation entre les jours
            if ($jourData !== end($coursOrganises)) {
                $data[] = ['', '', '', '', '', '', ''];
            }
        }

        return $data;
    }

    public function getCoursGroupes()
    {
        $coursGroupes = [];
        
        // Déterminer les types à inclure
        $types = ['Cours', 'Évaluation', 'Événement'];
        if ($this->type_export === 'cours') {
            $types = ['Cours'];
        } elseif ($this->type_export === 'evaluations') {
            $types = ['Évaluation'];
        }
        
        $emplois = EmploiDuTemp::with([
            'uv', 
            'salle', 
            'owner'
        ])
        ->where('group_id', $this->group_id)
        ->whereIn('type_programme', $types)
        ->get();

        $jours_fr = [
            'Monday' => 'LUNDI',
            'Tuesday' => 'MARDI',
            'Wednesday' => 'MERCREDI',
            'Thursday' => 'JEUDI',
            'Friday' => 'VENDREDI',
            'Saturday' => 'SAMEDI',
            'Sunday' => 'DIMANCHE'
        ];

        // Tableau pour stocker les cours UNIQUES
        $coursUniques = [];

        foreach ($emplois as $emploi) {
            if ($emploi->recurrence_type === 'aucune') {
                $date = Carbon::parse($emploi->debut);
                if ($date->between($this->date_debut, $this->date_fin)) {
                    $heure_debut = Carbon::parse($emploi->debut)->format('H:i');
                    $heure_fin = Carbon::parse($emploi->fin)->format('H:i');
                    
                    $matiere = $this->getMatiereLibelle($emploi);
                    $type = $emploi->type_programme->value ?? $emploi->type_programme;
                    
                    $cle_unique = $date->format('Y-m-d') . '|' . $heure_debut . '|' . $heure_fin . '|' . $matiere . '|' . $type;
                    
                    if (!isset($coursUniques[$cle_unique])) {
                        $professeur = $this->getProfesseurLibelle($emploi);
                        $salleInfo = $this->getSalleInfo($emploi->salle);
                        
                        $coursUniques[$cle_unique] = [
                            'date' => $date->format('Y-m-d'),
                            'date_obj' => $date,
                            'jour_fr' => $jours_fr[$date->format('l')] ?? strtoupper($date->format('l')),
                            'date_formatted' => $date->format('d/m/Y'),
                            'heure_debut' => $heure_debut,
                            'heure_fin' => $heure_fin,
                            'horaire' => $heure_debut . ' - ' . $heure_fin,
                            'matiere' => $matiere,
                            'type' => $type,
                            'professeur' => $professeur,
                            'salle_affichage' => $salleInfo['affichage'],
                            'lien_salle' => $salleInfo['lien'],
                            'est_virtuelle' => $salleInfo['est_virtuelle']
                        ];
                    }
                }
            } 
            elseif ($emploi->recurrence_type === 'hebdomadaire') {
                $this->ajouterCoursRecurrent($coursUniques, $emploi, $jours_fr);
            }
        }

        // Organiser les cours par jour
        $coursParJour = [];
        foreach ($coursUniques as $cours) {
            $dateKey = $cours['date'];
            
            if (!isset($coursParJour[$dateKey])) {
                $coursParJour[$dateKey] = [
                    'jour_fr' => $cours['jour_fr'],
                    'date_formatted' => $cours['date_formatted'],
                    'date' => $cours['date_obj'],
                    'cours' => []
                ];
            }
            
            $coursParJour[$dateKey]['cours'][] = $cours;
        }

        // Trier par date
        ksort($coursParJour);
        
        // Trier les cours par heure pour chaque jour
        foreach ($coursParJour as &$jourData) {
            usort($jourData['cours'], function($a, $b) {
                return strcmp($a['heure_debut'], $b['heure_debut']);
            });
        }

        return array_values($coursParJour);
    }
    
    /**
     * Récupère le libellé de la matière
     */
    private function getMatiereLibelle($emploi)
    {
        if ($emploi->uv) {
            return $emploi->uv->code . ' - ' . $emploi->uv->nom;
        }
        return $emploi->titre ?? 'Cours';
    }
    
    /**
     * Récupère le libellé du professeur
     */
    private function getProfesseurLibelle($emploi)
    {
        if ($emploi->owner && $emploi->owner_type === 'App\\Models\\User') {
            return $emploi->owner->nom . ' ' . $emploi->owner->prenom;
        }
        return null;
    }
    
    /**
     * Prépare les informations de la salle (physique ou virtuelle)
     */
    private function getSalleInfo($salle)
    {
        $result = [
            'affichage' => 'Non assignée',
            'lien' => null,
            'est_virtuelle' => false
        ];
        
        if (!$salle) {
            return $result;
        }
        
        if ($salle->type === 'virtuelle' && !empty($salle->lien_reunion)) {
            $result['est_virtuelle'] = true;
            $result['lien'] = $salle->lien_reunion;
            
            // Texte d'affichage avec la plateforme si disponible
            $plateforme = !empty($salle->plateforme) ? $salle->plateforme : 'Lien';
            $result['affichage'] = $salle->nom . ' (' . $plateforme . ') - Cliquer pour rejoindre';
        } else {
            $result['affichage'] = $salle->nom ?? 'Non assignée';
        }
        
        return $result;
    }
    
    private function ajouterCoursRecurrent(&$coursUniques, $emploi, $jours_fr)
    {
        $date_debut_recurrence = Carbon::parse($emploi->debut);
        $fin_recurrence = $emploi->recurrence_end_date 
            ? Carbon::parse($emploi->recurrence_end_date) 
            : $this->date_fin;
        
        $jours_map = ['SU' => 0, 'MO' => 1, 'TU' => 2, 'WE' => 3, 'TH' => 4, 'FR' => 5, 'SA' => 6];
        $jours_recurrence = explode(',', $emploi->recurrence_days ?? '');
        
        $map_jours = [
            'MO' => 'LUNDI',
            'TU' => 'MARDI',
            'WE' => 'MERCREDI',
            'TH' => 'JEUDI',
            'FR' => 'VENDREDI',
            'SA' => 'SAMEDI',
            'SU' => 'DIMANCHE'
        ];
        
        foreach ($jours_recurrence as $code_jour) {
            if (isset($jours_map[$code_jour])) {
                $jour_semaine = $jours_map[$code_jour];
                
                $date_courante = $this->date_debut->copy();
                $premiere_date = null;
                
                while ($date_courante <= $this->date_fin && $date_courante <= $fin_recurrence) {
                    if ($date_courante->dayOfWeek == $jour_semaine) {
                        $premiere_date = $date_courante->copy();
                        break;
                    }
                    $date_courante->addDay();
                }
                
                if ($premiere_date) {
                    $heure_debut = Carbon::parse($emploi->debut)->format('H:i');
                    $heure_fin = Carbon::parse($emploi->fin)->format('H:i');
                    
                    $matiere = $this->getMatiereLibelle($emploi);
                    $type = $emploi->type_programme->value ?? $emploi->type_programme;
                    
                    $jours_fr_recurrence = [];
                    foreach ($jours_recurrence as $code) {
                        if (isset($map_jours[$code])) {
                            $jours_fr_recurrence[] = $map_jours[$code];
                        }
                    }
                    
                    if (count($jours_fr_recurrence) > 1) {
                        $dernier = array_pop($jours_fr_recurrence);
                        $liste_jours = implode(', ', $jours_fr_recurrence) . ' et ' . $dernier;
                    } else {
                        $liste_jours = $jours_fr_recurrence[0] ?? '';
                    }
                    
                    
                    
                    $cle_unique = $premiere_date->format('Y-m-d') . '|' . $heure_debut . '|' . $heure_fin . '|' . $matiere . '|' . $type . '|recurrent';
                    
                    if (!isset($coursUniques[$cle_unique])) {
                        $professeur = $this->getProfesseurLibelle($emploi);
                        $salleInfo = $this->getSalleInfo($emploi->salle);
                        
                        $coursUniques[$cle_unique] = [
                            'date' => $premiere_date->format('Y-m-d'),
                            'date_obj' => $premiere_date,
                            'jour_fr' => $map_jours[$code_jour],
                            'date_formatted' => $premiere_date->format('d/m/Y'),
                            'heure_debut' => $heure_debut,
                            'heure_fin' => $heure_fin,
                            'horaire' => $heure_debut . ' - ' . $heure_fin,
                            'matiere' => $matiere,
                            'type' => $type,
                            'professeur' => $professeur,
                            'salle_affichage' => $salleInfo['affichage'],
                            'lien_salle' => $salleInfo['lien'],
                            'est_virtuelle' => $salleInfo['est_virtuelle']
                        ];
                    }
                }
            }
        }
    }

    public function title(): string
    {
        $suffix = '';
        if ($this->type_export === 'cours') {
            $suffix = ' - COURS';
        } elseif ($this->type_export === 'evaluations') {
            $suffix = ' - EVALUATIONS';
        }
        
        $nom = $this->groupe->nom ?? 'Emploi du temps';
        return substr($nom . $suffix, 0, 31);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            4 => [
                'font' => ['bold' => true, 'size' => 11],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => self::COULEUR_HEADER]
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF']],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;
                $lastColumn = 'G';
                
                // Titre principal
                $sheet->insertNewRowBefore(1, 3);
                $sheet->mergeCells('A1:' . $lastColumn . '1');
                
                $niveau = $this->groupe && $this->groupe->niveau ? $this->groupe->niveau->libelle : '';
                
                $typeTexte = '';
                if ($this->type_export === 'cours') {
                    $typeTexte = ' - COURS';
                } elseif ($this->type_export === 'evaluations') {
                    $typeTexte = ' - ÉVALUATIONS ';
                }
                
                $titre = 'EMPLOI DU TEMPS' . $typeTexte;
                if ($niveau) {
                    $titre .= ' - ' . strtoupper($niveau);
                }
                if ($this->groupe && $this->groupe->nom) {
                    $titre .= ' ' . $this->groupe->nom;
                }
                $sheet->setCellValue('A1', $titre);
                
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 18],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ]
                ]);
                
                // Période
                $sheet->mergeCells('A2:' . $lastColumn . '2');
                $periode = 'DU ' . $this->date_debut->format('d/m/Y');
                if ($this->date_fin > $this->date_debut) {
                    $periode .= ' AU ' . $this->date_fin->format('d/m/Y');
                }
                $sheet->setCellValue('A2', $periode);
                
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ]
                ]);
                
                // Date de génération
                $sheet->mergeCells('A3:' . $lastColumn . '3');
                $infos = 'Généré le ' . Carbon::now()->format('d/m/Y à H:i');
                $sheet->setCellValue('A3', $infos);
                
                $sheet->getStyle('A3')->applyFromArray([
                    'font' => ['italic' => true, 'size' => 10],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ]
                ]);
                
                // Style pour les en-têtes de jours
                $lastRow = $sheet->getHighestRow();
                
                for ($row = 5; $row <= $lastRow; $row++) {
                    $cellValue = $sheet->getCell('A' . $row)->getValue();
                    
                    if (!empty($cellValue) && $row > 4 && !$this->isHeaderRow($row) && !$this->isSeparatorRow($sheet, $row)) {
                        $sheet->mergeCells('A' . $row . ':G' . $row);
                        
                        $sheet->getStyle('A' . $row)->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => self::COULEUR_JOUR]
                            ],
                            'font' => ['bold' => true, 'size' => 11],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_LEFT,
                                'vertical' => Alignment::VERTICAL_CENTER
                            ]
                        ]);
                        
                        $sheet->getStyle('A' . $row)->getBorders()->getTop()->setBorderStyle(Border::BORDER_MEDIUM);
                    }
                }
                
                // Bordures
                $sheet->getStyle('A4:' . $lastColumn . $lastRow)
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);
                
                // Alignements
                $sheet->getStyle('A4:G4')
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
                
                for ($row = 5; $row <= $lastRow; $row++) {
                    if (!$this->isSeparatorRow($sheet, $row)) {
                        $sheet->getStyle('C' . $row)
                            ->getAlignment()
                            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        
                        $sheet->getStyle('E' . $row)
                            ->getAlignment()
                            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }
                }
                
                // Largeurs de colonnes
                $sheet->getColumnDimension('A')->setWidth(12);
                $sheet->getColumnDimension('B')->setWidth(12);
                $sheet->getColumnDimension('C')->setWidth(15);
                $sheet->getColumnDimension('D')->setWidth(50);
                $sheet->getColumnDimension('E')->setWidth(15);
                $sheet->getColumnDimension('F')->setWidth(30);
                $sheet->getColumnDimension('G')->setWidth(40);
                
                // Hauteurs de lignes
                $sheet->getRowDimension('1')->setRowHeight(30);
                $sheet->getRowDimension('2')->setRowHeight(20);
                $sheet->getRowDimension('3')->setRowHeight(15);
                $sheet->getRowDimension('4')->setRowHeight(25);
                
                // CRÉATION DES HYPERLIENS CLIQUABLES
                foreach ($this->coursAvecLiens as $coursLien) {
                    $row = $coursLien['row'] + 3; // +3 car on a inséré 3 lignes au début
                    $cell = 'G' . $row;
                    
                    // Récupérer la cellule
                    $cell = $sheet->getCell($cell);
                    
                    // Définir l'hyperlien
                    $cell->getHyperlink()->setUrl($coursLien['lien']);
                    $cell->getHyperlink()->setTooltip('Cliquer pour rejoindre la réunion');
                    
                    // Appliquer le style de lien
                    $sheet->getStyle($cell->getCoordinate())->applyFromArray([
                        'font' => [
                            'color' => ['rgb' => self::COULEUR_LIEN],
                            'underline' => true
                        ]
                    ]);
                }
                
                // Légende
                $legendeRow = $lastRow + 2;
                $sheet->mergeCells('A' . $legendeRow . ':G' . $legendeRow);
                $legende = 'LÉGENDE : ';
                $legende .= '• Les cours identiques (même date, même heure) sont automatiquement regroupés. ';
                $legende .= '• Les salles virtuelles sont indiquées en bleu souligné - Cliquez sur le lien pour rejoindre la réunion.';
                
                $sheet->setCellValue('A' . $legendeRow, $legende);
                $sheet->getStyle('A' . $legendeRow)->applyFromArray([
                    'font' => ['italic' => true, 'size' => 9],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
                ]);
                
                // Optionnel : Ajouter une section avec tous les liens
                if (!empty($this->coursAvecLiens)) {
                    $liensRow = $legendeRow + 2;
                    $sheet->mergeCells('A' . $liensRow . ':G' . $liensRow);
                    $sheet->setCellValue('A' . $liensRow, 'LISTE DES LIENS DE CONNEXION :');
                    $sheet->getStyle('A' . $liensRow)->applyFromArray([
                        'font' => ['bold' => true, 'size' => 10],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
                    ]);
                    
                    $lienRow = $liensRow + 1;
                    
                    // Grouper par salle pour éviter les doublons
                    $liensUniques = [];
                    foreach ($this->coursAvecLiens as $coursLien) {
                        $key = $coursLien['texte'] . '|' . $coursLien['lien'];
                        if (!isset($liensUniques[$key])) {
                            $liensUniques[$key] = $coursLien;
                        }
                    }
                    
                    foreach ($liensUniques as $lien) {
                        $sheet->mergeCells('A' . $lienRow . ':G' . $lienRow);
                        $sheet->setCellValue('A' . $lienRow, '• ' . $lien['texte']);
                        
                        // Créer un lien dans la légende aussi
                        $cell = $sheet->getCell('A' . $lienRow);
                        $cell->getHyperlink()->setUrl($lien['lien']);
                        $cell->getHyperlink()->setTooltip('Cliquer pour rejoindre');
                        
                        $sheet->getStyle('A' . $lienRow)->applyFromArray([
                            'font' => [
                                'color' => ['rgb' => self::COULEUR_LIEN],
                                'italic' => true,
                                'size' => 9,
                                'underline' => true
                            ]
                        ]);
                        
                        $lienRow++;
                    }
                }
            },
        ];
    }
    
    private function isHeaderRow($row)
    {
        return $row <= 4;
    }
    
    private function isSeparatorRow($sheet, $row)
    {
        $cellValue = $sheet->getCell('A' . $row)->getValue();
        return empty($cellValue) && 
               empty($sheet->getCell('B' . $row)->getValue()) && 
               empty($sheet->getCell('C' . $row)->getValue());
    }

    
}