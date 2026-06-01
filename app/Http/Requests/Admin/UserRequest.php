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
		$rules = [
			'nom' => ['required', 'string', 'max:255'],
			'prenom' => ['required', 'string', 'max:255'],
			'biographie' => ['nullable', 'string'],
			'genre' => ['required', Rule::enum(GenreEnum::class)],
			'email' => ['required', 'email', 'unique:users,email'],
			'roles' => ['nullable', 'array', 'min:1'],
			'tel' => ['required', 'string', 'max:20'],
			'supervisor_type' => ['required', 'in:interne,externe,non_surveillant'],
			'supervisor_notes' => ['nullable', 'string'],
			'nationalite' => ['nullable', 'string', 'max:255'],
			'nif' => ['nullable', 'string', 'max:50', 'unique:users,nif'],
			'identity_document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
			'nif_document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
			'diploma_document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
			'cv_document' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
		];

		// Si la nationalité est Togo, le NIF devient obligatoire
		if ($this->input('nationalite') === 'Togo') {
			$rules['nif'] = ['required', 'string', 'max:50', 'unique:users,nif'];
			$rules['nif_document'] = ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'];
		}

		return $rules;
	}

	public function messages(): array
	{
		return [
			'email.unique' => "L'adresse mail est déjà utilisée",
			'roles.min' => "Veuillez choisir au moins un rôle",
			'nom.required' => "Le nom est requis",
			'prenom.required' => "Le prénom est requis",
			'genre.required' => "Le genre est requis",
			'tel.required' => "Le numéro de téléphone est requis",
			'supervisor_type.required' => "Le type de surveillant est requis",
			'supervisor_type.in' => "Type de surveillant invalide",
			'nationalite.required' => "La nationalité est requise",
			'nif.required' => "Le numéro NIF est obligatoire pour les professeurs togolais",
			'nif.unique' => "Ce numéro NIF est déjà utilisé",
			'identity_document.required' => "La pièce d'identité est obligatoire",
			'identity_document.file' => "Le fichier doit être un document valide",
			'identity_document.mimes' => "Le document doit être au format PDF, JPG, JPEG ou PNG",
			'identity_document.max' => "Le document ne doit pas dépasser 5 Mo",
			'nif_document.required' => "Le justificatif du NIF est obligatoire pour les professeurs togolais",
			'nif_document.mimes' => "Le document NIF doit être au format PDF, JPG, JPEG ou PNG",
			'diploma_document.mimes' => "Le diplôme doit être au format PDF, JPG, JPEG ou PNG",
			'diploma_document.max' => "Le diplôme ne doit pas dépasser 10 Mo",
			'cv_document.mimes' => "Le CV doit être au format PDF, DOC ou DOCX",
			'cv_document.max' => "Le CV ne doit pas dépasser 5 Mo",
		];
	}

	public function attributes(): array
	{
		return [
			'nom' => 'Le nom',
			'prenom' => 'Le prénom',
			'genre' => 'Le genre',
			'email' => 'L\'adresse mail',
			'roles' => 'Le rôle',
			'tel' => 'Le numéro de téléphone',
			'supervisor_type' => 'Le type de surveillant',
			'supervisor_notes' => 'Les notes de surveillance',
			'nationalite' => 'La nationalité',
			'nif' => 'Le numéro d\'identification fiscale',
			'identity_document' => 'La pièce d\'identité',
			'nif_document' => 'Le justificatif du NIF',
			'diploma_document' => 'Le diplôme',
			'cv_document' => 'Le CV',
		];
	}

	protected function prepareForValidation(): void
	{
		// Nettoyer le NIF (supprimer les espaces)
		if ($this->has('nif')) {
			$this->merge([
				'nif' => preg_replace('/\s+/', '', $this->nif)
			]);
		}
	}

	protected function passedValidation(): void
	{
		if ($this->enum('genre', GenreEnum::class) === GenreEnum::M) {
			$image = config('images.teachers.man');
		} else {
			$image = config('images.teachers.woman');
		}
		
		$this->merge(['image' => $image]);
		$this->merge(['matricule' => Str::upper(uniqid())]);
		$this->merge(['password' => Hash::make($clearPassword = Str::random(8))]);
		$this->merge(compact('clearPassword'));
	}
}