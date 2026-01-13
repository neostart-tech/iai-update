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
            "code" => $this->resource->code,
            "slug" => $this->resource->slug,
            "active" => $this->resource->active,
        ];
    }
}
