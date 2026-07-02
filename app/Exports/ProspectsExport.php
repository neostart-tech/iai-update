<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProspectsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $prospects;

    public function __construct(Collection $prospects)
    {
        $this->prospects = $prospects;
    }

    public function collection()
    {
        return $this->prospects;
    }

    public function headings(): array
    {
        return [
            'Nom et Prénom',
            'Adresse Email',
            'Téléphone',
            'Formation Visée',
            'Statut',
            'Date de Réception',
        ];
    }

    public function map($prospect): array
    {
        $formations = '--';
        if (!empty($prospect->formation_visee)) {
            // Replace ' | ' with actual newlines for Excel
            $formations = str_replace(' | ', "\n", $prospect->formation_visee);
        }

        return [
            $prospect->nom ?? '--',
            $prospect->email ?? '--',
            $prospect->tel ?? '--',
            $formations,
            $prospect->status ? 'Contacté' : 'Non contacté',
            $prospect->created_at ? $prospect->created_at->format('d/m/Y H:i') : '--',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Activer le renvoi à la ligne pour la colonne D (Formation Visée)
        $sheet->getStyle('D')->getAlignment()->setWrapText(true);

        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
