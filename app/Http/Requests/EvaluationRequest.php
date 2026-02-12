<?php

namespace App\Http\Requests;

use App\Enums\TypeEvaluationEnum;
use App\Models\{AnneeScolaire, Evaluation, Group, UniteValeur};
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class EvaluationRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	public function rules(): array
	{
		return [
			'group_id' => ['required', 'exists:groups,slug'],
			'salle_id' => ['required', 'exists:salles,id'],
			'unite_valeur_id' => ['required', 'exists:unite_valeurs,slug'],
			'niveau_id' => ['nullable', 'exists:niveaux,id'],
			'semestre' => ['nullable', 'integer'],
			'published' => ['nullable'],
			'type' => ['required', Rule::enum(TypeEvaluationEnum::class)],
			'debut' => ['required'],
			'fin' => ['required'],
			'date' => ['required', 'date'],
			'correction_end_date' => ['nullable', 'date', 'after:now']
		];
	}

	public function attributes(): array
	{
		return [
			'group_id' => 'Le groupe',
			'salle_id' => 'La salle',
			'unite_valeur_id' => 'L\'unité de valeur',
			'published' => 'le statut de publication',
			'debut' => 'L\'heure de début',
			'fin' => 'L\'heure de fin',
			'type_id' => 'Le type de l\'évaluation',
			'date' => 'La date de l\'évaluation',
			'correction_end_date' => 'La date de remise des corrections des copies'
		];
	}

	protected function passedValidation()
{
    $this->merge([
        'published' => $this->boolean('published'),

        'debut' => Carbon::parse($this->debut),
        'fin'   => Carbon::parse($this->fin),

        'date' => Carbon::createFromFormat('Y-m-d', $this->date),

        'group_id' => Group::whereSlug($this->group_id)->value('id'),
        'unite_valeur_id' => UniteValeur::whereSlug($this->unite_valeur_id)->value('id'),

        'correction_end_date' => $this->correction_end_date
            ? Carbon::parse($this->correction_end_date)
            : null,
    ]);
}


	// public function withValidator($validator)
	// {
	// 	$validator->after(function ($validator) {
	// 		// Vérifier la limitation à 2 évaluations par type par UV
	// 		$groupId = Group::query()->firstWhere('slug', $this->get('group_id'))?->getAttribute('id');
	// 		$uvId = UniteValeur::query()->firstWhere('slug', $this->get('unite_valeur_id'))?->getAttribute('id');
	// 		$type = $this->get('type');

	// 		if ($groupId && $uvId && $type) {
	// 			$evaluationsCount = Evaluation::where('group_id', $groupId)
	// 				->where('unite_valeur_id', $uvId)
	// 				->where('type', $type)
	// 				->count();

	// 			// Exclure l'évaluation actuelle en cas de modification
	// 			if ($this->route('evaluation')) {
	// 				$evaluationsCount = Evaluation::where('group_id', $groupId)
	// 					->where('unite_valeur_id', $uvId)
	// 					->where('type', $type)
	// 					->where('id', '!=', $this->route('evaluation')->id)
	// 					->count();
	// 			}

	// 			if ($evaluationsCount >= 2) {
	// 				$validator->errors()->add('type', 'Limite atteinte : maximum 2 évaluations de type "' . $type . '" par matière.');
	// 			}
	// 		}
	// 	});
	// }

	public function withValidator($validator)
	{
		$validator->after(function ($validator) {

			$anneeScolaireId = AnneeScolaire::where('active', true)->value('id');

			$groupId = Group::whereSlug($this->group_id)->value('id');
			$uvId = UniteValeur::whereSlug($this->unite_valeur_id)->value('id');
			$type = $this->type;

			// Sécurité
			if (! $anneeScolaireId || ! $groupId || ! $uvId || ! $type) {
				return;
			}

			$query = Evaluation::where('annee_scolaire_id', $anneeScolaireId)
				->where('group_id', $groupId)
				->where('unite_valeur_id', $uvId)
				->where('type', $type);

			// if ($evaluation = $this->route('evaluation')) {
			// 	$query->where('id', '!=', $evaluation->id);
			// }

			// if ($query->count() >= 2) {
			// 	$validator->errors()->add(
			// 		'type',
			// 		"Limite atteinte : maximum 2 évaluations de type « {$type} » par matière pour l’année scolaire active."
			// 	);
			// }
		});
	}
}
