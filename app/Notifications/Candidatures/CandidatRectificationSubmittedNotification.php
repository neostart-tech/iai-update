<?php

namespace App\Notifications\Candidatures;

use App\Models\Candidature;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CandidatRectificationSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $candidature;

    public function __construct(Candidature $candidature)
    {
        $this->candidature = $candidature;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $recipientName = method_exists($notifiable, 'civiliteName') ? $notifiable->civiliteName() : ($notifiable->nom ?? 'Cher utilisateur');
        $candidatName = method_exists($this->candidature, 'civiliteName') ? $this->candidature->civiliteName() : ($this->candidature->nom . ' ' . $this->candidature->prenom);

        $mailContent = "
            <p style='margin-bottom: 15px; font-size: 16px;'>Bonjour <strong>{$recipientName}</strong>,</p>
            <p style='margin-bottom: 20px; font-size: 15px;'>Le candidat a soumis ses rectifications et son dossier est à nouveau en attente de validation.</p>
            
            <div style='background-color: #f9f9f9; padding: 20px; border-radius: 8px; border: 1px solid #eaeaea; margin-bottom: 25px;'>
                <p style='margin: 0 0 10px 0;'><strong>Candidat :</strong> {$candidatName}</p>
                <p style='margin: 0 0 10px 0;'><strong>Email :</strong> <a href='mailto:{$this->candidature->email}' style='color: #80BF2E; text-decoration: none;'>{$this->candidature->email}</a></p>
                <p style='margin: 0 0 10px 0;'><strong>Téléphone :</strong> <a href='tel:{$this->candidature->tel}' style='color: #80BF2E; text-decoration: none;'>{$this->candidature->tel}</a></p>
                <p style='margin: 0 0 10px 0;'><strong>Nationalité :</strong> {$this->candidature->nationalite}</p>
            </div>
        ";

        return (new MailMessage)
                    ->subject('Rectifications soumises - ' . $candidatName)
                    ->view('mails.base', [
                        'mailTitle' => 'Rectifications Soumises',
                        'mailContent' => $mailContent,
                        'buttonText' => 'Étudier le dossier',
                        'buttonHref' => rtrim(env('FRONTEND_URL', 'http://localhost:3001'), '/') . '/candidatures/etude-dossier',
                        'moreInfo' => 'Veuillez examiner les nouvelles informations dans le tableau de bord.'
                    ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'candidature_id' => $this->candidature->id,
            'nom' => $this->candidature->nom,
            'prenom' => $this->candidature->prenom,
            'email' => $this->candidature->email,
            'message' => 'Rectifications soumises par ' . $this->candidature->nom . ' ' . $this->candidature->prenom,
            'type' => 'rectification_submitted'
        ];
    }
}
