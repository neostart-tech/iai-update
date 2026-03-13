<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaiementGlobalRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'etudiant_id' => 'required|exists:etudiants,id',
            'montant' => 'required|numeric|min:1',
            'mode_paiement' => 'required|string|in:especes,banque,semoa,caisse,carte,virement,cheque',
            'reference' => 'nullable|string|max:255',
            'date_paiement' => 'nullable|date',
            'justificatif' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048'
        ];
    }

    public function messages()
    {
        return [
            'etudiant_id.required' => 'Veuillez sélectionner un étudiant',
            'etudiant_id.exists' => 'L\'étudiant sélectionné n\'existe pas',
            'montant.required' => 'Le montant est obligatoire',
            'montant.numeric' => 'Le montant doit être un nombre',
            'montant.min' => 'Le montant doit être supérieur à 0',
            'mode_paiement.required' => 'Le mode de paiement est obligatoire',
            'mode_paiement.in' => 'Le mode de paiement sélectionné n\'est pas valide'
        ];
    }
}