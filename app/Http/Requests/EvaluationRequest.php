<?php

namespace App\Http\Requests;

use App\Enums\TypeEvaluationEnum;
use App\Models\{Evaluation, Group, UniteValeur};
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
			'semestre' => ['required', 'integer', 'between:1,2'],
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
			'debut' => Carbon::createFromFormat('Y-m-d H:i', $this->get('date') . ' ' . $this->get('debut')),
			'fin' => Carbon::createFromFormat('Y-m-d H:i', $this->get('date') . ' ' . $this->get('fin')),
			'date' => Carbon::createFromFormat('Y-m-d', $this->get('date')),
			'group_id' => Group::query()->firstWhere('slug', $this->get('group_id'))->getAttribute('id'),
			'unite_valeur_id' => UniteValeur::query()->firstWhere('slug', $this->get('unite_valeur_id'))->getAttribute('id'),
			'correction_end_date' => $this->input('correction_end_date')
		]);
	}

	public function withValidator($validator)
	{
		$validator->after(function ($validator) {
			// Vérifier la limitation à 2 évaluations par type par UV
			$groupId = Group::query()->firstWhere('slug', $this->get('group_id'))?->getAttribute('id');
			$uvId = UniteValeur::query()->firstWhere('slug', $this->get('unite_valeur_id'))?->getAttribute('id');
			$type = $this->get('type');

			if ($groupId && $uvId && $type) {
				$evaluationsCount = Evaluation::where('group_id', $groupId)
					->where('unite_valeur_id', $uvId)
					->where('type', $type)
					->count();

				// Exclure l'évaluation actuelle en cas de modification
				if ($this->route('evaluation')) {
					$evaluationsCount = Evaluation::where('group_id', $groupId)
						->where('unite_valeur_id', $uvId)
						->where('type', $type)
						->where('id', '!=', $this->route('evaluation')->id)
						->count();
				}

				if ($evaluationsCount >= 2) {
					$validator->errors()->add('type', 'Limite atteinte : maximum 2 évaluations de type "' . $type->value . '" par matière.');
				}
			}
		});
	}
}
