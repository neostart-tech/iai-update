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
			'start_date' => ['required', 'date'],
			'end_date' => ['nullable', 'date'],
			'details' => ['required'],
			'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
			'type' => ['required', 'string', 'in:internal,public'],
			'destination' => ['required', 'string', 'in:intranet,website,all'],
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
