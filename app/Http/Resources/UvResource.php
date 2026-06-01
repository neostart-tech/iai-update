<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UvResource extends JsonResource
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
            "code" => $this->resource->code,
            "cm" => $this->resource->cm,
            "td" => $this->resource->td,
            "tp" => $this->resource->tp,
            "ec" => $this->resource->ec,
            'volume_horaire'=> $this->resource->volume_horaire,
            "slug" => $this->resource->slug,
            "coefficient" => $this->resource->coefficient,
            "ue" => new UeResource($this->resource->ue),
            "user"=> UserResource::collection($this->resource->user) ?? null,
            'filiere'=>new FiliereResource($this->resource->filiere) ?? null,
            'periode'=>new PeriodeResource($this->resource->periode) ?? null,
            'niveau' => new NiveauResource($this->resource->niveau) ?? null,
        ];
    }
}
