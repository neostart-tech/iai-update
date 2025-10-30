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
		$filiereId = (int)$this->route('filiere', new Filiere())->getAttribute('id');

		return [
			'nom' => ['required', Rule::unique('filieres')->ignore($filiereId)],
			'code' => ['required', Rule::unique('filieres')->ignore($filiereId)],
			'annee_scolaire' => ['nullable', 'number'],
			'image' => ['nullable', File::image()],
			'description' => ['nullable'],
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
