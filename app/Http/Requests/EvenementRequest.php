<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EvenementRequest extends FormRequest
{

	public function authorize(): bool
	{
		return true;
	}

	public function rules(): array
	{
		return [
			'nom' => ['required', 'max:255'],
			'start_date' => ['required', 'date', 'after_or_equal:today'],
			'end_date' => ['nullable', 'date', 'after_or_equal:end_date'],
			'details' => ['required']
		];
	}

	public function attributes(): array
	{
		return [
			'nom' => 'Le nom de l\'évènement',
			'start_date' => 'La date de début de l\'évènement',
			'end_date' => 'La date de fin de l\'évènement',
			'details' => 'Le de l\'évènement',
		];
	}
}
