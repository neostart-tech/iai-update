<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EtudiantAnnouncementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->getAttribute('id'),
            "title" => $this->getAttribute('title'),
            "description" => $this->getAttribute('description'),
            "type_annonce" => $this->getAttribute('type_annonce'),
            "type_contrat" => $this->getAttribute('type_contrat'),
            "advertiser" => new AdvertiserResource($this->advertiser),
            "applied" => $this->announcementEtudiants()->where('etudiant_id', $request->user()->id)->exists(),
            
        ];
    }
}
