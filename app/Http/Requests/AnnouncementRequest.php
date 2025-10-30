<?php

namespace App\Http\Requests;

use App\Enums\{TypeAnnonceEnum, TypeContratEnum};
use App\Models\Advertiser;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnnouncementRequest extends FormRequest
{

	public function authorize(): bool
	{
		return true;
	}

	public function rules(): array
	{
		return [
			'advertiser_id' => ['required', 'exists:advertisers,slug'],
			'type_annonce' => ['required', Rule::enum(TypeAnnonceEnum::class)],
			'type_contrat' => ['required', Rule::enum(TypeContratEnum::class)],
			'ville' => ['nullable'],
			'file_path' => ['nullable', Rule::requiredIf(fn() => $this->isEmptyString('content')), 'file'],
			'content' => ['nullable', Rule::requiredIf(fn() => $this->isEmptyString('file_path'))],
			'duration' => [
				'nullable',
				Rule::requiredIf(fn() => $this->enum('type_contrat', TypeContratEnum::class) === TypeContratEnum::CDD),
				'max:63'
			]
		];
	}

	public function attributes(): array
	{
		return [
			'advertiser' => 'L\'auteur de l\'annonceur',
			'type' => 'Le type de l\'opportunité',
			'title' => 'Le titre de l\'opportunité',
			'ville' => 'La ville',
			'file_path' => 'Le document officiel de l\'opportunité',
			'content' => 'Le contenu de l\'opportunité',
		];
	}

	public function messages(): array
	{
		return [
			'duration.required' => 'La durée de l\'opportunité est obligatoire quand il s\'agit d\'un CDD',
		];
	}

	protected function passedValidation()
	{
		$this->merge([
			'advertiser_id' => ($advertiser = Advertiser::query()->firstWhere('slug', $this->input('advertiser_id')))
				->getAttribute('id'),
			'ville' => $this->input('ville') ?? $advertiser->getAttribute('ville')
		]);
	}
}
