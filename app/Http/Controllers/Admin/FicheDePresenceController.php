<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TypeProgrammeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\FicheDePresenceRequest;
use App\Jobs\FicheDePresenceSubmittingJob;
use App\Models\{EmploiDuTemp, Evaluation, FicheDePresence, User};
use App\Notifications\{EnseignantEvaluationDeProgrammationNotification, EnseignantEvaluationProgrammationNotification};
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class FicheDePresenceController extends Controller
{
	public function index(): View
	{
		return view('admin.fiches.index')->with([
			'fiches' => FicheDePresence::all()
		]);
	}

	public function store(FicheDePresenceRequest $request): RedirectResponse
	{
		$fiche = FicheDePresence::create([
			...$request->only(['controllable_type', 'controllable_id']),
			...injectAnneeScolaireId(),
		]);

		$fiche->surveillants()->attach($request->only(['surveillant_1_id', 'surveillant_2_id']), injectAnneeScolaireId());

		/**
		 * @var User $surveillant1
		 */
		$surveillant1 = User::query()->find($request->get('surveillant_1_id'));
		$this->notifySurveillants($fiche, $surveillant1, User::query()->find($request->get('surveillant_2_id')));

		successMsg('Fiche de présence configurée avec succès');
		return back();
	}

	public function update(FicheDePresenceRequest $request, FicheDePresence $fiche): RedirectResponse
	{
		$this->notifyNewSurveillants($request, $fiche);

		$fiche->surveillants()->syncWithPivotValues($request->only(['surveillant_1_id', 'surveillant_2_id']), injectAnneeScolaireId());

		$fiche->update([
			...$request->only(['controllable_type', 'controllable_id']),
		]);

		successMsg('Fiche de présence mise à jour avec succès');
		return back();
	}

	public function make(FicheDePresence $fiche): View
	{
		// S'assurer que seule la personne autorisée à effectuer cette action accède à la page
		if (!$fiche->surveillants->pluck('id')->contains(request()->user()->id)) {
			abort(Response::HTTP_UNAUTHORIZED);
		}

		$controllable = $fiche->controllable;
		$etudiants = $controllable->group->etudiants;
		$type = $controllable::class === Evaluation::class ? 'une évaluation' : 'un cours';

		return view('admin.fiches.make', compact('fiche', 'etudiants'))->with([
			'breadCrumbs' => ['Administration', 'Contrôle', 'Présence à ' . $type]
		]);
	}

	public function submit(Request $request, FicheDePresence $fiche): RedirectResponse
	{
		if ($fiche->getAttribute('submitted')) {
			warningMsg("Cette fiche de présence a déjà été soumise");
			return back();
		}

		FicheDePresenceSubmittingJob::dispatchAfterResponse($fiche, $request->collect('etudiants'));

		$fiche->update(['submitted' => true]);
		successMsg('Fiche de présence soumise avec succès');
		return back();
	}

	public function show(FicheDePresence $fiche): View
	{
		return view('admin.fiches.show');
	}

	private function notifySurveillants(FicheDePresence $fiche, User $surveillant1, User $surveillant2 = null)
	{
		$this->addToEmploiDuTemps($fiche, $surveillant1->getAttribute('id'));
		$surveillant1->notify(
			new EnseignantEvaluationProgrammationNotification(
				$surveillant1->greeting() .
				" Vous êtes programmé pour la surveillance durant l'évaluation suivante: "
				. $fiche->controllable->getDataAsString()
			)
		);

		if ($surveillant2) {
			$this->addToEmploiDuTemps($fiche, $surveillant2->getAttribute('id'));
			$surveillant2->notify(
				new EnseignantEvaluationProgrammationNotification(
					$surveillant2->greeting() .
					" Vous êtes programmé pour la surveillance durant l'évaluation suivante: "
					. $fiche->controllable->getDataAsString()
				)
			);
		}
	}

	private function notifyNewSurveillants(FicheDePresenceRequest $request, FicheDePresence $fiche)
	{
		// 1- Notification de déprogrammation
		// Cette méthode permettra de notifier un surveillant s'il est déprogrammé et de notifier le nouveau qui est mis à sa place
		// Test sur le premier surveillant

		//Todo: Changer l'accès aux surveillants

		$old_surveillant_1 = $fiche->surveillants->first();
		$old_surveillant_1_id = $old_surveillant_1;
		$old_surveillant_2_id = null;
		$new_surveillant_1_id = $request->get('surveillant_1_id');
		$new_surveillant_2_id = $request->get('surveillant_2_id');

		if (
			$fiche->getAttribute('surveillant_1_id') !== $request->get('surveillant_1_id') &&
			$fiche->getAttribute('surveillant_1_id') !== $request->get('surveillant_2_id')
		) {
			$old_surveillant_1->notify(new EnseignantEvaluationDeProgrammationNotification(
				$old_surveillant_1->greeting() .
				". Vous êtes déprogrammé pour la surveillance durant l'évaluation suivante: " .
				$fiche->controllable->getDataAsString()
			));
		}

		// Cette première condition est importante parce que les seconds surveillants sont optionnels
		if (
			($fiche->getAttribute('surveillant_2_id') && $request->get('surveillant_2_id')) &&
			(
				$fiche->getAttribute('surveillant_2_id') !== $request->get('surveillant_1_id') &&
				$fiche->getAttribute('surveillant_2_id') !== $request->get('surveillant_2_id')
			)
		) {
			$old_surveillant_2 = $fiche->surveillants->last();
			$old_surveillant_2_id = $old_surveillant_2->getAttribute('id');
			$old_surveillant_2->notify(new EnseignantEvaluationDeProgrammationNotification(
				$old_surveillant_2->greeting() .
				". Vous êtes déprogrammé pour la surveillance durant l'évaluation suivante: " .
				$fiche->controllable->getDataAsString()
			));
		}

		// 2- Notification de programmation
		if (
			$fiche->getAttribute('surveillant_1_id') !== $request->get('surveillant_1_id') &&
			$fiche->getAttribute('surveillant_2_id') !== $request->get('surveillant_1_id')
		) {
			/**
			 * @var User $new_surveillant1
			 */
			$new_surveillant1 = User::query()->find($request->get('surveillant_1_id'));
			$new_surveillant_1_id = (int)$new_surveillant1->getAttribute('id');
			$new_surveillant1->notify(new EnseignantEvaluationProgrammationNotification(
				$new_surveillant1->greeting() .
				" Vous êtes programmé pour la surveillance durant l'évaluation suivante: "
				. $fiche->controllable->getDataAsString()
			));
		}

		if (
			$request->get('surveillant_2_id') &&
			(
				$fiche->getAttribute('surveillant_1_id') !== $request->get('surveillant_2_id') &&
				$fiche->getAttribute('surveillant_2_id') !== $request->get('surveillant_2_id')
			)) {
			/**
			 * @var User $new_surveillant2
			 */
			$new_surveillant2 = User::query()->find($request->get('surveillant_2_id'));
			$new_surveillant2->notify(new EnseignantEvaluationProgrammationNotification(
				$new_surveillant2->greeting() .
				" Vous êtes programmé pour la surveillance durant l'évaluation suivante: "
				. $fiche->controllable->getDataAsString()
			));
		}
		$this->updateSurveillant($fiche, $old_surveillant_1_id, $new_surveillant_1_id, $old_surveillant_2_id, $new_surveillant_2_id);
	}

	private function addToEmploiDuTemps(FicheDePresence $fiche, int $surveillant_id)
	{
		/**
		 * @var Evaluation $ev
		 */
		$ev = $fiche->controllable;

		$data = [
			'debut' => $ev->getAttribute('debut'),
			'fin' => $ev->getAttribute('fin'),
			'uv_id' => $ev->getAttribute('unite_valeur_id'),
			'type_programme' => TypeProgrammeEnum::EVALUATION->value,
			'salle_id' => $ev->getAttribute('salle_id'),
			'details' => $ev->getDataAsString(),
			'group_id' => $ev->getAttribute('group_id'),
			...injectAnneeScolaireId(),
		];

		EmploiDuTemp::create([
			... $data,
			'owner_id' => $surveillant_id,
			'owner_type' => User::class
		]);
	}

	private function updateSurveillant(FicheDePresence $fiche, User $old_surveillant_1, int $new_surveillant_1_id, User $old_surveillant_2 = null, int $new_surveillant_2_id = null)
	{
		/**
		 * @var Evaluation $ev
		 */
		$ev = $fiche->controllable;
		// dump('ici');
		// Remplacement du premier surveillant par un autre
		$old_surveillant_1->emploiDuTemps()
			->where('type_programme', TypeProgrammeEnum::EVALUATION)
			->where('debut', $ev->getAttribute('debut'))
			->where('fin', $ev->getAttribute('fin'))
			->where('salle_id', $ev->getAttribute('salle_id'))
			->update(['owner_id' => $new_surveillant_1_id]);

		// Si on tient vraiment à remplacer le second surveillant
		if ($old_surveillant_2 && $new_surveillant_2_id) {
			$old_surveillant_2->emploiDuTemps()
				->firstWhere('type_programme', TypeProgrammeEnum::EVALUATION)
				->where('debut', $ev->getAttribute('debut'))
				->where('fin', $ev->getAttribute('fin'))
				->where('salle_id', $ev->getAttribute('salle_id'))
				->update(['owner_id' => $new_surveillant_2_id]);
		}

		// S'il n'y avait pas de second surveillant choisi, alors on programme qui aura été choisi
		if (!$old_surveillant_2 && $new_surveillant_2_id) {
			/**
			 * @var User $new_surveillant_2
			 */
			$new_surveillant_2 = User::query()->find($new_surveillant_2_id);
			if ($new_surveillant_2) {
				$this->addToEmploiDuTemps($fiche, $new_surveillant_2_id);
			}
		}
		// Si on ne choisit pas de second surveillant, on déprogramme quand-même l'ancien
		if ($old_surveillant_2 && !$new_surveillant_2_id) {
			$old_surveillant_2->emploiDuTemps()
				->firstWhere('type_programme', TypeProgrammeEnum::EVALUATION)
				->where('debut', $ev->getAttribute('debut'))
				->where('fin', $ev->getAttribute('fin'))
				->where('salle_id', $ev->getAttribute('salle_id'))
				->delete();
		}

		/*
			La condition ($old_surveillant_2 && !$new_surveillant_2_id) implique qu'on ne veut
			pas changer le second surveillant donc, aucune action n'est nécessaire
		*/
	}
}
