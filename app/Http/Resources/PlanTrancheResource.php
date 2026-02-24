<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanTrancheResource extends JsonResource
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
            'ordre' => $this->ordre,
            'montant' => $this->montant,
            'pourcentage' => $this->pourcentage,
            'mois_apres_debut' => $this->mois_apres_debut,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
