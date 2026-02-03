<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EtudiantRessource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'nom' => $this->resource->nom,
            'prenom' => $this->resource->prenom,
            'email' => $this->resource->email,
            'genre' => $this->resource->genre,
            'image' =>$this->resource->image ? $this->resource->ImagePath() : "",
            'matricule' => $this->resource->matricule,
            'biographie' => $this->resource->biographie,
            'annee_admission' => $this->resource->annee_admission,
            'slug' => $this->resource->slug,
            'tel' => $this->resource->tel,
            'nom_jeune_fille' => $this->resource->nom_jeune_fille,
            'date_naissance' => $this->resource->date_naissance,
            'lieu_naissance' => $this->resource->lieu_naissance,
            'nationalite' => $this->resource->nationalite,    
        ];
    }
}
