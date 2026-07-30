<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogResource extends JsonResource
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
            "auteur" => $this->resource->auteur,
             "status" => $this->resource->status,
            "image" => $this->resource->getFullPath(),
            "content" => $this->resource->content,
            "date_publication" => date_format(date_create($this->resource->publication_date), 'Y-m-d H:i:s'),
            "date_publication_detail" => date_format(date_create($this->resource->publication_date), 'd F Y'),
            "slug" => $this->resource->slug,
        ];
    }
}
