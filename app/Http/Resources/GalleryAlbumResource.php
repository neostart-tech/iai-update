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
            "name" => $this->resource->name,
            "slug" => $this->resource->slug,
            "description" => $this->resource->description,
            "cover_path" => $this->resource->cover_path,
            "cover_url" => $this->resource->cover_path ? asset(\Illuminate\Support\Facades\Storage::url($this->resource->cover_path)) : null,
            "is_published" => $this->resource->is_published,
            "published_at" => $this->resource->published_at,
            "photos_count" => $this->resource->photos_count,
            "created_at" => $this->resource->created_at,
        ];
    }
}
