<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GalleryAlbumResource extends JsonResource
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
            "description" => $this->resource->description,
            "cover_path" => $this->resource->cover_path,
            "is_published" => $this->resource->is_published,
            "published_at" => $this->resource->published_at,
        ];
    }
}
