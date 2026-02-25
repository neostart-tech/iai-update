<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Echeance;

class RappelEcheanceNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $echeance;
    protected $type;

    /**
     * Create a new notification instance.
     */
    public function __construct(Echeance $echeance, string $type)
    {
        $this->echeance = $echeance;
        $this->type = $type; // 'rappel_3_jours' ou 'retard_3_jours'
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $date = $this->echeance->date_limite->format('d/m/Y');
        $montant = number_format($this->echeance->montant, 0, ',', ' ') . ' FCFA';
        $reste = number_format($this->echeance->reste_a_payer, 0, ',', ' ') . ' FCFA';

        if ($this->type === 'rappel_3_jours') {
            return (new MailMessage)
                ->subject('Rappel d\'échéance de paiement')
                ->greeting('Bonjour ' . $notifiable->prenom . ' ' . $notifiable->nom)
                ->line('Ceci est un rappel concernant votre échéance de paiement.')
                ->line('**' . $this->echeance->libelle . '**')
                ->line('Montant: **' . $montant . '**')
                ->line('Date limite: **' . $date . '**')
                ->line('Reste à payer: **' . $reste . '**')
                ->action('Voir mes échéances', url('/etudiant/mes-echeances'))
                ->line('Merci d\'effectuer votre paiement avant la date limite.')
                ->salutation('L\'équipe de scolarité');
        } else {
            return (new MailMessage)
                ->subject('Retard de paiement')
                ->greeting('Bonjour ' . $notifiable->prenom . ' ' . $notifiable->nom)
                ->line('Nous constatons un retard concernant votre échéance de paiement.')
                ->line('**' . $this->echeance->libelle . '**')
                ->line('Montant: **' . $montant . '**')
                ->line('Date limite dépassée: **' . $date . '**')
                ->line('Reste à payer: **' . $reste . '**')
                ->line('Nous vous invitons à régulariser votre situation dans les plus brefs délais.')
                ->action('Régulariser maintenant', url('/etudiant/mes-echeances'))
                ->salutation('L\'équipe de scolarité');
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $date = $this->echeance->date_limite->format('d/m/Y');
        $montant = number_format($this->echeance->montant, 0, ',', ' ') . ' FCFA';
        
        if ($this->type === 'rappel_3_jours') {
            return [
                'icon' => '🔔',
                'title' => 'Rappel d\'échéance',
                'message' => "Votre échéance '{$this->echeance->libelle}' de {$montant} arrive à échéance le {$date}.",
                'echeance_id' => $this->echeance->id,
                'type' => 'rappel',
                'action_url' => '/etudiant/mes-echeances'
            ];
        } else {
            return [
                'icon' => '⚠️',
                'title' => 'Retard de paiement',
                'message' => "Votre échéance '{$this->echeance->libelle}' de {$montant} est en retard depuis le {$date}.",
                'echeance_id' => $this->echeance->id,
                'type' => 'retard',
                'action_url' => '/etudiant/mes-echeances'
            ];
        }
    }
}