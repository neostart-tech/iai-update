<?php

namespace App\Notifications\Candidatures;

use App\Models\Candidature;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CandidatureStatusUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Candidature $candidature;
    public string $statusTitle;
    public string $statusDetails;
    public ?string $actorName;

    /**
     * Create a new notification instance.
     */
    public function __construct(Candidature $candidature, string $statusTitle, string $statusDetails, ?string $actorName = null)
    {
        $this->candidature = $candidature;
        $this->statusTitle = $statusTitle;
        $this->statusDetails = $statusDetails;
        $this->actorName = $actorName;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $recipientName = method_exists($notifiable, 'civiliteName') ? $notifiable->civiliteName() : ($notifiable->nom ?? 'Cher utilisateur');
        $candidatName = method_exists($this->candidature, 'civiliteName') ? $this->candidature->civiliteName() : ($this->candidature->nom . ' ' . $this->candidature->prenom);

        $actorInfo = $this->actorName ? "<p style='margin: 0 0 10px 0;'><strong>Action effectuée par :</strong> {$this->actorName}</p>" : "";

        $mailContent = "
            <p style='margin-bottom: 15px; font-size: 16px;'>Bonjour <strong>{$recipientName}</strong>,</p>
            <p style='margin-bottom: 20px; font-size: 15px;'>Le statut du dossier de candidature de <strong>{$candidatName}</strong> a été mis à jour.</p>

            <div style='background-color: #f9f9f9; padding: 20px; border-radius: 8px; border: 1px solid #eaeaea; margin-bottom: 25px;'>
                <p style='margin: 0 0 10px 0;'><strong>Nouveau statut :</strong> {$this->statusTitle}</p>
                <p style='margin: 0 0 10px 0;'><strong>Détails :</strong> {$this->statusDetails}</p>
                {$actorInfo}
                <p style='margin: 0 0 10px 0;'><strong>Candidat :</strong> {$candidatName}</p>
                <p style='margin: 0 0 10px 0;'><strong>Email :</strong> <a href='mailto:{$this->candidature->email}' style='color: #80BF2E; text-decoration: none;'>{$this->candidature->email}</a></p>
                <p style='margin: 0 0 10px 0;'><strong>Téléphone :</strong> <a href='tel:{$this->candidature->tel}' style='color: #80BF2E; text-decoration: none;'>{$this->candidature->tel}</a></p>
            </div>
        ";

        return (new MailMessage)
                    ->subject("Mise à jour candidature - {$this->statusTitle} ({$candidatName})")
                    ->view('mails.base', [
                        'mailTitle' => $this->statusTitle,
                        'mailContent' => $mailContent,
                        'buttonText' => 'Consulter le dossier',
                        'buttonHref' => rtrim(env('FRONTEND_URL', 'http://localhost:3001'), '/') . '/candidatures/etude-dossier',
                        'moreInfo' => 'Consultez la fiche du candidat pour plus de détails.'
                    ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'candidature_id' => $this->candidature->id,
            'nom' => $this->candidature->nom,
            'prenom' => $this->candidature->prenom,
            'email' => $this->candidature->email,
            'title' => $this->statusTitle,
            'details' => $this->statusDetails,
            'message' => "Mise à jour candidature ({$this->statusTitle}) : {$this->candidature->nom} {$this->candidature->prenom}",
            'type' => 'candidature_status_updated'
        ];
    }
}
