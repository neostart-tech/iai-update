<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UnitEnseignementRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	public function rules(): array
	{
		return [
			'nom' => ['required'],
			'code' => ['required'],
			'credit' => ['required', 'integer', 'min:1'],
			'periode_id' => ['required', 'exists:periodes,id'],
			'filiere_id' => ['required', 'exists:filieres,id'],
		];
	}

	public function attributes(): array
	{
		return [
			'nom' => 'Le nom de l\'UE',
			'code' => 'Le code de l\'UE',
			'credit' => 'Le nombre de crédits de l\'UE',
			'periode_id' => 'La période de l\'UE',
			'filiere_id' => 'La filière de l\'UE',
		];
	}

	protected function passedValidation(): void
	{
		$this->merge(injectAnneeScolaireId());
	}
}
