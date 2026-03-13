<?php
// app/Http/Resources/SalleResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SalleResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'nom' => $this->nom,
            'type' => $this->type,
            'effectif' => $this->effectif,
            'est_virtuelle' => $this->est_virtuelle,
            'est_physique' => $this->est_physique,
            
            // Champs spécifiques aux salles virtuelles
            'lien_reunion' => $this->when($this->estVirtuelle, $this->lien_reunion),
            'lien_reunion_formate' => $this->when($this->estVirtuelle, $this->lien_reunion_formate),
            'plateforme' => $this->when($this->estVirtuelle, $this->plateforme),
            'plateforme_nom' => $this->when($this->estVirtuelle, $this->plateforme_nom),
            'instructions' => $this->when($this->estVirtuelle, $this->instructions),
            
            // Statistiques
            'programmations_count' => $this->when(
                $this->relationLoaded('emploiDuTemps'),
                $this->emploiDuTemps->count()
            ),
            
            // Dates
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}