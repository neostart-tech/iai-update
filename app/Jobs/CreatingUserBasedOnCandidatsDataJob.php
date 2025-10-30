<?php

namespace App\Jobs;

use App\Models\{Candidature, Etudiant};
use App\Notifications\Candidatures\{CandidatAccountLockNotification, CandidatToEtudiantWelcomeNotification};
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use App\Helpers\ConfigHelper as AppGetters;
use Illuminate\Support\{Collection, Facades\Log, Str};

class CreatingUserBasedOnCandidatsDataJob implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public function __construct(private readonly Collection $candidatures, private readonly int $groupId)
	{
	}

	public function handle(): void
	{
		$this->candidatures->each(function (string $slug) {
			/**
			 * @var Candidature $candidature
			 */
			$candidature = Candidature::query()->firstWhere('slug', $slug);

			if (!$candidature) {
				return;
			}
			// dump("Etudiant: " . $candidature->getAttribute('nom') . ' ' . $candidature->getAttribute('prenom'));
			$etudiant = Etudiant::create([
				'nom' => $candidature->getAttribute('nom'),
				'nom_jeune_fille' => $candidature->getAttribute('nom_jeune_fille'),
				'prenom' => $candidature->getAttribute('prenom'),
				'genre' => $candidature->getAttribute('genre'),
				'date_naissance' => $candidature->getAttribute('date_naissance'),
				'lieu_naissance' => $candidature->getAttribute('lieu_naissance'),
				'nationalite' => $candidature->getAttribute('nationalite'),
				'tel' => $candidature->getAttribute('tel'),
				'email' => $candidature->getAttribute('email'),
				'password' => $candidature->getAttribute('password'),
				'image' => config('images.etudiants.woman'),
				'annee_admission' => $year = today()->year,
				'matricule' => Str::upper($year . '_' . fake()->unique()->randomNumber(6, true)),
			]);

			$etudiant->groups()->attach(
				$this->groupId,
				injectAnneeScolaireId()
			);

			$updatedData = [
				'owner_id' => $etudiant->getAttribute('id'),
				'owner_type' => Etudiant::class,
			];

			$candidature->album->update($updatedData);

			$candidature->responsable->update($updatedData);

			$candidature->tuteur->update($updatedData);

			$candidature->update([
				'etudiant_id' => $etudiant->getAttribute('id'),
				'acceptation_date' => $now = now(),
				'end_accessibility_date' => $endAccessibilityDate = $now->addDays(3)
			]);

			$etudiant->notify(new CandidatToEtudiantWelcomeNotification($etudiant->greeting()));
			$message = $candidature->greeting();
			$message .= '. Suite à votre admission à ' .' '.AppGetters::getAppName() ? AppGetters::getAppName() : "Laravel"  . ', vous avez désormais un compte étudiant. 
				Ce espace candidat vous sera accessible jusqu\'au ' . $endAccessibilityDate->translatedFormat('d F Y')
				. '. L\'accès à votre espace étudiant se fait avec les identifiants du présent compte candidat.';

			$candidature->notify(new CandidatAccountLockNotification($message));
			// Msg("Operations  effectué avec succees pour:  " . $candidature->getAttribute('nom') . ' ' . $candidature->getAttribute('prenom'));

		});
	}

	public function fail($exception = null)
	{
		Log::info("Échec de l'opération: ", [$exception]);
	}

}
