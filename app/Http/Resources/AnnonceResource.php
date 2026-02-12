<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnnonceResource extends JsonResource
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
            "slug" => $this->resource->slug,
            "titre" => $this->resource->title,
            "type_annonce" => $this->resource->type_annonce,
            "type_contrat" => $this->resource->type_contrat,
            "content" => $this->resource->content,
            "slug" => $this->resource->slug,
            "ville" => $this->resource->ville,
            "date_publication" => $this->resource->date_publication,
            "status" => $this->resource->status,
            'filepath' => $this->resource->file_path ? asset($this->resource->cheminFichier()) : null,
            "title" => $this->resource->title,
            'duration' => $this->resource->duration,
            'advertiser' => new AdvertiserResource($this->resource->advertiser),

        ];
    }
}
