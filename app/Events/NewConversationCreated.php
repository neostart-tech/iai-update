<?php
// app/Events/NewConversationCreated.php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewConversationCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $conversationId;  // ← Changé : seulement l'ID
    public $participantId;

    public function __construct($conversationId, $participantId)  // ← Changé : reçoit l'ID au lieu de l'objet
    {
        $this->conversationId = $conversationId;  // ← Stocke seulement l'ID
        $this->participantId = $participantId;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('user.' . $this->participantId);
    }

    public function broadcastAs()
    {
        return 'conversation.created';
    }

    public function broadcastWith()
    {
        return [
            'conversation_id' => $this->conversationId  // ← Diffuse seulement l'ID
        ];
    }
}