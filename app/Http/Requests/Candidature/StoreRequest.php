<?php

namespace App\Http\Requests\Candidature;

use App\Enums\{GenreEnum, TypeDiplomeEnum};
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	public function rules(): array
	{
		return [
			// Identité 1
			'nom' => ['required'],
			'prenom' => ['required'],
			'nom_jeune_fille' => ['nullable', Rule::requiredIf(fn() => $this->enum('genre', GenreEnum::class) === GenreEnum::F)],
			'genre' => ['required', Rule::enum(GenreEnum::class)],
			'date_naissance' => ['required', 'before:' . today()->subYears(16)],
			'lieu_naissance' => ['required'],

			// Champs admission similaires
			'numero_table' => ['required'],
			'annee_bac' => ['required', 'integer', 'between:1990,' . date('Y')],
			'serie' => ['required', Rule::in(['C','D'])],
			'lettre_motivation' => ['required', 'string'],

			// Identité 2
			'tel' => ['required', 'min:8'],
			'tel2' => ['nullable', 'min:8'],
			'tel3' => ['nullable', 'min:8'],
			'email' => ['nullable', 'email', 'unique:candidatures'],
			'nationalite' => ['required'],
			'hobbit' => ['required'],
			'bp' => ['nullable'],
			'fax' => ['nullable'],

			// Docs
			'lettre_file' => ['required', 'mimes:pdf', 'max:4096'],
			'naissance_file' => ['required', 'mimes:pdf', 'max:4096'],
			'nationalite_file' => ['required', 'mimes:pdf', 'max:4096'],
			'diplome_file' => ['required', 'mimes:pdf', 'max:4096'],
			'photo_identite_file' => ['required', 'image', 'max:4096'],
			'certificat_medical_file' => ['nullable', 'mimes:pdf', 'max:4096'],
			'coupon_file' => ['nullable', 'mimes:pdf', 'extensions:pdf', 'max:4096'],
			'type_diplome' => ['required', Rule::enum(TypeDiplomeEnum::class)],

			// Bulletins multi-fichiers par niveau (min 2 fichiers)
			'bulletins_seconde' => ['required', 'array', 'min:2'],
			'bulletins_seconde.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
			'bulletins_premiere' => ['required', 'array', 'min:2'],
			'bulletins_premiere.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
			'bulletins_terminale' => ['required', 'array', 'min:2'],
			'bulletins_terminale.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
			'releve_bac1' => ['required', 'array', 'min:1'],
			'releve_bac1.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
			'releve_bac2' => ['required', 'array', 'min:1'],
			'releve_bac2.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],

			// Responsable des frais
			'nom_resp' => ['required'],
			'prenom_resp' => ['required'],
			'profession_resp' => ['required'],
			'employeur_resp' => ['required'],
			'email_resp' => ['required'],
			'tel_resp' => ['required'],
			'adresse_resp' => ['required'],
			'fax_resp' => ['nullable'],
			'bp_resp' => ['nullable'],

			// Tuteur
			'nom_tuteur' => ['required'],
			'prenom_tuteur' => ['required'],
			'profession_tuteur' => ['required'],
			'employeur_tuteur' => ['required'],
			'email_tuteur' => ['required'],
			'tel_tuteur' => ['required'],
			'adresse_tuteur' => ['required'],
			'fax_tuteur' => ['nullable'],
			'bp_tuteur' => ['nullable'],

			// Acceptation
			'accept_cgu' => ['accepted'],
		];
	}

	public function attributes(): array
	{
		return [
			// Identité 1
			'nom' => 'Votre nom',
			'prenom' => 'Votre prénom',
			'nom_jeune_fille' => 'Votre nom de jeune fille',
			'genre' => 'Votre genre',
			'date_naissance' => 'Votre date de naissance',
			'lieu_naissance' => 'Votre lieu de naissance',

			// Champs admission similaires
			'numero_table' => 'Votre numéro de table',
			'annee_bac' => 'Votre année de baccalauréat',
			'serie' => 'Votre série',
			'lettre_motivation' => 'Votre lettre de motivation',

			// Identité 2
			'tel' => 'Votre téléphone',
			'tel2' => 'Votre deuxième téléphone',
			'tel3' => 'Votre troisième téléphone',
			'email' => 'Votre adresse mail',
			'nationalite' => 'Votre nationalité',
			'hobbit' => 'Vos centres d\'intérêt',
			'bp' => '',
			'fax' => '',

			// Docs
			'lettre_file' => 'Le fichier contenant la lettre de motivation',
			'naissance_file' => 'Le fichier contenant l\'extrait de l\'acte de naissance',
			'nationalite_file' => 'Le fichier contenant la nationalité',
			'diplome_file' => 'Le fichier contenant le diplôme',
			'photo_identite_file' => 'Le fichier contenant la photo passeport',
			'certificat_medical_file' => 'Le fichier contenant le certificat médical',
			'coupon_file' => 'Le fichier contenant le coupon',
			'type_diplome' => 'Le type de diplôme',

			// Bulletins
			'bulletins_seconde' => 'Les bulletins de seconde',
			'bulletins_seconde.*' => 'Un fichier de bulletin de seconde',
			'bulletins_premiere' => 'Les bulletins de première',
			'bulletins_premiere.*' => 'Un fichier de bulletin de première',
			'bulletins_terminale' => 'Les bulletins de terminale',
			'bulletins_terminale.*' => 'Un fichier de bulletin de terminale',
			'releve_bac1' => 'Les relevés du BAC1',
			'releve_bac1.*' => 'Un fichier de relevé du BAC1',
			'releve_bac2' => 'Les relevés du BAC2',
			'releve_bac2.*' => 'Un fichier de relevé du BAC2',

			// Responsable des frais
			'nom_resp' => 'Le nom du responsable',
			'prenom_resp' => 'Le prénom du responsable',
			'profession_resp' => 'La profession du responsable',
			'employeur_resp' => 'Le nom de l\'employeur du responsable',
			'email_resp' => 'L\'adresse mail du responsable',
			'tel_resp' => 'Le téléphone du responsable',
			'adresse_resp' => 'L\'adresse du responsable',
			'fax_resp' => 'Le fax du responsable',
			'bp_resp' => 'La boîte postale du responsable',

			// Tuteur
			'nom_tuteur' => 'Le nom du tuteur',
			'prenom_tuteur' => 'Le prénom du tuteur',
			'profession_tuteur' => 'La profession du tuteur',
			'employeur_tuteur' => 'Le nom de l\'employeur du tuteur',
			'email_tuteur' => 'L\'adresse mail du tuteur',
			'tel_tuteur' => 'Le téléphone du tuteur',
			'adresse_tuteur' => 'L\'adresse du tuteur',
			'fax_tuteur' => 'Le fax du tuteur',
			'bp_tuteur' => 'La boîte postale du tuteur',

			// Acceptation
			'accept_cgu' => 'La case d\'acceptation des conditions générales',
		];
	}

	public function messages(): array
	{
		return [
			'hobbit.required' => 'Vos centres d\'intérêt sont obligatoires',
			'date_naissance.before' => 'Votre date de naissance doit être une date valide',
		];
	}

	protected function passedValidation()
	{
		$indicatif = '+' . $this->string('indicatif', '228');

		$normalize = function ($value) use ($indicatif) {
			$val = Str::of((string) $value)->replace([' ', '-', '.'], '')->trim();
			if ($val->isEmpty()) return null;
			return $val->startsWith('+') ? (string) $val : ($indicatif . (string) $val);
		};

		$normalized = [
			'tel' => $normalize($this->input('tel')),
			'tel2' => $normalize($this->input('tel2')),
			'tel3' => $normalize($this->input('tel3')),
		];
		$this->merge($normalized);
	}
}
