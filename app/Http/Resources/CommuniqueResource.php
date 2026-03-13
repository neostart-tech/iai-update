<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CommuniqueResource extends JsonResource
{
    public function toArray($request)
    {
        $data = $this->data;
        
        return [
            'id' => $this->id,
            'titre' => $data['titre'] ?? null,
            'contenu' => $data['contenu'] ?? null,
            'date_publication' => $data['date_publication'] ?? null,
            'date_expiration' => $data['date_expiration'] ?? null,
            'piece_jointe' => isset($data['piece_jointe']) 
                ? url('/storage/' . $data['piece_jointe']) 
                : null,
            'piece_jointe_nom' => $data['piece_jointe_nom'] ?? null,
            'expediteur' => $data['expediteur'] ?? null,
            'contexte_ciblage' => $data['contexte_ciblage'] ?? null,
            'lu' => !is_null($this->read_at),
            'lu_le' => $this->read_at?->format('Y-m-d H:i:s'),
            'recu_le' => $this->created_at->format('Y-m-d H:i:s'),
            'icon' => $data['icon'] ?? null,
            'level' => $data['level'] ?? 'info',
            'type' => $data['type'] ?? 'communique',
        ];
    }
}