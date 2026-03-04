<?php
// app/Events/MessagesRead.php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessagesRead implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $conversationId;
    public $userId;
    public $userName;
    public $messageIds;
    public $readAt;

    public function __construct($conversationId, $userId, $userName, $messageIds)
    {
        $this->conversationId = $conversationId;
        $this->userId = $userId;
        $this->userName = $userName;
        $this->messageIds = $messageIds;
        $this->readAt = now();
    }

    public function broadcastOn()
    {
        return new PrivateChannel('conversation.' . $this->conversationId);
    }

    public function broadcastAs()
    {
        return 'messages.read';
    }

    public function broadcastWith()
    {
        return [
            'conversation_id' => $this->conversationId,
            'user_id' => $this->userId,
            'user_name' => $this->userName,
            'message_ids' => $this->messageIds,
            'read_at' => $this->readAt
        ];
    }
}