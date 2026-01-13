<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TranchePaiementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            'annee_scolaire' => new AnneeScolaireResource($this->annee),
            "frais" => new FraisScolariteResource($this->frais),
            "libelle" => $this->libelle,
            "montant" => $this->montant,
            "date_limite" => $this->date_limite,
            "date_limite_complet" => date_format(date_create($this->date_limite), "d F Y"),
        ];
    }
}
