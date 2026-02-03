<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'image' => $this->resource->ImagePath(),
            'matricule' => $this->resource->matricule,
            'grade_id' => $this->resource->grade_id,
            'biographie' => $this->resource->biographie,
            'annee_admission' => $this->resource->annee_admission,
            'slug' => $this->resource->slug,
            'tel' => $this->resource->tel,
            'nom_jeune_fille' => $this->resource->nom_jeune_fille,
            'date_naissance' => $this->resource->date_naissance,
            'lieu_naissance' => $this->resource->lieu_naissance,
            'nationalite' => $this->resource->nationalite,
            'group' => new GroupeResource($this->resource->group),
            'supervisor_type' => $this->resource->supervisor_type,
            'supervisor_notes' => $this->resource->supervisor_notes,
            'roles'=>RoleResource::collection($this->resource->roles),
        ];
    }
}
