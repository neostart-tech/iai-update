<?php
namespace App\Notifications;

use App\Models\Reclamation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NouvelleReclamationNotification extends Notification
{
    use Queueable;

    public $reclamation;

    public function __construct(Reclamation $reclamation)
    {
        $this->reclamation = $reclamation;
    }

    public function via($notifiable)
    {
        return ['mail']; // Ou ['database', 'mail'] selon config
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("Nouvelle réclamation de note")
            ->line("L'étudiant {$this->reclamation->etudiant->nom} a soumis une réclamation.")
            ->action('Voir réclamation', url('/admin/reclamations'));
    }
}
