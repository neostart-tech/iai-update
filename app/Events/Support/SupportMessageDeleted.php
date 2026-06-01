<?php

namespace App\Events\Support;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SupportMessageDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    
    public $ticketId;
    public $messageId;
    
    public function __construct($ticketId, $messageId)
    {
        $this->ticketId = $ticketId;
        $this->messageId = $messageId;
    }
    
    public function broadcastOn()
    {
        return new PrivateChannel("support.ticket.{$this->ticketId}");
    }
    
    public function broadcastWith()
    {
        return [
            'ticket_id' => $this->ticketId,
            'message_id' => $this->messageId,
        ];
    }
}
