<?php

namespace App\Notifications;

use App\Models\UrgentInfo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class ActualiteNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $actualite;

    public function __construct(UrgentInfo $actualite)
    {
        $this->actualite = $actualite;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
        $detailUrl = "{$frontendUrl}/actualites/" . $this->actualite->id;

        return [
            'title' => 'Nouvelle actualité : ' . $this->actualite->title,
            'content' => $this->actualite->summary,
            'id' => $this->actualite->id,
            'url' => $detailUrl,
            'type' => 'actualite',
            'level' => 'info',
            'created_at' => now()->toDateTimeString(),
        ];
    }
}
