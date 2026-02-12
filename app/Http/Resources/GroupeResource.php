<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->resource->id ?? null,
            "nom" => $this->resource->nom ?? null,
            "slug" => $this->resource->slug ?? null,
            "niveau" => new NiveauResource($this->resource->niveau) ?? null,
            "filieres" => FiliereResource::collection($this->resource->filieres) ?? null,
            "inscrits"=>$this->resource->etudiants_count,
            
        ];
    }
}
