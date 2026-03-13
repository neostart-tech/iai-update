<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdvertiserResource extends JsonResource
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
            "email" => $this->resource->email,
            "tel" => $this->resource->tel,
            "details" => $this->resource->details,
            "site" => $this->resource->site,
            "ville" => $this->resource->ville,
            "slug" => $this->resource->slug,
            "logo_url" => $this->when($this->resource->logo, function () {
                return $this->resource->FileFulllPath();
            }), 
        ];
    }
}
