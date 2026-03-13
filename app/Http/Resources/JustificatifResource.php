<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JustificatifResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'type_libelle' => $this->getTypeLibelle(),
            'fichier_path' => $this->fichier_path,
            'fichier_url' => asset('storage/' . $this->fichier_path),
            'description' => $this->description,
            'statut' => $this->statut,
            'est_valide' => $this->estValide(),
            'date_debut_validite' => $this->date_debut_validite?->format('d/m/Y'),
            'date_fin_validite' => $this->date_fin_validite?->format('d/m/Y'),
            'est_expire' => $this->estExpire(),
            'valide_par' => new UserResource($this->whenLoaded('validateur')),
            'valide_le' => $this->valide_le?->format('d/m/Y H:i'),
            'created_at' => $this->created_at?->format('d/m/Y H:i'),
        ];
    }

    private function getTypeLibelle(): string
    {
        $libelles = [
            'certificat_medical' => 'Certificat médical',
            'mot_parental' => 'Mot parental',
            'convocation' => 'Convocation',
            'evenement_familial' => 'Événement familial',
            'autre' => 'Autre'
        ];
        
        return $libelles[$this->type] ?? $this->type;
    }
}