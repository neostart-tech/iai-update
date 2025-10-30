<?php

namespace App\Jobs;

use App\Models\Candidature;
use App\Notifications\Candidatures\CandidatAdmisNotification;
use App\Notifications\Candidatures\CandidatRecaleNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Stringable;

class ConcoursResultJob implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public function __construct(private readonly Stringable $admis, private readonly Stringable $recales)
	{
	}

	public function handle(): void
	{
		$admis = Candidature::query()->whereIn('slug', $this->admis->explode(','))->get();
		$recales = Candidature::query()->whereIn('slug', $this->recales->explode(','))->get();

		$admis->each(function (Candidature $candidature) {
			$candidature->update([
				'admission' => true,
				'admission_date' => now()
			]);

			$message = $candidature->greeting();
			$message .= ', nos félicitations. Vous avez êtes admis.e à ' . env('APP_NAME') . ' suite au concours que vous avez passé.';

			$candidature->notify(new CandidatAdmisNotification($message));
		});

		$recales->each(function (Candidature $candidature) {
			$candidature->update([
				'admission' => false,
				'admission_date' => now()
			]);

			$message = $candidature->greeting();
			$message .= ', désolé, vous n\'êtes pas admis.e à ' . env('APP_NAME') . ' suite au concours que vous avez passé. Bonne chance pour la suite';

			$candidature->notify(new CandidatRecaleNotification($message));
		});
	}
}
