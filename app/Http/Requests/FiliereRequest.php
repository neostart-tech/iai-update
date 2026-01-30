<?php

namespace App\Http\Requests;

use App\Models\Filiere;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class FiliereRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	public function rules(): array
	{
		// $filiereId = (int)$this->route('filiere', new Filiere())->getAttribute('id');
		$afficherChoixDate = \AppGetters::getAfficherChoixDate();


		return [
			'nom' => ['required', Rule::unique('filieres')],
			'code' => ['required', Rule::unique('filieres')],
			'annee_scolaire' => ['nullable', 'number'],
			'image' => ['nullable', File::image()],
			'description' => ['nullable'],
			'date_debut' => [
				$afficherChoixDate ? 'required' : 'nullable',
				'date',
			],
			'date_fin' => [
				$afficherChoixDate ? 'required' : 'nullable',
				'date',
				'after_or_equal:date_debut',
			],
		];
	}

	public function attributes(): array
	{
		return [
			'nom' => 'Ce nom de filière',
			'code' => 'Ce code de filière',
			'description' => 'La description de la filière',
			'annee_scolaire' => 'L\'année scolaire choisie',
			'image' => 'L\'image d\'illustration de filière',
			'date_debut' => "La date début",
			'date_fin' => "La date fin",
		];
	}

	protected function passedValidation(): void
	{
		$this->whenHas('annee_scolaire_id', function (string $input) {
			$this->merge(['annee_scolaire_id' => (int)$input]);
		}, function () {
			$this->merge(injectAnneeScolaireId());
		});
	}
}
