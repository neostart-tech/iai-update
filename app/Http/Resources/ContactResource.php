<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
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
            "message" => $this->resource->message,
            "slug" => $this->resource->slug,
            "status" => $this->resource->status === 1 ? "Lu" : "Non lu",
            "date_reception" => date_format(date_create($this->resource->created_at), 'd F Y'),
        ];
    }
}
