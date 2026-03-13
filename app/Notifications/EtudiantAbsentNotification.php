<?php

namespace App\Notifications;

use App\Models\CoursPresence;
use App\Models\Etudiant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class EtudiantAbsentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $presence;
    protected $etudiant;

    /**
     * Create a new notification instance.
     */
    public function __construct(CoursPresence $presence, Etudiant $etudiant)
    {
        $this->presence = $presence;
        $this->etudiant = $etudiant;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // On envoie en base de données + email selon configuration
        $channels = ['database'];
        
        // Si le parent a un email et souhaite être notifié par email
        if ($notifiable->email && $notifiable->notifications_email) {
            $channels[] = 'mail';
        }
        
        // Si le parent a un téléphone et souhaite être notifié par SMS
        if ($notifiable->telephone && $notifiable->notifications_sms) {
            // $channels[] = 'nexmo'; // ou autre service SMS
        }
        
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $statut = $this->presence->statut === 'absent' ? 'absence' : 'retard';
        $cours = $this->presence->seance?->emploiDuTemps?->uv?->nom ?? 'Cours';
        $date = $this->presence->date->format('d/m/Y');
        $heure = $this->presence->heure_arrivee ?? 'Non spécifiée';
        
        return (new MailMessage)
            ->subject("Notification d'{$statut} - {$this->etudiant->prenom} {$this->etudiant->nom}")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("Nous vous informons que votre enfant **{$this->etudiant->prenom} {$this->etudiant->nom}** a été signalé **{$statut}** au cours de **{$cours}** du **{$date}**.")
            ->line("Détails :")
            ->line("- Statut : " . ($statut === 'absence' ? 'Absent' : 'Retard'))
            ->line("- Date : {$date}")
            ->line("- Heure d'arrivée : {$heure}")
            ->line("- Commentaire : " . ($this->presence->commentaire ?? 'Aucun commentaire'))
            ->action('Voir le détail', url('/parent/presences/' . $this->presence->id))
            ->line("Merci de votre attention.");
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        $statut = $this->presence->statut === 'absent' ? 'absence' : 'retard';
        $cours = $this->presence->seance?->emploiDuTemps?->uv?->nom ?? 'Cours';
        
        return [
            'type' => 'presence_' . $this->presence->statut,
            'presence_id' => $this->presence->id,
            'etudiant_id' => $this->etudiant->id,
            'etudiant_nom' => $this->etudiant->prenom . ' ' . $this->etudiant->nom,
            'seance_id' => $this->presence->seance_id,
            'cours' => $cours,
            'date' => $this->presence->date->format('Y-m-d'),
            'statut' => $this->presence->statut,
            'heure_arrivee' => $this->presence->heure_arrivee,
            'message' => "{$this->etudiant->prenom} {$this->etudiant->nom} : {$statut} au cours de {$cours}",
            'icon' => $this->presence->statut === 'absent' ? '❌' : '⏰',
            'color' => $this->presence->statut === 'absent' ? 'red' : 'orange',
            'action_url' => '/parent/presences/' . $this->presence->id
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'presence_id' => $this->presence->id,
            'etudiant_id' => $this->etudiant->id,
            'statut' => $this->presence->statut,
            'date' => $this->presence->date->format('Y-m-d')
        ];
    }
}