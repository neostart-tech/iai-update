<?php

namespace App\Events\Support;

use App\Models\Support\SupportMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SupportMessageUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    
    public $message;
    
    public function __construct(SupportMessage $message)
    {
        $this->message = $message;
    }
    
    public function broadcastOn()
    {
        return new PrivateChannel("support.ticket.{$this->message->ticket_id}");
    }
    
    public function broadcastWith()
    {
        return [
            'ticket_id' => $this->message->ticket_id,
            'message' => [
                'id' => $this->message->id,
                'message' => $this->message->message,
                'type' => $this->message->type,
                'attachments' => $this->message->attachments->map(function($att) {
                    return [
                        'id' => $att->id,
                        'original_name' => $att->original_name,
                        'path' => $att->path,
                    ];
                }),
                'updated_at' => $this->message->updated_at,
            ],
        ];
    }
}
