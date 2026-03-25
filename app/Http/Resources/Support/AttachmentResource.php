<?php

namespace App\Http\Resources\Support;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttachmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'filename' => $this->filename,
            'original_name' => $this->original_name,
            'url' => $this->url,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'size_formatted' => $this->formatSize($this->size),
            'created_at' => $this->created_at,
        ];
    }
    
    private function formatSize($bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}