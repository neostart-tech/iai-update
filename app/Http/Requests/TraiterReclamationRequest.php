<?php
// app/Http/Requests/TraiterReclamationRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TraiterReclamationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
        // $user = auth()->user();
        // return $user && ($user->isAdmin() || $user->isResponsablePedagogique());
    }

    public function rules(): array
    {
        return [
            'statut' => 'required|in:approuvee,rejetee',
            'commentaire_admin' => 'required_if:statut,rejetee|nullable|string|max:1000',
            'nouvelle_note' => 'required_if:statut,approuvee|nullable|numeric|min:0|max:20'
        ];
    }
}