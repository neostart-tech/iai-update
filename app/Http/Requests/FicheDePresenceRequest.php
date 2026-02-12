<?php

namespace App\Http\Requests;

use App\Models\{Evaluation, User};
use Illuminate\Foundation\Http\FormRequest;

class FicheDePresenceRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	public function rules(): array
{
    return [
        'evaluation_id' => ['required', 'exists:evaluations,slug'],
        'surveillant_1_id' => ['required', 'exists:users,slug'],
        'surveillant_2_id' => ['nullable', 'exists:users,slug'],
    ];
}


	public function attributes(): array
	{
		return [
			'surveillant_1_id' => 'Le premier surveillant',
			'surveillant_1_id.required' => 'Il vous faut au moins un surveillant',
			'surveillant_2_id' => 'Le second surveillant',
		];
	}

	public function passedValidation()
	{
		$this->merge([
			'controllable_type' => Evaluation::class,
			'controllable_id' => Evaluation::query()->firstWhere('slug', $this->get('evaluation_id'))->getAttribute('id'),
			'surveillant_1_id' => User::query()->firstWhere('slug', $this->get('surveillant_1_id'))->getAttribute('id'),
			'surveillant_2_id' => $this->get('surveillant_2_id') ? User::query()->firstWhere('slug', $this->get('surveillant_2_id'))->getAttribute('id') : null
		]);
	}
}
