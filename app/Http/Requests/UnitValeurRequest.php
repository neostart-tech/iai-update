<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UnitValeurRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	public function rules(): array
	{
		return [
			'nom' => ['required', Rule::unique('unite_valeurs')->ignore($this->route('uv'), 'nom')],
			'code' => ['required', Rule::unique('unite_valeurs')->ignore($this->route('uv'), 'code')],
			'cm' => ['required', 'numeric', 'integer', 'min:1'],
			'td' => ['required', 'numeric', 'integer', 'min:1'],
			'tp' => ['required', 'numeric', 'integer', 'min:1'],
			'ec' => ['required', 'numeric', 'integer', 'min:1'],
			'coefficient' => ['required', 'numeric', 'integer', 'min:1'],
//			'annee_scolaire_id' => ['nullable', 'exists:annee_scolaire,id'],
			'ue_id' => ['required', 'exists:unite_enseignements,id'],
			'enseignant_id' => ['required'],
			// Optional weighting fields (0-100)
			'poids_devoir' => ['nullable', 'integer', 'min:0', 'max:100'],
			'poids_interrogation' => ['nullable', 'integer', 'min:0', 'max:100'],
			'poids_examen' => ['nullable', 'integer', 'min:0', 'max:100'],
			'poids_tp' => ['nullable', 'integer', 'min:0', 'max:100'],
			'poids_expose' => ['nullable', 'integer', 'min:0', 'max:100'],
		];
	}

	public function attributes(): array
	{
		return [
			'nom' => 'Le nom cette unité d\'enseignement',
			'code' => 'Le code cette unité d\'enseignement',
			'cm' => 'Le volume horaire de CM',
			'td' => 'Le volume horaire de travaux pratiques',
			'tp' => 'Le volume horaire de',
			'ec' => 'Le volume horaire de EC',
			'coefficient' => 'Le coefficient de cette unité d\'enseignement',
			'ue_id' => 'L\'unité d\'enseignement',
			'enseignant_id' => 'L\'enseignent de cette unité d\'enseignement',
		];
	}

	protected function passedValidation()
	{
		$this->whenHas('annee_scolaire_id', function (string $input) {
			$this->merge(['annee_scolaire_id' => (int)$input]);
		}, function () {
			$this->merge(injectAnneeScolaireId());
		});
		$this->merge([
			'unite_enseignement_id' => $this->integer('ue_id'),
		]);
	}

	public function withValidator($validator)
	{
		$validator->after(function ($validator) {
			$keys = ['poids_devoir', 'poids_interrogation', 'poids_examen', 'poids_tp', 'poids_expose'];
			$values = [];
			$anyProvided = false;
			foreach ($keys as $k) {
				$v = (int) $this->input($k, 0);
				$values[] = $v;
				if ($v > 0) { $anyProvided = true; }
			}
			$sum = array_sum($values);
			if ($anyProvided && $sum !== 100) {
				$validator->errors()->add('poids_total', 'La somme des pourcentages doit être égale à 100 si vous renseignez des poids.');
			}
		});
	}
}
