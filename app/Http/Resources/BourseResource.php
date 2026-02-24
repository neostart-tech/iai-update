<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BourseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "slug" => $this->slug,
            "nom" => $this->nom,
            "type" => $this->type,
            "valeur" => $this->valeur,
            "description" => $this->description,
            "etudiants" => EtudiantRessource::collection($this->whenLoaded('etudiants')),
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
        ];
    }
}