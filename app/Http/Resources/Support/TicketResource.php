<?php

namespace App\Http\Resources\Support;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'status_color' => $this->getStatusColor(),
            'priority' => $this->priority,
            'priority_label' => $this->getPriorityLabel(),
            'priority_color' => $this->getPriorityColor(),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'ticketable_type' => $this->ticketable_type,
            'ticketable' => $this->whenLoaded('ticketable', function() {
                return [
                    'id' => $this->ticketable->id,
                    'name' => $this->ticketable->nom . ' ' . $this->ticketable->prenom,
                    'email' => $this->ticketable->email ?? null,
                    'type' => class_basename($this->ticketable_type),
                ];
            }),
            'assigned_agent' => new UserResource($this->whenLoaded('assignedAgent')),
            'messages' => MessageResource::collection($this->whenLoaded('messages')),
            'messages_count' => $this->whenCounted('messages'),
            'rating' => $this->rating,
            'feedback' => $this->feedback,
            'resolved_at' => $this->resolved_at,
            'closed_at' => $this->closed_at,
            'created_at' => $this->created_at,
            'created_at_formatted' => $this->created_at->diffForHumans(),
            'updated_at' => $this->updated_at,
        ];
    }
    
    private function getStatusLabel(): string
    {
        return match($this->status) {
            'open' => 'Ouvert',
            'in_progress' => 'En cours',
            'waiting' => 'En attente',
            'resolved' => 'Résolu',
            'closed' => 'Fermé',
            default => $this->status,
        };
    }
    
    private function getStatusColor(): string
    {
        return match($this->status) {
            'open' => 'blue',
            'in_progress' => 'yellow',
            'waiting' => 'orange',
            'resolved' => 'green',
            'closed' => 'gray',
            default => 'gray',
        };
    }
    
    private function getPriorityLabel(): string
    {
        return match($this->priority) {
            'low' => 'Basse',
            'medium' => 'Moyenne',
            'high' => 'Haute',
            'critical' => 'Critique',
            default => $this->priority,
        };
    }
    
    private function getPriorityColor(): string
    {
        return match($this->priority) {
            'low' => 'gray',
            'medium' => 'blue',
            'high' => 'orange',
            'critical' => 'red',
            default => 'gray',
        };
    }
}