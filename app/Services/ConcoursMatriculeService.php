<?php

namespace App\Services;

use App\Models\Candidature;
use Illuminate\Support\Facades\DB;

class ConcoursMatriculeService
{
    /**
     * Génère et retourne un matricule de concours structuré pour le candidat,
     * sans le persister (à l'appelant de sauvegarder l'attribut).
     */
    public function generateFor(Candidature $candidature): string
    {
        $year = $candidature->created_at?->year ?? now()->year;

        return DB::transaction(function () use ($year) {
            $count = Candidature::where('matricule_concours', 'like', "CONC-{$year}-%")
                ->lockForUpdate()
                ->count();

            $nextNumber = $count + 1;

            return sprintf('CONC-%d-%04d', $year, $nextNumber);
        });
    }
}
