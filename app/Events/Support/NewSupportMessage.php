<?php

namespace App\Events\Support;

use App\Models\Support\SupportTicket;
use App\Models\Support\SupportMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewSupportMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    
    public $ticket;
    public $message;
    
    public function __construct(SupportTicket $ticket, SupportMessage $message)
    {
        $this->ticket = $ticket;
        $this->message = $message;
    }
    
    public function broadcastOn()
    {
        // Canal privé pour le créateur du ticket
        $channels = [];
        
        // Canal pour le créateur du ticket
        $channels[] = new PrivateChannel("support.ticket.{$this->ticket->id}");
        
        // Canal pour l'informaticien assigné
        if ($this->ticket->assigned_to) {
            $channels[] = new PrivateChannel("support.agent.{$this->ticket->assigned_to}");
        }
        
        // Canal pour tous les informaticiens (pour la liste des tickets)
        $channels[] = new PrivateChannel("support.informaticiens");
        
        return $channels;
    }
    
    public function broadcastWith()
    {
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_reference' => $this->ticket->reference,
            'message' => [
                'id' => $this->message->id,
                'message' => $this->message->message,
                'user' => [
                    'id' => $this->message->user->id,
                    'name' => $this->message->user->nom . ' ' . $this->message->user->prenom,
                ],
                'type' => $this->message->type,
                'created_at' => $this->message->created_at,
            ],
        ];
    }
}