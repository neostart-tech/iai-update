<?php

namespace App\Events;

use App\Models\Conversation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewConversationCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $conversation;
    public $participantId;

    public function __construct(Conversation $conversation, $participantId)
    {
        $this->conversation = $conversation->load('participants');
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
            'conversation' => $this->conversation
        ];
    }
}