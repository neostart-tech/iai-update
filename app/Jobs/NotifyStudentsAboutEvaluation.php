<?php

namespace App\Jobs;

use App\Enums\TypeProgrammeEnum;
use App\Models\{EmploiDuTemp, Etudiant, Evaluation};
use App\Notifications\EvaluationNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Support\Facades\Notification;

class NotifyStudentsAboutEvaluation implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public function __construct(private readonly Evaluation $evaluation){}

	public function handle(): void
	{
		$matiere = $this->evaluation->matiere;

		// Enregistre l'évènement dans les emplois du temps
		EmploiDuTemp::create([
			'debut' => $this->evaluation->getAttribute('debut'),
			'fin' => $this->evaluation->getAttribute('fin'),
			'uv_id' => $matiere->getAttribute('id'),
			'type_programme' => TypeProgrammeEnum::EVALUATION->value,
			'group_id' => $this->evaluation->getAttribute('group_id'),
			'salle_id' => $this->evaluation->getAttribute('salle_id'),
			'details' => $this->evaluation->getAttribute('type')->value . " de " . $matiere->getAttribute('nom'),
			...injectAnneeScolaireId()
		]);

		// Envoi de notifications aux étudiants concernés
		$this->evaluation->group->etudiants->each(function (Etudiant $etudiant) {
			dump($etudiant->getAttribute('nom'));
			Notification::send(
				$etudiant,
				new EvaluationNotification(
					$etudiant->greeting(),
					$this->evaluation->getDataAsString()
				)
			);
		});
	}
}
