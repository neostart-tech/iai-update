<?php

namespace App\Notifications\Etudiants;

use App\Models\Etudiant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EtudiantProfileValidatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Etudiant $etudiant
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $etudiantNom = trim(($this->etudiant->nom ?? '') . ' ' . ($this->etudiant->prenom ?? ''));
        $frontendUrl = rtrim(env('FRONTEND_URL', 'http://localhost:3001'), '/');

        $mailContent = "
            <p style='margin-bottom: 15px; font-size: 16px;'>Bonjour <strong>{$etudiantNom}</strong>,</p>
            <p style='margin-bottom: 20px; font-size: 15px;'>Nous vous informons que les modifications apportées à votre profil ainsi que vos pièces justificatives ont été <strong>vérifiées et validées avec succès</strong> par l'administration.</p>
            
            <div style='background-color: #f0fdf4; padding: 20px; border-radius: 8px; border: 1px solid #bbf7d0; margin-bottom: 25px;'>
                <p style='margin: 0; color: #166534; font-weight: 500;'>
                    Votre profil officiel est désormais à jour. Vous pouvez consulter l'ensemble de vos informations à tout moment depuis votre espace étudiant.
                </p>
            </div>
            <p style='margin-bottom: 15px; font-size: 14px; color: #666;'>Merci pour votre collaboration et votre réactivité.</p>
        ";

        return (new MailMessage)
            ->subject("Validation de votre profil étudiant - " . config('app.name', 'IAI-Togo'))
            ->view('mails.base', [
                'mailTitle' => 'Votre profil a été validé',
                'mailContent' => $mailContent,
                'buttonText' => 'Consulter mon profil',
                'buttonHref' => "{$frontendUrl}/profile/info",
                'moreInfo' => 'Vos données officielles sont désormais enregistrées.'
            ]);
    }
}
