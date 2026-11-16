<?php

namespace App\Notifications\Etudiants;

use App\Models\Etudiant;
use App\Models\EtudiantProfileRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EtudiantProfileSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Etudiant $etudiant,
        public EtudiantProfileRequest $profileRequest
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $recipientName = method_exists($notifiable, 'civiliteName') ? $notifiable->civiliteName() : ($notifiable->nom ?? 'Cher administrateur');
        $etudiantNom = trim(($this->etudiant->nom ?? '') . ' ' . ($this->etudiant->prenom ?? ''));
        $matricule = $this->etudiant->matricule ?? 'Non défini';
        $frontendUrl = rtrim(env('FRONTEND_URL', 'http://localhost:3001'), '/');

        $mailContent = "
            <p style='margin-bottom: 15px; font-size: 16px;'>Bonjour <strong>{$recipientName}</strong>,</p>
            <p style='margin-bottom: 20px; font-size: 15px;'>Un étudiant a soumis des modifications ou des pièces justificatives pour son profil. Cette demande est en attente de votre validation.</p>
            
            <div style='background-color: #f9f9f9; padding: 20px; border-radius: 8px; border: 1px solid #eaeaea; margin-bottom: 25px;'>
                <p style='margin: 0 0 10px 0;'><strong>Étudiant :</strong> {$etudiantNom}</p>
                <p style='margin: 0 0 10px 0;'><strong>Matricule :</strong> {$matricule}</p>
                <p style='margin: 0 0 10px 0;'><strong>Email :</strong> <a href='mailto:{$this->etudiant->email}' style='color: #80BF2E; text-decoration: none;'>{$this->etudiant->email}</a></p>
                <p style='margin: 0 0 10px 0;'><strong>Téléphone :</strong> {$this->etudiant->tel}</p>
                <p style='margin: 0 0 10px 0;'><strong>Date de soumission :</strong> " . now()->format('d/m/Y H:i') . "</p>
            </div>
            <p style='margin-bottom: 15px; font-size: 14px; color: #666;'>Veuillez vous connecter à la plateforme pour vérifier et valider ou refuser cette demande.</p>
        ";

        return (new MailMessage)
            ->subject("Nouvelle demande de validation de profil - {$etudiantNom} ({$matricule})")
            ->view('mails.base', [
                'mailTitle' => 'Demande de validation de profil',
                'mailContent' => $mailContent,
                'buttonText' => 'Examiner la demande',
                'buttonHref' => "{$frontendUrl}/admin/etudiants/demandes-profil",
                'moreInfo' => 'Seules les modifications validées par l\'administration seront appliquées sur la fiche officielle de l\'étudiant.'
            ]);
    }
}
