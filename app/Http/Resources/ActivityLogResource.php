<?php

namespace App\Http\Resources;

use App\Support\ActivityDescriber;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $causer = $this->causer;

        return [
            'id' => $this->id,
            'action' => ActivityDescriber::describe($this->resource),
            'log_name' => $this->log_name,
            'description' => $this->description,
            'event' => $this->event,
            'subject_type' => $this->subject_type,
            'subject_id' => $this->subject_id,
            'causer' => $causer ? [
                'id' => $causer->id,
                'type' => $this->causer_type,
                'nom' => $this->causerDisplayName($causer),
            ] : null,
            'properties' => $this->properties,
            'created_at' => $this->created_at,
        ];
    }

    private function causerDisplayName($causer): string
    {
        if (isset($causer->nom) || isset($causer->prenom)) {
            return trim(($causer->nom ?? '') . ' ' . ($causer->prenom ?? '')) ?: ('#' . $causer->id);
        }

        return $causer->email ?? ('#' . $causer->id);
    }
}
