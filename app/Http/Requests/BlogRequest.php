<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BlogRequest extends FormRequest
{
	public function authorize(): bool
	{
		return true;
	}

	public function rules(): array
	{
		$rules = [
			'title' => ['required', 'string', 'max:255'],
			'author_name' => ['nullable', 'string', 'max:255'],
			'content' => ['required', 'string'],
			'status' => ['nullable', 'string', 'in:published,draft'],
			'publication_date' => ['nullable', 'date'],
		];

		// Si c'est une création (POST) l'image est requise
		if ($this->isMethod('post')) {
			$rules['image'] = ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'];
		} 
		// Si c'est une mise à jour (PUT/PATCH) l'image est optionnelle
		else if ($this->isMethod('put') || $this->isMethod('patch')) {
			$rules['image'] = ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'];
		}

		return $rules;
	}

	public function messages(): array
	{
		return [
			'title.required' => 'Le titre est obligatoire',
			'title.max' => 'Le titre ne doit pas dépasser 255 caractères',
			'content.required' => 'Le contenu est obligatoire',
			'image.required' => 'L\'image est obligatoire',
			'image.image' => 'Le fichier doit être une image',
			'image.mimes' => 'L\'image doit être au format: jpeg, png, jpg, gif, svg',
			'image.max' => 'L\'image ne doit pas dépasser 2Mo',
		];
	}

	public function attributes(): array
	{
		return [
			'title' => 'Le titre de la publication',
			'author_name' => "L'auteur de la publication",
			'image' => 'L\'image de la publication',
			'content' => 'Le contenu de la publication',
		];
	}
}