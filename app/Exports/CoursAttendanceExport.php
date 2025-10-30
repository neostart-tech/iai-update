<?php

namespace App\Exports;

use App\Models\{Cours, CoursPresence, EmploiDuTemp, UniteValeur};
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CoursAttendanceExport implements FromArray, WithHeadings
{
    private array $data;
    public string $fileName = 'recap_presence.xlsx';

    public function __construct(Cours $cours, ?EmploiDuTemp $emploi = null)
    {
    $emploi = $emploi ?? $cours->emploisDuTemps()->latest('debut')->first();
    $matiere = optional($emploi?->uv)->nom ?? optional($emploi?->uv)->code ?? $cours->titre;
        $salle = optional($emploi?->salle)->nom ?? '';
        $creneau = ($emploi?->debut?->format('d/m/Y H:i') ?? '').' - '.($emploi?->fin?->format('H:i') ?? '');

        $rows = CoursPresence::with(['etudiant','sanction'])
            ->where('cours_id', $cours->id)
            ->get()
            ->map(function ($p) use ($matiere, $salle, $creneau) {
                return [
                    'Matiere' => $matiere,
                    'Salle' => $salle,
                    'Creneau' => $creneau,
                    'Etudiant' => $p->etudiant?->completName() ?? '',
                    'Statut' => $p->statut,
                    'Commentaire' => $p->commentaire,
                    'Sanction' => $p->sanction?->description,
                    'Validation' => $p->needs_validation ? 'En attente' : '—',
                ];
            })->toArray();

        // Teacher recap row (if available)
        $teacher = \App\Models\EnseignantPresence::where('emploi_du_temps_id', $emploi?->id)
            ->with('enseignant')
            ->first();
        if ($teacher) {
            $rows[] = [
                'Matiere' => $matiere,
                'Salle' => $salle,
                'Creneau' => $creneau,
                'Etudiant' => 'ENSEIGNANT',
                'Statut' => $teacher->statut,
                'Commentaire' => $teacher->commentaire,
                'Sanction' => null,
                'Validation' => '—',
            ];
        }

        $this->data = $rows;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        return ['Matiere', 'Salle', 'Creneau', 'Etudiant', 'Statut', 'Commentaire', 'Sanction', 'Validation'];
    }
}
