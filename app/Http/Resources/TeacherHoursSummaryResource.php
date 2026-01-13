<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeacherHoursSummaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'enseignant' => [
                'id' => $this['user']->id,
                'nom' => $this['user']->nom,
                'prenom' => $this['user']->prenom,
                'email' => $this['user']->email,
            ],
            'heures' => $this['hours'],
            'secondes' => $this['seconds'],
        ];
    }
}
