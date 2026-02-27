<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class NegociationRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à faire cette requête
     */
    public function authorize(): bool
    {
        return true; // À adapter selon vos permissions
    }

    /**
     * Règles de validation
     */
    public function rules(): array
    {
        return [
            'etudiant_id' => 'required|exists:etudiants,id',
            'frais_scolarite_id' => 'required|exists:frais_scolarites,id',
            'annee_scolaire_id' => 'required|exists:annee_scolaires,id',
            'bourse_etudiant_id' => 'nullable|exists:bourse_etudiants,bourse_id',
            'type_paiement' => 'required|in:tranches_globales,negociation',
            'frequence_paiement' => 'required_if:type_paiement,tranches_globales|in:annuel,trimestriel,bimestriel,mensuel',
            'echeances' => 'required_if:type_paiement,negociation|array|min:1',
            'echeances.*.libelle' => 'required|string|max:255',
            'echeances.*.montant' => 'required|numeric|min:1',
            'echeances.*.date_limite' => 'required|date|after:today',
            'commentaire' => 'nullable|string|max:1000'
        ];
    }

    /**
     * Messages d'erreur personnalisés
     */
    public function messages(): array
    {
        return [
            // Étudiant
            'etudiant_id.required' => 'Vous devez sélectionner un étudiant',
            'etudiant_id.exists' => "L'étudiant sélectionné n'existe pas dans notre système",
            
            // Frais de scolarité
            'frais_scolarite_id.required' => 'Vous devez sélectionner les frais de scolarité',
            'frais_scolarite_id.exists' => 'Les frais de scolarité sélectionnés ne sont pas valides',
            
            // Année scolaire
            'annee_scolaire_id.required' => "Vous devez sélectionner l'année scolaire",
            'annee_scolaire_id.exists' => "L'année scolaire sélectionnée n'existe pas",
            
            // Bourse (optionnel)
            'bourse_etudiant_id.exists' => 'La bourse sélectionnée n\'existe pas',
            
            // Type de paiement
            'type_paiement.required' => 'Vous devez choisir le type de paiement',
            'type_paiement.in' => 'Le type de paiement doit être : "Tranches globales" ou "Négociation"',
            
            // Fréquence de paiement
            'frequence_paiement.required_if' => 'La fréquence de paiement est requise pour les tranches globales',
            'frequence_paiement.in' => 'La fréquence doit être : annuelle, trimestrielle, bimestrielle ou mensuelle',
            
            // Échéances
            'echeances.required_if' => 'Vous devez définir au moins une échéance pour la négociation',
            'echeances.array' => 'Le format des échéances est incorrect',
            'echeances.min' => 'Vous devez ajouter au moins :min échéance',
            
            // Commentaire
            'commentaire.string' => 'Le commentaire doit être du texte',
            'commentaire.max' => 'Le commentaire ne doit pas dépasser :max caractères'
        ];
    }

    /**
     * Messages personnalisés pour les échéances (champs dans tableau)
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $echeances = $this->input('echeances', []);
            
            foreach ($echeances as $index => $echeance) {
                $position = $index + 1;
                
                // Vérification personnalisée du libellé
                if (empty($echeance['libelle'] ?? null)) {
                    $validator->errors()->add(
                        "echeances.{$index}.libelle", 
                        "Le libellé de l'échéance n°{$position} est obligatoire"
                    );
                }
                
                // Vérification personnalisée du montant
                if (!isset($echeance['montant']) || !is_numeric($echeance['montant'])) {
                    $validator->errors()->add(
                        "echeances.{$index}.montant", 
                        "Le montant de l'échéance n°{$position} doit être un nombre valide"
                    );
                } elseif ($echeance['montant'] < 1) {
                    $validator->errors()->add(
                        "echeances.{$index}.montant", 
                        "Le montant de l'échéance n°{$position} doit être d'au moins 1 FCFA"
                    );
                }
                
                // Vérification personnalisée de la date
                if (empty($echeance['date_limite'] ?? null)) {
                    $validator->errors()->add(
                        "echeances.{$index}.date_limite", 
                        "La date limite de l'échéance n°{$position} est obligatoire"
                    );
                } elseif (strtotime($echeance['date_limite']) <= strtotime('today')) {
                    $validator->errors()->add(
                        "echeances.{$index}.date_limite", 
                        "La date limite de l'échéance n°{$position} doit être dans le futur"
                    );
                }
            }
        });
    }

    /**
     * Personnalisation de la réponse en cas d'échec de validation
     */
    protected function failedValidation(Validator $validator)
    {
        // Si c'est une requête AJAX/API, retourner du JSON
        if ($this->expectsJson()) {
            throw new HttpResponseException(
                response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422)
            );
        }

        // Sinon, redirection avec les erreurs en session
        parent::failedValidation($validator);
    }
}