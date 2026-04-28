<?php

namespace App\Events\Support;

use App\Models\Support\SupportTicket;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Http\Resources\Support\TicketResource;

class SupportTicketUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    
    public $ticket;
    
    public function __construct(SupportTicket $ticket)
    {
        $this->ticket = $ticket;
    }
    
    public function broadcastOn()
    {
        return [
            new PrivateChannel("support.ticket.{$this->ticket->id}"),
            new PrivateChannel("support.informaticiens"),
        ];
    }
    
    public function broadcastWith()
    {
        return [
            'ticket' => new TicketResource($this->ticket->load(['category', 'assignedAgent', 'ticketable']))
        ];
    }
}
