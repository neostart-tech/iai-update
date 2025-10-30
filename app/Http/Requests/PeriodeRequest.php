<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PeriodeRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	public function rules(): array
	{
		return [
			'nom' => ['required'],
			'description' => ['required'],
			'debut' => ['required', 'before_or_equal:' . $this->date('fin'), 'after_or_equal:' . today()],
			'fin' => ['required', 'after_or_equal:' . today(), 'after_or_equal:' . $this->date('debut')]
		];
	}

	public function attributes(): array
	{
		return [
			'nom' => 'Le nom de la période',
			'description' => 'La description de la période',
			'debut' => 'La date de début de la période',
			'fin' => 'La date de fin de la période',
		];
	}

	protected function passedValidation(): void
	{
		$this->merge(injectAnneeScolaireId());
	}
}
