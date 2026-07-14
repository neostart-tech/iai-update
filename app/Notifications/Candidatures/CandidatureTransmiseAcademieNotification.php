<?php

namespace App\Notifications\Candidatures;

use App\Models\Candidature;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CandidatureTransmiseAcademieNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $candidature;

    /**
     * Create a new notification instance.
     */
    public function __construct(Candidature $candidature)
    {
        $this->candidature = $candidature;
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
        $mailContent = "
            <p style='margin-bottom: 15px; font-size: 16px;'>Bonjour <strong>{$notifiable->nom}</strong>,</p>
            <p style='margin-bottom: 20px; font-size: 15px;'>Un dossier de candidature a été vérifié par le chargé de la clientèle et vous est transmis pour étude académique.</p>

            <div style='background-color: #f9f9f9; padding: 20px; border-radius: 8px; border: 1px solid #eaeaea; margin-bottom: 25px;'>
                <p style='margin: 0 0 10px 0;'><strong>Candidat :</strong> {$this->candidature->nom} {$this->candidature->prenom}</p>
                <p style='margin: 0 0 10px 0;'><strong>Email :</strong> <a href='mailto:{$this->candidature->email}' style='color: #80BF2E; text-decoration: none;'>{$this->candidature->email}</a></p>
                <p style='margin: 0 0 10px 0;'><strong>Téléphone :</strong> <a href='tel:{$this->candidature->tel}' style='color: #80BF2E; text-decoration: none;'>{$this->candidature->tel}</a></p>
            </div>
        ";

        return (new MailMessage)
                    ->subject('Dossier transmis pour étude académique - ' . $this->candidature->nom . ' ' . $this->candidature->prenom)
                    ->view('mails.base', [
                        'mailTitle' => 'Dossier transmis pour étude académique',
                        'mailContent' => $mailContent,
                        'buttonText' => 'Étudier le dossier',
                        'buttonHref' => rtrim(env('FRONTEND_URL', 'http://localhost:3001'), '/') . '/candidatures/etude-dossier',
                        'moreInfo' => 'Veuillez examiner cette candidature dans le tableau de bord.'
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
            'message' => 'Dossier transmis à l\'académie : ' . $this->candidature->nom . ' ' . $this->candidature->prenom,
            'type' => 'candidature_transmise_academie'
        ];
    }
}
