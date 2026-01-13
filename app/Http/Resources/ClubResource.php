<?php

namespace App\Http\Resources;

use App\Models\Etudiant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClubResource extends JsonResource
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
            "nom" => $this->resource->nom,
            "description" => $this->resource->description,
            'responsables' => Etudiant::all(),
            "responsable" => new UserResource($this->resource->responsable),
            "date_creation" => $this->resource->date_creation,
        ];
    }
}
