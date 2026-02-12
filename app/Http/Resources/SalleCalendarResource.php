<?php

namespace App\Http\Resources;

use App\Enums\TypeProgrammeEnum;
use App\Models\Group;
use App\Models\UniteValeur;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalleCalendarResource extends JsonResource
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
            'nom' => $this->nom,
            'uvs' => UniteValeur::all(),
            'types' => TypeProgrammeEnum::cases(),
            'groups' => Group::with('niveau')->orderBy('id')->get(),
            'teachers' => User::enseignants()->get(),
        ];
    }
}
