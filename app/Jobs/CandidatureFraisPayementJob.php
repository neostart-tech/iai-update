<?php

namespace App\Jobs;

use App\Models\Candidature;
use App\Notifications\Candidatures\CandidatPayementNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class CandidatureFraisPayementJob implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public function __construct(private readonly Collection $candidatures)
	{
	}

	public function handle(): void
	{
		$candidatures = Candidature::query()->whereIn('slug', $this->candidatures)->get();
		$candidatures->each(function (Candidature $candidature) {
			$candidature->update([
				'frai_paye_date' => now(),
				'frais_paye' => true
			]);

			$content = $candidature->greeting();
			$content .= '. Le payement de votre quittance pour la participation au concours de sélection de IAI-Togo a été 
			enregistré avec succès. Votre code pour le concours est le suivant: ' . $candidature->getAttribute('code');

			$candidature->notify(new CandidatPayementNotification($content));
		});
	}
}
