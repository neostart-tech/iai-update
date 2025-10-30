<?php

namespace App\Http\Requests\Admin;

use App\Enums\GenreEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	public function rules(): array
	{
		return [
			'nom' => ['required'],
			'prenom' => ['required'],
			'biographie' => ['nullable'],
			'genre' => ['required', Rule::enum(GenreEnum::class)],
			'email' => ['required', 'email', 'unique:users,email'],
			'roles' => ['nullable', 'array', 'min:1'],
			'tel' => ['required'],
			'supervisor_type' => ['required', 'in:interne,externe,non_surveillant'],
			'supervisor_notes' => ['nullable', 'string']
		];

	}

	public function message()
	{
		return [
			"email.unique" => "L'adresse mail est déjà utilisée",
			"roles.min" => "Veuillez choisir au moins un rôle",
			"nom.required" => "Le nom est requis",
			"prenom.required" => "Le prénom est requis",
			"genre.required" => "Le genre est requis",
			"tel.required" => "Le numéro de téléphone est requis",
			"supervisor_type.required" => "Le type de surveillant est requis",
			"supervisor_type.in" => "Type de surveillant invalide"
		];
	}


	public function attributes(): array
	{


		return [
			'nom' => 'Le nom',
			'prenom' => 'Le nom',
			'genre' => 'Le genre',
			'email' => 'L\'adresse mail',
			'roles' => 'Le rôle',
			'tel' => 'Le numero de téléphone',
			'supervisor_type' => 'Le type de surveillant',
			'supervisor_notes' => 'Les notes de surveillance'
		];
	}

	public function messages(): array
	{
		return [
			'enum' => 'Le genre choisi n\'est pas valide'
		];
	}

	protected function passedValidation(): void
	{
		if ($this->enum('genre', GenreEnum::class) === GenreEnum::M) {
			$image = config('images.teachers.man');
		} else {
			$image = config('images.teachers.woman');
			;
		}
		$this->merge(['image' => $image]);
		$this->merge(['matricule' => Str::upper(uniqid())]);
		$this->merge(['password' => Hash::make($clearPassword = Str::random(8))]);
		$this->merge(compact('clearPassword'));
	}
}
