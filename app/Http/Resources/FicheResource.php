<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FicheResource extends JsonResource
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
            "slug" => $this->resource->slug,
            // "annee_scolaire" =>new AnneeScolaireResource($this->resource->anneeScolaire),
            "etudiants" =>  EtudiantRessource::collection($this->whenLoaded('etudiants')),
            "surveillants" => UserResource::collection(
                $this->whenLoaded('surveillants')
            ),
            "submitted" => $this->resource->submitted,
            "processed" => $this->resource->processed,
            // "id" => $this->resource->id,
            // "id" => $this->resource->id,
            // "id" => $this->resource->id,
            // "id" => $this->resource->id,
            // "id" => $this->resource->id,
            // "id" => $this->resource->id,
        ];
    }
}
