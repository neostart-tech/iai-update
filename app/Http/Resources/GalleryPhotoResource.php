<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GalleryPhotoResource extends JsonResource
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
            "title" => $this->resource->title,
            "caption" => $this->resource->caption,
            "file_path" => $this->resource->file_path,
            "file_url" => $this->resource->file_path ? asset(\Illuminate\Support\Facades\Storage::url($this->resource->file_path)) : null,
            "alt_text" => $this->resource->alt_text,
            "position" => $this->resource->position,
            "is_published" => $this->resource->is_published,
            "published_at" => $this->resource->published_at,
            "taken_at" => $this->resource->taken_at,
            "created_by" => $this->resource->created_by,
            "created_at" => $this->resource->created_at,
            "gallery_album_id" => $this->resource->gallery_album_id,
        ];
    }
}
