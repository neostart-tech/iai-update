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

	public function store(FicheDePresenceRequest $request)
	{
		$fiche = FicheDePresence::create([
			...$request->only(['controllable_type', 'controllable_id']),
			...injectAnneeScolaireId(),
		]);

		$fiche->surveillants()->attach($request->only(['surveillant_1_id', 'surveillant_2_id']), injectAnneeScolaireId());

		/**
		 * @var User $surveillant1
		 */
		$surveillant1 = User::find($request->surveillant_1_id);
		if (!$surveillant1) {
			return response()->json([
				'message' => 'Surveillant introuvable'
			], 422);
		}

		$this->notifySurveillants($fiche, $surveillant1, User::query()->find($request->get('surveillant_2_id')));

		// successMsg('Fiche de présence configurée avec succès');
		// return back();
		return response()->json(["message" => "Fiche configurée avec succes"], 201);
	}

	public function update(FicheDePresenceRequest $request, FicheDePresence $fiche)
	{
		$this->notifyNewSurveillants($request, $fiche);

		$currentIds = $fiche->surveillants()
			->pluck('users.id')
			->values();

		$finalIds = collect($currentIds);

		if ($request->surveillant_1_id) {
			$finalIds[0] = $request->surveillant_1_id;
		}

		if ($request->has('surveillant_2_id')) {
			if ($request->surveillant_2_id) {
				$finalIds[1] = $request->surveillant_2_id;
			} else {
				$finalIds->forget(1);
			}
		}

		$fiche->surveillants()->syncWithPivotValues(
			$finalIds->filter()->unique()->toArray(),
			injectAnneeScolaireId()
		);

		$fiche->update(
			$request->only(['controllable_type', 'controllable_id'])
		);

		return response()->json([
			"message" => "Fiche mise à jour avec succès"
		], 200);
	}



	public function make(FicheDePresence $fiche)
	{
		// S'assurer que seule la personne autorisée à effectuer cette action accède à la page
		if (!$fiche->surveillants->pluck('id')->contains(request()->user()->id)) {
			abort(Response::HTTP_UNAUTHORIZED);
		}

		$controllable = $fiche->controllable;
		$etudiants = $controllable->group->etudiants;
		$type = $controllable::class === Evaluation::class ? 'une évaluation' : 'un cours';


		// return view('admin.fiches.make', compact('fiche', 'etudiants'))->with([
		// 	'breadCrumbs' => ['Administration', 'Contrôle', 'Présence à ' . $type]
		// ]);
		return response()->json(["message" => "Fiche configurée avec succes", 'fiche' => $fiche, 'etudiants' => $etudiants], 201);
	}

	public function submit(Request $request, FicheDePresence $fiche)
	{
		if ($fiche->getAttribute('submitted')) {
			// warningMsg("Cette fiche de présence a déjà été soumise");
			// return back();
			return response()->json(["message" => "Cette fiche de présence a déjà été soumise"], 404);
		}

		FicheDePresenceSubmittingJob::dispatchAfterResponse($fiche, $request->collect('etudiants'));

		$fiche->update(['submitted' => true]);
		// successMsg('Fiche de présence soumise avec succès');
		// return back();
		return response()->json(["message" => "Fiche configurée avec succes"], 201);
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
		$old = $fiche->surveillants->values();
		$old1 = $old->get(0);
		$old2 = $old->get(1);

		$new1Id = $request->surveillant_1_id;
		$new2Id = $request->surveillant_2_id;

		foreach ([$old1, $old2] as $oldUser) {
			if ($oldUser && !in_array($oldUser->id, array_filter([$new1Id, $new2Id]))) {
				$oldUser->notify(
					new EnseignantEvaluationDeProgrammationNotification(
						$oldUser->greeting() .
							" Vous êtes déprogrammé pour : " .
							$fiche->controllable->getDataAsString()
					)
				);
			}
		}

		foreach (array_filter([$new1Id, $new2Id]) as $newId) {
			if (!$old->pluck('id')->contains($newId)) {
				$user = User::find($newId);
				if ($user) {
					$user->notify(
						new EnseignantEvaluationProgrammationNotification(
							$user->greeting() .
								" Vous êtes programmé pour : " .
								$fiche->controllable->getDataAsString()
						)
					);
				}
			}
		}

		$this->updateSurveillant($fiche, $old1, $new1Id, $old2, $new2Id);
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
			...$data,
			'owner_id' => $surveillant_id,
			'owner_type' => User::class
		]);
	}

	private function updateSurveillant(FicheDePresence $fiche, ?User $old1, int $new1Id, ?User $old2 = null, ?int $new2Id = null)
	{
		$ev = $fiche->controllable;

		if ($old1) {
			$old1->emploiDuTemps()
				->where('type_programme', TypeProgrammeEnum::EVALUATION)
				->where('debut', $ev->debut)
				->where('fin', $ev->fin)
				->where('salle_id', $ev->salle_id)
				->update(['owner_id' => $new1Id]);
		}

		if ($old2 && $new2Id) {
			$old2->emploiDuTemps()
				->where('type_programme', TypeProgrammeEnum::EVALUATION)
				->where('debut', $ev->debut)
				->where('fin', $ev->fin)
				->where('salle_id', $ev->salle_id)
				->update(['owner_id' => $new2Id]);
		}

		if (!$old2 && $new2Id) {
			$this->addToEmploiDuTemps($fiche, $new2Id);
		}

		if ($old2 && !$new2Id && request()->has('surveillant_2_id')) {
			$old2->emploiDuTemps()
				->where('type_programme', TypeProgrammeEnum::EVALUATION)
				->where('debut', $ev->debut)
				->where('fin', $ev->fin)
				->where('salle_id', $ev->salle_id)
				->delete();
		}
	}
}
