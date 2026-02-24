<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanPaiementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'slug' => $this->slug,
            'type' => $this->type,
            'nombre_tranches' => $this->nombre_tranches,
            'est_personnalise' => $this->est_personnalise,
            'actif' => $this->actif,
            'tranches' => PlanTrancheResource::collection($this->tranches),
        ];
    }
}
