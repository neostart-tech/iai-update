<?php

namespace App\Http\Resources;

use App\Models\ExamSubmission;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NoteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->resource->id,
            "matiere" => new UvResource($this->resource->matiere),
            "etudiant" => new MiniUserResource($this->resource->etudiant),
            "evaluation" => new EvaluationResource($this->resource->evaluation),
            "anonymat" => $this->resource->anonymat,
            "notation" => $this->resource->note,
            "online_score" => $this->resource->evaluation->is_online ? ExamSubmission::where('evaluation_id', $this->resource->evaluation_id)
                ->where('etudiant_id', $this->resource->etudiant_id)
                ->sum('points_obtenus') : null,
            'reclamation'=> ReclamationResource::collection($this->resource->reclamations)
        ];
    }
}
