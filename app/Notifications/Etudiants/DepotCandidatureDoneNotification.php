<?php
// app/Notifications/Etudiants/DepotCandidatureDoneNotification.php

namespace App\Notifications\Etudiants;

use App\Mail\Externals\DepotCandidatureSentMail;
use App\Models\Announcement;
use App\Notifications\NotificationBase;
use Illuminate\Mail\Mailable;

class DepotCandidatureDoneNotification extends NotificationBase
{
    public static string $icon = '<i class="material-icons-two-tone"> send</i>';

    public function __construct(
        private readonly Announcement $announcement,
        private readonly ?string $filePath = null  // Reçoit le CV uploadé
    ) {
        $content = 'Le dépôt de votre candidature à l\'offre ' . $this->announcement->getAttribute('title') . ' de '
            . ($this->announcement->advertiser?->getAttribute('nom') ?? 'l\'annonceur') . ' a été fait avec succès.';
        parent::__construct('Dépôt de candidature', $content);
    }

    public function toMail(object $notifiable): Mailable
    {
        // On envoie l'email à l'annonceur avec le CV uploadé
        return (new DepotCandidatureSentMail(
            $this->announcement->getAttribute('title'),
            $this->filePath  // C'est le CV uploadé
        ))->to($this->announcement->advertiser?->getAttribute('email'));
    }
}