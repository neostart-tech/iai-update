<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaiementResource extends JsonResource
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
            "etudiant" => new MiniUserResource($this->resource->etudiant),
            "montant" => $this->montant,
            "mode_paiement" => $this->mode_paiement,
            "reference" => $this->reference,
            "justificatif" => $this->justificatif,
            "status" => $this->status,
            "recu" => $this->recu,
            "annule" => $this->annule,
            "annule_par" => $this->annule_par,
            "date_paiement" => $this->resource->date_paiement

        ];
    }
}
