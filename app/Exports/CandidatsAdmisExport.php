<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CandidatsAdmisExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $candidatures;

    public function __construct(Collection $candidatures)
    {
        $this->candidatures = $candidatures;
    }

    public function collection()
    {
        return $this->candidatures;
    }

    public function headings(): array
    {
        return [
            'Nom',
            'Prénom',
            'Email',
            'Téléphone',
            'Matricule concours',
            'Niveau',
            'Filière',
            "Date d'admission",
            'Déjà inscrit',
        ];
    }

    public function map($candidature): array
    {
        return [
            $candidature->nom ?? '--',
            $candidature->prenom ?? '--',
            $candidature->email ?? '--',
            $candidature->tel ?? '--',
            $candidature->matricule_concours ?? '--',
            $candidature->niveau->libelle ?? '--',
            $candidature->filiere->nom ?? '--',
            $candidature->admission_date ? $candidature->admission_date->format('d/m/Y') : '--',
            $candidature->etudiant_id ? 'Oui' : 'Non',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
