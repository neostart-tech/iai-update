<?php

namespace App\Notifications\Etudiants;

use App\Mail\Externals\DepotCandidatureSentMail;
use App\Models\Announcement;
use App\Notifications\NotificationBase;
use Illuminate\Mail\Mailable;

class DepotCandidatureDoneNotification extends NotificationBase
{
	public static string $icon = '<i class="material-icons-two-tone"> send</i>';

	public function __construct(private readonly Announcement $announcement, private readonly string $filePath)
	{
		$content = 'Le dépôt de votre candidature à l\'offre ' . $this->announcement->getAttribute('title') . ' de '
			. $this->announcement->advertiser->getAttribute('nom') . ' a été fait avec succès.';
		parent::__construct('Dépôt de candidature', $content);
	}

	public function toMail(object $notifiable): Mailable
	{
		return (new DepotCandidatureSentMail($this->announcement->getAttribute('title'), $this->filePath))->to($this->announcement->advertiser->getAttribute('email'));
	}
}
