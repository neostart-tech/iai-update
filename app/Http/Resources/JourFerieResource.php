<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class JourFerieResource extends JsonResource
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
            'titre' => $this->titre,
            'slug' => $this->slug,
            'date' => $this->date->format('Y-m-d'),
            'date_formatee' => Carbon::parse($this->date)->translatedFormat('d F Y'),
            'est_recurrent' => $this->est_recurrent,
            'description' => $this->description,
            'annee_scolaire_id' => $this->annee_scolaire_id,
            'annee_scolaire_nom' => $this->anneeScolaire?->nom,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
