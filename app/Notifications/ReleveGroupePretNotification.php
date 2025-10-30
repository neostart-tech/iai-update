<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReleveGroupePretNotification extends Notification
{
    use Queueable;
    
  protected $filePath;

    /**
     * Create a new notification instance.
     */
    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }



    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = url('storage/' . $this->filePath);

        return (new MailMessage)
            ->subject('Relevé de notes du groupe prêt')
            ->line('Le relevé de notes que vous avez demandé est maintenant disponible.')
            ->action('Télécharger le relevé', $url)
            ->line('Merci.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
