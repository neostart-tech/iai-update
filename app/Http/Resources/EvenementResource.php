<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EvenementResource extends JsonResource
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
            "details" => $this->resource->details,
            "slug" => $this->resource->slug,
            "date_debut" => $this->resource->start_date,
            "date_fin" => $this->resource->end_date,
            "date_debut_detail" => date_format(date_create($this->resource->start_date),'d F Y'),
            "date_fin_detail" => date_format(date_create($this->resource->end_date),'d F Y'),
        ];
    }
}
