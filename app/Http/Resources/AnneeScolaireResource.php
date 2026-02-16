<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnneeScolaireResource extends JsonResource
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
            "nom" => $this->resource->nom,
            "date_debut" => $this->resource->date_debut,
            "slug" => $this->resource->slug,
            "date_fin" => $this->resource->date_fin,
            "date_debut_detail" => date_format(date_create($this->resource->date_debut), "d F Y"),
            "date_fin_detail" => date_format(date_create($this->resource->date_fin), "d F Y"),
            "code" => $this->resource->code,
            "active" => $this->resource->active,
        ];
    }
}
