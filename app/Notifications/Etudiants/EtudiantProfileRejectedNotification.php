<?php

namespace App\Notifications\Etudiants;

use App\Models\Etudiant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EtudiantProfileRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Etudiant $etudiant,
        public string $motifRefus
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
            <p style='margin-bottom: 20px; font-size: 15px;'>Après examen de votre demande de mise à jour de profil par l'administration, celle-ci n'a pas pu être validée pour le motif suivant :</p>
            
            <div style='background-color: #fef2f2; padding: 20px; border-radius: 8px; border: 1px solid #fecaca; margin-bottom: 25px;'>
                <p style='margin: 0 0 5px 0; color: #991b1b; font-weight: bold;'>Motif du refus :</p>
                <p style='margin: 0; color: #b91c1c; font-size: 15px;'>" . nl2br(e($this->motifRefus)) . "</p>
            </div>

            <p style='margin-bottom: 15px; font-size: 14px; color: #4b5563;'>
                Vos informations précédentes restent inchangées sur votre profil officiel. Vous pouvez toujours accéder normalement à la plateforme et soumettre de nouvelles corrections ou les pièces justificatives demandées à tout moment.
            </p>
        ";

        return (new MailMessage)
            ->subject("Mise à jour de profil non validée - " . config('app.name', 'IAI-Togo'))
            ->view('mails.base', [
                'mailTitle' => 'Demande de mise à jour non validée',
                'mailContent' => $mailContent,
                'buttonText' => 'Accéder à mon espace étudiant',
                'buttonHref' => "{$frontendUrl}/etudiant/completer-profil",
                'moreInfo' => 'Veuillez corriger les éléments indiqués et soumettre à nouveau votre profil.'
            ]);
    }
}
