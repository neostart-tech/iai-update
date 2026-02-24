<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FraisScolariteResource extends JsonResource
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
            "annee_scolaire" => new AnneeScolaireResource($this->resource->anneeScolaire),
            "niveau" => new NiveauResource($this->resource->niveau),
            "montant" => $this->resource->montant,
            "genre" => $this->resource->genre,
            "description" => $this->resource->description,
            "filiere"=>new FiliereResource($this->resource->filiere),
            // 'tranches' => TranchePaiementResource::collection(
            //     $this->whenLoaded('tranchepaiement')
            // ),
        ];
    }
}
