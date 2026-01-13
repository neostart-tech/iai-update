<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UeResource extends JsonResource
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
            'nom' => $this->resource->nom,
            'code' => $this->resource->code,
            'slug' => $this->resource->slug,
            'credit' => $this->resource->credit,
            'periode'=>new PeriodeResource($this->resource->periode)
        ];
    }
}
