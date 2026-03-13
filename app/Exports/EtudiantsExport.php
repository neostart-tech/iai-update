<?php
// app/Exports/EtudiantsExport.php

namespace App\Exports;

use App\Models\Etudiant;
use App\Models\EtudiantGroup;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;

class EtudiantsExport implements 
    FromCollection, 
    WithHeadings, 
    WithMapping, 
    WithStyles, 
    WithColumnWidths, 
    WithEvents,
    ShouldAutoSize,
    WithTitle
{
    protected $filters;
    protected $totalEtudiants;

    /**
     * Constructeur avec filtres optionnels
     */
    public function __construct($filters = [])
    {
        $this->filters = $filters;
        $this->totalEtudiants = 0;
    }

    /**
     * Retourner les étudiants avec leurs infos et filtres
     */
    public function collection()
    {
        $query = Etudiant::with([
            'etudiantGroups.filiere', 
            'etudiantGroups.niveau', 
            'etudiantGroups.group',
            'bourses'
        ]);

        // Application des filtres
        if (!empty($this->filters['niveau_id'])) {
            $query->whereHas('etudiantGroups', function($q) {
                $q->where('niveau_id', $this->filters['niveau_id']);
            });
        }

        if (!empty($this->filters['filiere_id'])) {
            $query->whereHas('etudiantGroups', function($q) {
                $q->where('filiere_id', $this->filters['filiere_id']);
            });
        }

        if (!empty($this->filters['search'])) {
            $query->where(function($q) {
                $q->where('nom', 'like', '%' . $this->filters['search'] . '%')
                  ->orWhere('prenom', 'like', '%' . $this->filters['search'] . '%')
                  ->orWhere('matricule', 'like', '%' . $this->filters['search'] . '%');
            });
        }

        if (!empty($this->filters['genre'])) {
            $query->where('genre', $this->filters['genre']);
        }

        // Tri
        $query->orderBy('nom')->orderBy('prenom');

        $etudiants = $query->get();
        $this->totalEtudiants = $etudiants->count();

        return $etudiants;
    }

    /**
     * Map les données pour Excel avec formatage amélioré
     */
    public function map($etudiant): array
    {
        $group = $etudiant->etudiantGroups->first(); // Premier groupe si plusieurs
        
        // Calcul de l'âge
        $age = $etudiant->date_naissance ? Carbon::parse($etudiant->date_naissance)->age : null;
        
        // Récupération de la bourse
        $bourse = $etudiant->bourses->first();
        $typeBourse = $bourse ? $bourse->type : null;
        $valeurBourse = $bourse  ? $bourse->valeur : null;
        
        // Formatage du téléphone
        $telephone = $etudiant->tel;
        if ($telephone && strlen($telephone) === 9) {
            $telephone = substr($telephone, 0, 2) . ' ' . 
                         substr($telephone, 2, 2) . ' ' . 
                         substr($telephone, 4, 2) . ' ' . 
                         substr($telephone, 6, 3);
        }

        return [
            $etudiant->matricule,
            strtoupper($etudiant->nom),
            ucfirst($etudiant->prenom),
            $etudiant->email,
            $telephone,
            $etudiant->genre->value === "Masculin" ? "M" : "F",
            $etudiant->date_naissance ? $etudiant->date_naissance->format('d/m/Y') : '-',
            $age ? $age . ' ans' : '-',
            $group && $group->filiere ? $group->filiere->nom : '-',
            $group && $group->niveau ? $group->niveau->libelle : '-',
            $group && $group->group ? $group->group->nom : '-',
            $typeBourse ? ucfirst($typeBourse) : '-',
            $valeurBourse ? ($typeBourse === 'pourcentage' ? $valeurBourse . '%' : number_format($valeurBourse, 0, ',', ' ') . ' F') : '-',
        ];
    }

    /**
     * Entêtes Excel améliorées
     */
    public function headings(): array
    {
        return [
            ['LISTE DES ÉTUDIANTS'],
            ['Générée le ' . Carbon::now()->format('d/m/Y à H:i')],
            ['Filtres: ' . $this->getFiltresTexte()],
            [], // Ligne vide
            [
                'MATRICULE',
                'NOM',
                'PRÉNOM',
                'EMAIL',
                'TÉLÉPHONE',
                'GENRE',
                'DATE NAISS.',
                'ÂGE',
                'FILIÈRE',
                'NIVEAU',
                'GROUPE',
                'TYPE BOURSE',
                'VALEUR BOURSE',
            ]
        ];
    }

    /**
     * Styles du fichier Excel
     */
    public function styles(Worksheet $sheet)
    {
        // Style pour le titre principal
        $sheet->mergeCells('A1:M1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getFont()->getColor()->setARGB('FF1E3A5F');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Style pour la date
        $sheet->mergeCells('A2:M2');
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(11);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Style pour les filtres
        $sheet->mergeCells('A3:M3');
        $sheet->getStyle('A3')->getFont()->setItalic(true)->setSize(10);
        $sheet->getStyle('A3')->getFont()->getColor()->setARGB('FF64748B');
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Style pour les en-têtes de colonnes
        $sheet->getStyle('A5:M5')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A5:M5')->getFont()->getColor()->setARGB(Color::COLOR_WHITE);
        $sheet->getStyle('A5:M5')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF1E3A5F');
        $sheet->getStyle('A5:M5')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        return [];
    }

    /**
     * Largeurs des colonnes
     */
    public function columnWidths(): array
    {
        return [
            'A' => 15, // Matricule
            'B' => 20, // Nom
            'C' => 20, // Prénom
            'D' => 30, // Email
            'E' => 18, // Téléphone
            'F' => 8,  // Genre
            'G' => 12, // Date naiss.
            'H' => 8,  // Âge
            'I' => 20, // Filière
            'J' => 15, // Niveau
            'K' => 15, // Groupe
            'L' => 15, // Type bourse
            'M' => 18, // Valeur bourse
        ];
    }

    /**
     * Titre de la feuille Excel
     */
    public function title(): string
    {
        return 'Liste des étudiants';
    }

    /**
     * Événements pour le formatage final
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $this->totalEtudiants + 5; // +5 pour les lignes d'en-tête

                // Ajouter une ligne de total
                $totalRow = $lastRow + 2;
                $sheet->setCellValue('A' . $totalRow, 'TOTAL ÉTUDIANTS:');
                $sheet->mergeCells('A' . $totalRow . ':D' . $totalRow);
                $sheet->setCellValue('E' . $totalRow, $this->totalEtudiants);
                
                // Style pour la ligne de total
                $sheet->getStyle('A' . $totalRow . ':M' . $totalRow)->getFont()->setBold(true);
                $sheet->getStyle('A' . $totalRow . ':M' . $totalRow)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE2E8F0');

                // Bordures pour toutes les données
                $sheet->getStyle('A5:M' . $lastRow)
                    ->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                // Alternance des couleurs pour les lignes
                for ($row = 6; $row <= $lastRow; $row++) {
                    if ($row % 2 == 0) {
                        $sheet->getStyle('A' . $row . ':M' . $row)->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('FFF8FAFC');
                    }
                }

                // Alignement du texte
                $sheet->getStyle('A6:M' . $lastRow)->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER);

                // Alignements spécifiques
                $sheet->getStyle('A6:A' . $lastRow)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER); // Matricule centré
                $sheet->getStyle('F6:F' . $lastRow)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER); // Genre centré
                $sheet->getStyle('H6:H' . $lastRow)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER); // Âge centré
                $sheet->getStyle('M6:M' . $lastRow)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT); // Valeur bourse à droite

                // Formatage spécial pour les emails (lien)
                for ($row = 6; $row <= $lastRow; $row++) {
                    $email = $sheet->getCell('D' . $row)->getValue();
                    if ($email && $email !== '-') {
                        $sheet->getCell('D' . $row)->getHyperlink()->setUrl('mailto:' . $email);
                        $sheet->getStyle('D' . $row)->getFont()->getColor()->setARGB('FF2563EB');
                    }
                }

                // Hauteur des lignes
                $sheet->getRowDimension('5')->setRowHeight(25); // En-tête plus haute
                for ($row = 6; $row <= $lastRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(20);
                }
            },
        ];
    }

    /**
     * Génère le texte des filtres appliqués
     */
    private function getFiltresTexte()
    {
        if (empty($this->filters)) {
            return 'Tous les étudiants';
        }

        $filtres = [];
        if (!empty($this->filters['niveau_id'])) {
            $niveau = \App\Models\Niveau::find($this->filters['niveau_id']);
            $filtres[] = 'Niveau: ' . ($niveau ? $niveau->libelle : $this->filters['niveau_id']);
        }
        if (!empty($this->filters['filiere_id'])) {
            $filiere = \App\Models\Filiere::find($this->filters['filiere_id']);
            $filtres[] = 'Filière: ' . ($filiere ? $filiere->nom : $this->filters['filiere_id']);
        }
        if (!empty($this->filters['search'])) {
            $filtres[] = 'Recherche: "' . $this->filters['search'] . '"';
        }
        if (!empty($this->filters['genre'])) {
            $filtres[] = 'Genre: ' . $this->filters['genre'];
        }

        return empty($filtres) ? 'Tous les étudiants' : implode(' | ', $filtres);
    }
}