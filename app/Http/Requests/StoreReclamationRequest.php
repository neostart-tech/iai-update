<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Note;
use App\Models\Reclamation;

class StoreReclamationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->guard('etudiants')->check();
    }

    public function rules(): array
    {
        $etudiantId = auth()->guard('etudiants')->id();

        return [
            'evaluation_id' => [
                'required',
                'exists:evaluations,id',
                function ($attribute, $value, $fail) use ($etudiantId) {
                    // Vérifier que l'étudiant a une note
                    $note = Note::where('etudiant_id', $etudiantId)
                        ->where('evaluation_id', $value)
                        ->first();

                    if (!$note) {
                        $fail('Vous n\'avez pas de note pour cette évaluation.');
                    }
                },
                function ($attribute, $value, $fail) use ($etudiantId) {
                    // Vérifier pas de réclamation en cours
                    $exists = Reclamation::where('etudiant_id', $etudiantId)
                        ->where('evaluation_id', $value)
                        ->where('statut', 'en_attente')
                        ->exists();

                    if ($exists) {
                        $fail('Vous avez déjà une réclamation en cours pour cette évaluation.');
                    }
                }
            ],
            'motif' => 'required|string|min:10|max:2000',
            'fichier_justificatif' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'nouvelle_note' => 'nullable|numeric|min:0|max:20'
        ];
    }
}