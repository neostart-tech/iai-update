<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	public function rules(): array
	{
		return [
			'nom' => ['required'],
			'email' => ['required', 'email'],
			'tel' => ['required'],
			'message' => ['required', 'max:500']
		];
	}

	public function attributes(): array
	{
		return [
			'nom' => "Votre nom",
			'email' => "Votre adresse mail",
			'tel' => "Votre numéro de téléphone",
			'message' => 'Votre message'
		];
	}
}
