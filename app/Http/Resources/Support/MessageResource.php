<?php

namespace App\Http\Resources\Support;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ticket_id' => $this->ticket_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'message' => $this->message,
            'type' => $this->type,
            'type_label' => $this->getTypeLabel(),
            'is_read' => $this->is_read,
            'read_at' => $this->read_at,
            'attachments' => AttachmentResource::collection($this->whenLoaded('attachments')),
            'created_at' => $this->created_at,
            'created_at_formatted' => $this->created_at->diffForHumans(),
        ];
    }
    
    private function getTypeLabel(): string
    {
        return match($this->type) {
            'user' => 'Utilisateur',
            'informaticien' => 'Informaticien',
            'system' => 'Système',
            default => $this->type,
        };
    }
}