<?php

namespace App\Jobs;

use App\Models\{Etudiant, Evaluation, FicheDePresence};
use App\Notifications\Etudiants\EtudiantAbsentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Support\{Collection, Str};
use Illuminate\Support\Facades\Notification;

class FicheDePresenceSubmittingJob implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public function __construct(private readonly FicheDePresence $fiche, private readonly Collection $etudiantsAbsentsSlugs)
	{
	}

	public function handle(): void
	{
		/**
		 * @var Evaluation $ev
		 */
		$ev = $this->fiche->controllable;

		$message = config('messages.absences.evaluation.parents');
		$message = Str::replace(':_salutation', $greetingTime = getGreetingTime(), $message); // Message avec salutation
		$message = Str::replace(':_info_evaluation', $evaluationInfo = $ev->getInformationsForMessaging(), $message); // Message avec salutation et informations de l'évaluation

		$this->fiche->controllable->group->etudiants->each(function (Etudiant $etudiant) use ($message, $greetingTime, $evaluationInfo) {
			$title = "Absence à une évaluation";
			$etudiant->fichesDePresence()->attach($this->fiche, [
				'was_present' => $was_present = !$this->etudiantsAbsentsSlugs->contains($etudiant->getAttribute('slug')),
				...injectAnneeScolaireId()
			]);

			if (!$was_present) {

				// Notification du tuteur et du responsable des frais par SMS
				$message = Str::replace(':_nom_prenom_etu', $etudiantInfo = $etudiant->completName(), $message); // Message avec salutation et informations de l'évaluation
				$responsableMessage = $message;
				$message = Str::replace(':_nom_prenom_resp', $etudiant->tuteur->completName(), $message); // Message avec informations de l'étudiant
				// SmsSendingProcess::dispatch($etudiant->tuteur->getAttribute('tel'), $message);
				Notification::send($etudiant->tuteur, new EtudiantAbsentNotification($title, $message));
				if (!$etudiant->areTuteurAndParentTheSamePerson()) {
					$responsableMessage = Str::replace(':_nom_prenom_resp', $etudiant->responsableFrais->completName(), $message); // Message avec informations de l'étudiant
					// SmsSendingProcess::dispatch($etudiant->tuteur->getAttribute('tel'), $responsableMessage);
					Notification::send($etudiant->responsableFrais, new EtudiantAbsentNotification($title, $message));
				}

				$etudiantMessage = config('messages.absences.evaluation.etudiant');
				$etudiantMessage = Str::replace(':_salutation', $greetingTime, $etudiantMessage);
				$etudiantMessage = Str::replace(':_nom_prenom_etu', $etudiantInfo, $etudiantMessage);
				$etudiantMessage = Str::replace(':_info_evaluation', $evaluationInfo, $etudiantMessage);

				Notification::send($etudiant, new EtudiantAbsentNotification(title: $title, content: $etudiantMessage));
			}
		});
		$this->fiche->update(['processed' => true]);
	}
}
