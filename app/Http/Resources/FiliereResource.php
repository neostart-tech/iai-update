<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FiliereResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'nom' => $this->resource->nom,
            'code' => $this->resource->code,
            'slug' => $this->resource->slug,
            'description' => $this->resource->description,
            'image' => $this->resource->pathImage(),
            'etudiant_counts'=>$this->etudiants_count,
            // 'annee_scolaire' => new AnneeScolaireResource($this->resource->annee_scolaire_id),
        ];
    }
}
