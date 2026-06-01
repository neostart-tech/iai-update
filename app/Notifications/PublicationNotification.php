<?php

namespace App\Notifications;

use App\Models\Blog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class PublicationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $blog;

    public function __construct(Blog $blog)
    {
        $this->blog = $blog;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
        // On suppose que le lien vers le détail est /publications/{slug}/detail
        $detailUrl = "{$frontendUrl}/publications/{$this->blog->slug}/detail";

        return [
            'title' => 'Nouvelle publication : ' . $this->blog->title,
            'content' => 'Un nouvel article a été publié : ' . $this->blog->title,
            'slug' => $this->blog->slug,
            'url' => $detailUrl,
            'level' => 'info',
            'type' => 'publication',
            'created_at' => now()->toDateTimeString(),
        ];
    }
}
