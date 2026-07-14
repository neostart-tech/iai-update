<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Propaganistas\LaravelPhone\Rules\Phone;

class ProspectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc,dns', 'max:255'],
            'tel' => ['required', 'string', 'max:30', (new Phone)->international()],
            'formation_visee' => ['nullable', 'string', 'max:255'],
            'origine' => ['nullable', 'string', 'max:255']
        ];
    }

    public function messages(): array
    {
        return [
            'email.email' => 'L\'adresse email est invalide ou le domaine n\'existe pas.',
            'tel.phone' => 'Le numéro de téléphone est invalide. Utilisez le format international (ex: +22967123456).',
        ];
    }
}
