<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EvaluationResource extends JsonResource
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
            'slug' => $this->slug,
            'group' => new GroupeResource($this->group),
            'niveau' => new NiveauResource($this->resource->niveau),
            'semestre' => new PeriodeResource($this->resource->periode),
            'matiere' => new UvResource($this->resource->matiere),
            'fiche' => new FicheResource($this->resource->fiche),
            'salle' => new SalleResource($this->resource->salle),
            'published' => $this->resource->published,
            'type' => $this->resource->type,
            'date' => $this->resource->date,
            'heure_debut' => date_format(date_create($this->resource->debut), 'h:i:s'),
            'heure_fin' => date_format(date_create($this->resource->fin), 'h:i:s'),
            'debut' => $this->resource->debut,
            'duration_minutes' => $this->resource->duration_minutes,
            'fin' => $this->resource->fin,
            'has_anonymat' => $this->resource->has_anonymat,
            'correction_end_date' => $this->resource->correction_end_date,
            'correction_submission_date' => $this->resource->correction_submission_date,
            'is_online' => $this->resource->is_online,
            'status' => $this->computeStatus(),



        ];
    }


    private function computeStatus(): string
    {
        if (! $this->published) {
            return 'En attente';
        }

        if (! $this->debut || ! $this->fin) {
            return 'En attente';
        }

        $now = Carbon::now();

        $start = Carbon::parse($this->debut);
        $end   = Carbon::parse($this->fin);

        if ($now->lt($start)) {
            return 'En attente';
        }

        if ($now->between($start, $end)) {
            return 'En cours';
        }

        return 'Terminée';
    }
}
