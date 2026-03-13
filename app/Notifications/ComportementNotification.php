<?php

namespace App\Notifications;

use App\Models\Comportement;
use App\Models\Etudiant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ComportementNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $comportement;
    protected $etudiant;

    /**
     * Create a new notification instance.
     */
    public function __construct(Comportement $comportement, Etudiant $etudiant)
    {
        $this->comportement = $comportement;
        $this->etudiant = $etudiant;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];
        
        // Si c'est une alerte, on envoie aussi par email
        if ($this->comportement->type === 'alerte' || $this->comportement->intensite >= 4) {
            $channels[] = 'mail';
        }
        
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $typeTexte = $this->comportement->type === 'positif' ? 'positif' : 'préoccupant';
        
        return (new MailMessage)
            ->subject("Signalement comportemental - {$this->etudiant->prenom} {$this->etudiant->nom}")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("Un signalement **{$typeTexte}** concernant votre enfant **{$this->etudiant->prenom} {$this->etudiant->nom}** a été enregistré.")
            ->line("Détails :")
            ->line("- Type : " . $this->comportement->type)
            ->line("- Catégorie : " . $this->comportement->categorie)
            ->line("- Description : " . $this->comportement->description)
            ->action('Voir le détail', url('/parent/comportements/' . $this->comportement->id))
            ->line("Merci de votre attention.");
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'comportement_' . $this->comportement->type,
            'comportement_id' => $this->comportement->id,
            'etudiant_id' => $this->etudiant->id,
            'etudiant_nom' => $this->etudiant->prenom . ' ' . $this->etudiant->nom,
            'type_comportement' => $this->comportement->type,
            'categorie' => $this->comportement->categorie,
            'libelle' => $this->comportement->libelle,
            'intensite' => $this->comportement->intensite,
            'message' => "{$this->etudiant->prenom} {$this->etudiant->nom} : {$this->comportement->libelle}",
            'icon' => $this->comportement->type === 'positif' ? '👍' : '⚠️',
            'color' => $this->comportement->type === 'positif' ? 'green' : 'red',
            'action_url' => '/parent/comportements/' . $this->comportement->id
        ];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'comportement_id' => $this->comportement->id,
            'etudiant_id' => $this->etudiant->id,
            'type' => $this->comportement->type
        ];
    }
}