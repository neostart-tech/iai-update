<?php
// app/Events/MessageUpdated.php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message->load('sender');
    }

    public function broadcastOn()
    {
        return new PrivateChannel('conversation.' . $this->message->conversation_id);
    }

    public function broadcastAs()
    {
        return 'message.updated';
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->message->id,
            'body' => $this->message->body,
            'is_edited' => $this->message->is_edited,
            'edited_at' => $this->message->edited_at,
            'conversation_id' => $this->message->conversation_id,
            'sender' => [
                'id' => $this->message->sender->id,
                'nom' => $this->message->sender->nom,
                'prenom' => $this->message->sender->prenom
            ]
        ];
    }
}