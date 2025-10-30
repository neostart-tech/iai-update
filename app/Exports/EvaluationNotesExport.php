<?php

namespace App\Exports;

use App\Models\Evaluation;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EvaluationNotesExport implements FromCollection, WithHeadings
{
    public function __construct(private readonly Evaluation $evaluation)
    {
    }

    public function collection(): Collection
    {
        return $this->evaluation
            ->notes()
            ->with(['etudiant:id,matricule,nom,prenom'])
            ->orderBy('anonymat')
            ->get()
            ->map(function ($note) {
                return [
                    'Matricule' => $note->etudiant->matricule,
                    'Nom' => $note->etudiant->nom,
                    'Prénom' => $note->etudiant->prenom,
                    'Anonymat' => $note->anonymat,
                    'Note' => $note->notation === 'R' ? 'R' : $note->note,
                ];
            });
    }

    public function headings(): array
    {
        return ['Matricule', 'Nom', 'Prénom', 'Anonymat', 'Note'];
    }
}
