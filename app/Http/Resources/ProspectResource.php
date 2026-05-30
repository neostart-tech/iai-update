<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProspectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'email' => $this->email,
            'tel' => $this->tel,
            'formation_visee' => $this->formation_visee,
            'origine' => $this->origine,
            'status' => $this->status,
            'slug' => $this->slug,
            'created_at' => $this->created_at,
            'date_formatted' => $this->created_at ? $this->created_at->format('d/m/Y H:i') : null,
        ];
    }
}
