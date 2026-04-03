<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FraisInscriptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->resource->id,
            "montant" => $this->resource->montant,
            "active" => (bool) $this->resource->active,
            "slug" => $this->resource->slug,
            "annee_scolaire_id" => $this->resource->annee_scolaire_id,
            "annee_scolaire" => $this->resource->anneeScolaire ? [
                "id" => $this->resource->anneeScolaire->id,
                "nom" => $this->resource->anneeScolaire->nom,
            ] : null,
            "created_at" => $this->resource->created_at,
        ];
    }
}
