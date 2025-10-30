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
		return [
			'title' => ['required', 'string', 'max:255'],
			'author_name' => ['nullable', 'string', 'max:255'],
			'image' => [($this->routeIs('admin.blogs.store') ? 'required' : 'nullable'), 'image'],
			'content' => ['required', 'string'],
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
