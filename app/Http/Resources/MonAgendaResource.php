<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MonAgendaResource extends JsonResource
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
            "title" => $this->resource->titre,
            "start_time" => $this->resource->start_time,
            "end_time" => $this->resource->end_time,
            'description'=> $this->resource->description,
            "alerte" => $this->resource->alerte
        ];
    }
}
