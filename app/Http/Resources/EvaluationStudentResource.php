<?php

namespace App\Http\Resources;

use App\Http\Resources\Admin\EmploiDuTempsResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EvaluationStudentResource extends JsonResource
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

            'date' => $this->date?->translatedFormat('d F Y'),
            'heure_debut' => $this->debut?->translatedFormat('H:i'),
            'heure_fin' => $this->fin?->translatedFormat('H:i'),

            'is_online' => $this->is_online,

            'salle' => new SalleResource($this->resource->salle),

            'group' => new GroupeResource($this->resource->group),

            'submissions_count' => $this->submissions?->count(),

            'emploi_du_temps' =>new EmploiDuTempsResource($this->resource->emploiDutemp)
            // 'submissions'=>new ,
        ];
    }
}
