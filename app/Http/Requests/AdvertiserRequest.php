<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdvertiserRequest extends FormRequest
{
	// public function authorize(): bool
	// {
	// 	return true;
	// }

	public function rules(): array
	{
		return [
			'nom' => ['required'],
			// 'email' => ['required', 'email', Rule::unique('advertisers')->ignore($this->route()->parameter('advertiser'))],
			'email' => ['required', 'email'],
			'ville' => ['nullable', 'string'],
			'site' => ['nullable', 'url'],
			'details' => ['required', 'string'],
			'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', 

		];
	}

	public function attributes(): array
	{
		return [
			'nom' => 'Le nom du partenaire',
			'email' => 'L\'adresse mail du partenaire',
			'ville' => 'La ville du partenaire',
			'site' => 'Le site du partenaire',
			'details' => 'Les informations du partenaire',
		];
	}
}
