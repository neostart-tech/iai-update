<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AlbumResource extends JsonResource
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
            'lettre' => $this->lettre,
            'naissance' => $this->naissance,
            'diplome' => $this->diplome,
            'nationalite' => $this->nationalite,
            'photo' => $this->photo,
            'type_diplome' => $this->type_diplome,
            'certificat_medical' => $this->certificat_medical,
            'coupon' => $this->coupon,
            'cv' => $this->cv,
            'owner_id' => $this->owner_id,
            'owner_type' => $this->owner_type,
            'bulletins_lycee_paths' => $this->bulletins_lycee_paths,
            'releve_bac1_path' => $this->releve_bac1_path,
            'releve_bac2_path' => $this->releve_bac2_path,

        ];
    }
}
