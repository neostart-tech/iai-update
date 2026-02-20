<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Note;
use App\Models\Reclamation;

class StoreReclamationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
        // return auth()->guard('etudiants')->check();
    }

    public function rules(): array
    {
        return [
            'motif' => 'required|string|min:10|max:2000',
            'fichier_justificatif' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'nouvelle_note' => 'required|numeric|min:0|max:20'
        ];
    }

    public function messages()

    {
        return [
            "evaluation_id" => "L'évaluation",
            "fichier_justificatif.required"=>"Le fichier justificatif est requis",
            "nouvelle_note" => "La nouvelle note"
        ];
    }
}
