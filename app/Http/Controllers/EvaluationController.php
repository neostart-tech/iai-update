<?php

namespace App\Http\Controllers;

use App\Enums\TypeEvaluationEnum;
use App\Http\Controllers\Admin\NoteController;
use App\Http\Requests\EvaluationRequest;
use App\Jobs\NotifyStudentsAboutEvaluation;
use App\Models\{Evaluation, Group, Salle, User};
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Foundation\Application;
use Illuminate\Http\{RedirectResponse, Response};
use Illuminate\View\View;
use Throwable;

class EvaluationController extends Controller
{
	public function index(): View
	{
		return view('admin.evaluations.index')->with([
			'evaluations' => Evaluation::query()
				->with([
					'salle:id,nom',
					'group:id,nom,filiere_id',
					'group.filiere:id,code',
					'matiere:id,nom,code',
					'fiche.surveillants'
				])
				->orderByDesc('debut')
				->get()
				->map(function (Evaluation $evaluation) {
					$evaluation->setAttribute('dateFormatted', $evaluation->getAttribute('debut')->translatedFormat('d F Y'));
					$evaluation->setAttribute('debutFormatted', $evaluation->getAttribute('debut')->translatedFormat('H:i'));
					$evaluation->setAttribute('finFormatted', $evaluation->getAttribute('fin')->translatedFormat('H:i'));
					return $evaluation;
				}),
			'enseignants' => User::all()
		]);
	}

	public function create(): View
	{
		return view('admin.evaluations.create')->with([
			'evaluation' => new Evaluation([
				'debut' => '12:00',
				'fin' => '14:00',
				'correction_end_date' => now()->addWeeks(2)
			]),
			'groups' => Group::all(),
			'salles' => Salle::all(),
			'types' => TypeEvaluationEnum::cases(),
			'niveaux' => \App\Models\Niveau::all(),
		]);
	}

	public function store(EvaluationRequest $request): RedirectResponse
	{
		// dd('Ceci est un test');
		$evaluation = Evaluation::create([
			...$request->only([
				'type',
				'group_id',
				'unite_valeur_id',
				'salle_id',
				'niveau_id',
				'semestre',
				'date',
				'debut',
				'fin',
				'published',
				'correction_end_date'
			]),
			...injectAnneeScolaireId()
		]);

		if ($evaluation->getAttribute('published')) {
			NotifyStudentsAboutEvaluation::dispatch($evaluation);
		}

		return to_route('admin.evaluations.index')->with(successMsg('Évaluation enregistrée avec succès'));
	}

	public function show(Evaluation $evaluation): View
	{
		return view('admin.evaluations.show', [
			'evaluation' => $evaluation,
			'salles' => Salle::all(),
			'enseignants' => User::all(),
		]);
	}

	public function edit(Evaluation $evaluation): View|RedirectResponse
	{
		if ($evaluation->getAttribute('published') or $evaluation->getAttribute('debut')->isBefore(now())) {
			warningMsg("L'évènement ne peut plus être modifier");
			return back();
		}

		return view('admin.evaluations.edit', compact('evaluation'))->with([
			'groups' => Group::all(),
			'salles' => Salle::all(),
			'types' => TypeEvaluationEnum::cases(),
			'niveaux' => \App\Models\Niveau::all(),
			'enseignants' => User::all(),
		]);
	}

	public function update(EvaluationRequest $request, Evaluation $evaluation): RedirectResponse
	{
		$evaluation->setAllWaysUpdate(false);
		$evaluation->update([
			...$request->only([
				'type',
				'group_id',
				'unite_valeur_id',
				'salle_id',
				'niveau_id',
				'semestre',
				'date',
				'debut',
				'fin',
				'published',
				'correction_end_date'
			])
		]);

		if ($evaluation->getAttribute('published')) {
			NotifyStudentsAboutEvaluation::dispatch($evaluation);
		}

		successMsg('Évaluation mise à jour avec succès');
		return to_route('admin.evaluations.index');
	}

	public function publish(string $slug): Application|Response|ResponseFactory
	{
		$evaluation = Evaluation::query()->firstWhere('slug', $slug);

		if (!$evaluation)
			return __404();

		/**
		 * @var Evaluation $evaluation
		 */

		if ($evaluation->getAttribute('published'))
			return __200();

		try {
			$evaluation->update(['published' => true]);
			NotifyStudentsAboutEvaluation::dispatch($evaluation);
		} catch (Throwable $exception) {
			return __500($exception->getMessage());
		}

		return response([
			'message' => 'Annonce d\'évaluation publiée avec succès.'
		]);
	}

	public function getNoteFiche(Evaluation $evaluation): View
	{
		return (new NoteController())->evaluationNotesIndex($evaluation);
	}
}
