<?php
namespace App\Exports;

use App\Http\Controllers\ComptabiliteController;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PaiementsExport implements FromArray, WithHeadings
{
    public function __construct(private int $year) {}

    public function array(): array
    {
        $data = app()->make(ComptabiliteController::class)
                    ->buildEtudiantsInfos($this->year);

        return $data->map(fn($e)=>[
            $e['etudiant']->nom.' '.$e['etudiant']->prenoms,
            $e['niveau'],
            $e['filiere'],
            $e['total_frais'],
            $e['total_paye'],
            $e['statut'],
        ])->toArray();
    }
    public function headings(): array
    {
        return ['Étudiant','Niveau','Filière','Total dû','Total payé','Statut'];
    }
}
