<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UrgentInfoResource extends JsonResource
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
            'title' => $this->resource->title,
            'summary' => $this->resource->summary,
            'file_url' => $this->resource->file_url,
            'file_path' => $this->resource->getFullPath(),
            'image' => $this->resource->image ? asset(\Illuminate\Support\Facades\Storage::url($this->resource->image)) : null,
            'attachments' => collect($this->resource->attachments)->map(function($attachment) {
                return [
                    'name' => $attachment['name'],
                    'size' => $attachment['size'],
                    'url' => asset(\Illuminate\Support\Facades\Storage::url($attachment['path'])),
                ];
            }),
            'target_audience' => $this->resource->target_audience,
            'target_group' => $this->resource->group ? [
                'id' => $this->resource->group->id,
                'nom' => $this->resource->group->nom,
            ] : null,
            'is_published' => $this->resource->is_published,
            'published_at' => $this->resource->published_at,
            'published_at_detail' => $this->resource->published_at ? date_format(date_create($this->resource->published_at), 'd F Y') : null,
            'created_by' => $this->resource->created_by,
            'created_at' => $this->resource->created_at,
        ];
    }
}
