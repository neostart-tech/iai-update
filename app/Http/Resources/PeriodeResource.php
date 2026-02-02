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
            "debut" => date_format(date_create($this->resource->debut),'d F Y') ?? '--',
            "fin" => date_format(date_create($this->resource->fin),'d F Y') ?? '--',
            "slug" => $this->resource->slug,
            "status" => $this->resource->is_active,


        ];
    }
}
