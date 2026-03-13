<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PresenceCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => PresenceResource::collection($this->collection),
            'meta' => [
                'total' => $this->collection->count(),
                'statistiques' => [
                    'presents' => $this->collection->whereIn('statut', ['present'])->count(),
                    'absents' => $this->collection->whereIn('statut', ['absent', 'absent_justifie'])->count(),
                    'retards' => $this->collection->whereIn('statut', ['retard', 'retard_justifie'])->count(),
                    'justifies' => $this->collection->whereIn('statut', ['absent_justifie', 'retard_justifie'])->count(),
                ],
                'taux_presence' => $this->collection->count() > 0 
                    ? round(($this->collection->where('statut', 'present')->count() / $this->collection->count()) * 100, 2)
                    : 0
            ]
        ];
    }
}