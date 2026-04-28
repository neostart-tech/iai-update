<?php

namespace App\Notifications;

use App\Models\Communication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class CommunicationPublishedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Communication $communication;

    public function __construct(Communication $communication)
    {
        $this->communication = $communication;
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'id' => $this->communication->uuid,
            'title' => $this->communication->title,
            'type' => $this->communication->type,
            'published_at' => $this->communication->published_at,
            'author' => $this->communication->author->nom_complet ?? 'Administration',
            'message' => Str::limit(strip_tags($this->communication->content), 120),
            'action_url' => '/communications/' . $this->communication->slug,
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => $this->communication->uuid,
            'title' => $this->communication->title,
            'type' => $this->communication->type,
            'message' => Str::limit(strip_tags($this->communication->content), 120),
            'action_url' => '/communications/' . $this->communication->slug,
        ]);
    }
}
