<?php

namespace App\Services;

use App\Models\Candidature;
use App\Notifications\Candidatures\{CandidatAbsentNotification, CandidatPresentNotification};
use Illuminate\Support\Collection;

class CandidaturePresenceService
{
    /**
     * Marque les candidats listés comme absents (notifie candidat + tuteur + responsable),
     * et marque tous les autres candidats éligibles restants comme présents.
     *
     * @return array{presents: string[], absents: string[]}
     */
    public function processPresence(Collection $absentSlugs): array
    {
        $absentCandidats = Candidature::query()->whereIn('slug', $absentSlugs)->get();

        $absentCandidats->each(function (Candidature $candidat) {
            $candidat->update([
                'participation' => false,
                'participation_date' => now(),
            ]);

            $message = $candidat->greeting();
            $message .= '. Nous avons remarqué que vous n\'avez pas pu participer à l\'épreuve du concours qui a eu
			 lieu. Nous regrettons sincèrement votre absence et comprenons que des imprévus peuvent survenir.
			 Nous espérons vous voir participer à nos futurs événements et concours';

            $candidat->notify(new CandidatAbsentNotification($message));

            $candidat->tuteur?->notify(new CandidatAbsentNotification($message));
            $candidat->responsable?->notify(new CandidatAbsentNotification($message));
        });

        $presentCandidats = Candidature::query()
            ->where('dossier_valide', true)
            ->where('frais_paye', true)
            ->where('participation', false)
            ->whereNull('participation_date')
            ->where('admission', false)
            ->whereNull('motif')
            ->whereNotIn('slug', $absentSlugs)
            ->get();

        $presentCandidats->each(function (Candidature $candidat) {
            $message = $candidat->greeting();

            $candidat->update([
                'participation' => true,
                'participation_date' => now(),
            ]);

            $message .= ' . Nous tenons à vous remercier pour votre participation au concours . Nous tenons à vous informer que
			les résultats seront annoncés le [Date prévue]. Nous vous prions de bien vouloir patienter jusqu\'à cette date.';

            $candidat->notify(new CandidatPresentNotification($message));
        });

        return [
            'presents' => $presentCandidats->pluck('slug')->values()->all(),
            'absents' => $absentCandidats->pluck('slug')->values()->all(),
        ];
    }
}
