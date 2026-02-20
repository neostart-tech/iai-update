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
use Carbon\Carbon;

class EmploiDuTempsMatriceExport implements FromArray, WithTitle, WithStyles, ShouldAutoSize, WithEvents
{
    protected $group_id;
    protected $date_debut;
    protected $date_fin;
    protected $groupe;
    protected $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
    protected $creneaux = [];

    public function __construct($group_id, $date_debut, $date_fin)
    {
        $this->group_id = $group_id;
        $this->date_debut = Carbon::parse($date_debut)->startOfWeek(Carbon::MONDAY);
        $this->date_fin = Carbon::parse($date_fin)->endOfWeek(Carbon::SUNDAY);
        $this->groupe = Group::with('niveau')->find($group_id); // ← AJOUT: charger le niveau
        $this->genererCreneaux();
    }

    private function genererCreneaux()
    {
        $heures = [8, 10, 12, 14, 16, 18, 20, 22];
        
        foreach ($heures as $heure) {
            $debut = sprintf('%02d:00', $heure);
            $fin = sprintf('%02d:00', $heure + 2);
            $this->creneaux[] = $debut . ' - ' . $fin;
        }
    }

    public function array(): array
    {
        $data = [];
        
        // En-têtes
        $header = ['Horaire'];
        $date_courante = $this->date_debut->copy();
        
        foreach ($this->jours as $jour) {
            $header[] = $jour . ' ' . $date_courante->format('d/m');
            $date_courante->addDay();
        }
        $data[] = $header;

        // Récupérer les cours
        $cours = $this->getCours();

        // Remplir le tableau
        foreach ($this->creneaux as $creneau) {
            $ligne = [$creneau];
            
            $date_courante = $this->date_debut->copy();
            foreach ($this->jours as $jour) {
                $cours_trouve = $this->findCours($cours, $jour, $date_courante->format('Y-m-d'), $creneau);
                $ligne[] = $cours_trouve ?: '-';
                $date_courante->addDay();
            }
            
            $data[] = $ligne;
        }

        return $data;
    }

    private function getCours()
    {
        $cours = [];
        
        $emplois = EmploiDuTemp::with(['uv', 'salle'])
            ->where('group_id', $this->group_id)
            ->whereIn('type_programme', ['Cours', 'Évaluation', 'Événement'])
            ->get();

        foreach ($emplois as $emploi) {
            if ($emploi->recurrence_type === 'aucune') {
                $date = Carbon::parse($emploi->debut);
                if ($date->between($this->date_debut, $this->date_fin)) {
                    $cours[] = [
                        'date' => $date->format('Y-m-d'),
                        'jour' => $this->jours[$date->dayOfWeek - 1] ?? 'Dimanche',
                        'heure_debut' => $date->format('H:i'),
                        'heure_fin' => Carbon::parse($emploi->fin)->format('H:i'),
                        'matiere' => $emploi->uv->nom ?? 'Cours',
                        'salle' => $emploi->salle->nom ?? 'N/A',
                        'type' => $emploi->type_programme
                    ];
                }
            } 
            elseif ($emploi->recurrence_type === 'hebdomadaire') {
                $this->addRecurrences($emploi, $cours);
            }
        }

        return $cours;
    }

    private function addRecurrences($emploi, &$cours)
    {
        $debut_original = Carbon::parse($emploi->debut);
        $fin_recurrence = $emploi->recurrence_end_date 
            ? Carbon::parse($emploi->recurrence_end_date) 
            : $this->date_fin;
        
        $jours_map = ['SU' => 0, 'MO' => 1, 'TU' => 2, 'WE' => 3, 'TH' => 4, 'FR' => 5, 'SA' => 6];
        $jours_recurrence = explode(',', $emploi->recurrence_days ?? '');
        
        $date_courante = $this->date_debut->copy();
        
        while ($date_courante <= $this->date_fin && $date_courante <= $fin_recurrence) {
            $jour_semaine = $date_courante->dayOfWeek;
            $jour_code = array_search($jour_semaine, $jours_map);
            
            if (in_array($jour_code, $jours_recurrence)) {
                $cours[] = [
                    'date' => $date_courante->format('Y-m-d'),
                    'jour' => $this->jours[$jour_semaine - 1] ?? 'Dimanche',
                    'heure_debut' => $debut_original->format('H:i'),
                    'heure_fin' => Carbon::parse($emploi->fin)->format('H:i'),
                    'matiere' => $emploi->uv->nom ?? 'Cours',
                    'salle' => $emploi->salle->nom ?? 'N/A',
                    'type' => $emploi->type_programme
                ];
            }
            
            $date_courante->addDay();
        }
    }

    private function findCours($cours, $jour, $date, $creneau)
    {
        list($heure_debut, $heure_fin) = explode(' - ', $creneau);
        
        $cours_trouves = array_filter($cours, function($c) use ($jour, $date, $heure_debut, $heure_fin) {
            return $c['jour'] === $jour 
                && $c['date'] === $date
                && $c['heure_debut'] >= $heure_debut 
                && $c['heure_fin'] <= $heure_fin;
        });

        if (empty($cours_trouves)) {
            return null;
        }

        $resultats = [];
        foreach ($cours_trouves as $c) {
            $resultats[] = $c['matiere'] . ' (' . $c['salle'] . ')';
        }

        return implode(" / ", $resultats);
    }

    public function title(): string
    {
        return 'Emploi du temps';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E3A8A']
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF']],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;
                $lastColumn = 'G'; // Lundi à Samedi = 6 jours + colonne Horaire = 7 colonnes (A à G)
                $lastRow = count($this->creneaux) + 1;
                
                // Titre avec niveau
                $sheet->insertNewRowBefore(1, 2);
                $sheet->mergeCells('A1:' . $lastColumn . '1');
                
                // ← MODIFICATION: Ajout du niveau dans le titre
                $niveau = $this->groupe && $this->groupe->niveau ? $this->groupe->niveau->libelle : '';
                $titre = 'EMPLOI DU TEMPS';
                if ($niveau) {
                    $titre .= ' - ' . $niveau;
                }
                if ($this->groupe && $this->groupe->nom) {
                    $titre .= ' - ' . $this->groupe->nom;
                }
                $sheet->setCellValue('A1', $titre);
                
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // Période
                $sheet->mergeCells('A2:' . $lastColumn . '2');
                $sheet->setCellValue('A2', 'Semaine du ' . $this->date_debut->format('d/m/Y') . ' au ' . $this->date_fin->format('d/m/Y'));
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // Bordures
                $sheet->getStyle('A3:' . $lastColumn . ($lastRow + 2))
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);
                
                // Centrer le texte
                $sheet->getStyle('A3:' . $lastColumn . ($lastRow + 2))
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);
                
                // Hauteur des lignes
                for ($row = 4; $row <= $lastRow + 2; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(30);
                }
            },
        ];
    }
}