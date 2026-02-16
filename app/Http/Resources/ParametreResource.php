<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ParametreResource extends JsonResource
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
            'key' => $this->resource->key,
            'value' => $this->resource->value,
            'valueKey' => $this->resource->valueKey,
            'options' => $this->resource->options,
            'type' => $this->resource->type,
            // 'key' => $this->resource->key,
        ];
    }
}
