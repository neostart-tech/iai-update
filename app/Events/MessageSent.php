<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message->load(['sender', 'attachments']);
    }

    public function broadcastOn()
    {
        return new PrivateChannel('conversation.' . $this->message->conversation_id);
    }

    public function broadcastAs()
    {
        return 'message.sent';
    }

    public function broadcastWith()
    {
        $data = [
            'id' => $this->message->id,
            'body' => $this->message->body,
            'type' => $this->message->type,
            'conversation_id' => $this->message->conversation_id,
            'sender_id' => $this->message->sender_id,
            'sender_type' => $this->message->sender_type,
            'sender' => [
                'id' => $this->message->sender->id,
                'nom' => $this->message->sender->nom,
                'prenom' => $this->message->sender->prenom
            ],
            'created_at' => $this->message->created_at,
            'is_edited' => false,
            'read_by_count' => 0
        ];

        // Ajouter les pièces jointes si elles existent
        if ($this->message->attachments->isNotEmpty()) {
            $data['attachments'] = $this->message->attachments->map(function ($attachment) {
                return [
                    'id' => $attachment->id,
                    'file_name' => $attachment->file_name,
                    'file_path' => $attachment->file_path,
                    'file_size' => $attachment->file_size,
                    'mime_type' => $attachment->mime_type,
                    'file_extension' => $attachment->file_extension,
                    'url' => $attachment->url,
                    'icon' => $attachment->icon,
                    'formatted_size' => $attachment->formatted_size,
                    'preview_url' => $attachment->preview_url,
                    'download_url' => $attachment->download_url
                ];
            });
        }

        return $data;
    }
}