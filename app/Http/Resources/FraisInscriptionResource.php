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
            "niveau_id" => $this->resource->niveau_id,
            "niveau" => $this->resource->niveau ? [
                "id" => $this->resource->niveau->id,
                "nom" => $this->resource->niveau->nom ?? $this->resource->niveau->libelle,
            ] : null,
            "filiere_id" => $this->resource->filiere_id,
            "filiere" => $this->resource->filiere ? [
                "id" => $this->resource->filiere->id,
                "nom" => $this->resource->filiere->nom,
            ] : null,
            "has_payments" => $this->resource->has_payments,
            "created_at" => $this->resource->created_at,
        ];
    }
}
