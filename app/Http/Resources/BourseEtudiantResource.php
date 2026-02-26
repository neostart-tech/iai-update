<?php
// app/Http/Resources/BourseEtudiantResource.php

namespace App\Http\Resources;

use App\Models\AnneeScolaire;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BourseEtudiantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->pivot->id, 
            'bourse_id' => $this->id,
            'etudiant_id' => $this->pivot->etudiant_id,
            'slug' => $this->pivot->slug,
            'annee_scolaire_id' => $this->pivot->annee_scolaire_id,
            'date_attribution' => $this->pivot->created_at,
            
            'bourse' => new BourseResource($this),
            
            // Informations supplémentaires formatées
            'libelle' => $this->nom,
            'type' => $this->type,
            'valeur' => $this->valeur,
            'valeur_formatted' => $this->type === 'pourcentage' 
                ? $this->valeur . '%' 
                : number_format($this->valeur, 0, ',', ' ') . ' FCFA',
            'description' => $this->description,
            
            // Année scolaire (si vous voulez l'inclure)
            'annee_scolaire' => $this->when($this->pivot->annee_scolaire_id, function() {
                return [
                    'id' => $this->pivot->annee_scolaire_id,
                    'libelle' => AnneeScolaire::courante()->nom
                ];
            }),
        ];
    }
}