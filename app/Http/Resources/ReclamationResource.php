<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ReclamationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'motif' => $this->motif,
            'statut' => $this->statut,
            'fichier_justificatif' => $this->fichier_justificatif ?
                Storage::url($this->fichier_justificatif) : null,
            'commentaire_admin' => $this->commentaire_admin,
            'nouvelle_note_proposee' => $this->nouvelle_note,
            'date_creation' => $this->created_at->format('d/m/Y H:i'),
            'date_traitement' => $this->traitee_le?->format('d/m/Y H:i'),
            'evaluation' => [
                'id' => $this->evaluation->id,
                'titre' => $this->evaluation->titre,
                'matiere' => $this->evaluation->matiere?->nom
            ],
            'slug' => $this->resource->slug,
            'note_actuelle' => $this->note?->note,
            'etudiant' => [
                'id' => $this->etudiant->id,
                'nom' => $this->etudiant->nom,
                'prenom' => $this->etudiant->prenom,
                'email' => $this->etudiant->email
            ],
            "peut_reclamer" => $this->etudiant->peutReclamer(),

        ];
    }
}
