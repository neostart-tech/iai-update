<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PeriodeResource extends JsonResource
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
            "nom" => $this->resource->nom,
            "description" => $this->resource->description,
            "debut" => $this->resource->debut,
            "fin" => $this->resource->fin,
            "slug" => $this->resource->slug,
            "status" => $this->resource->is_active,


        ];
    }
}
