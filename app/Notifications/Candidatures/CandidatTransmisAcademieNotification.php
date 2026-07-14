<?php

namespace App\Notifications\Candidatures;

use App\Mail\Candidatures\CandidatureTransmisAcademieMail;
use App\Notifications\NotificationBase;
use Illuminate\Bus\Queueable;

class CandidatTransmisAcademieNotification extends NotificationBase
{
	use Queueable;

	static string $icon = '<i class="material-icons-two-tone"> send</i> ';

	public function __construct(public string $content)
	{
		parent::__construct("Dossier transmis à l'académie", $this->content);
	}

	public function toMail(object $notifiable): CandidatureTransmisAcademieMail
	{
		return ((new CandidatureTransmisAcademieMail($notifiable->greeting(true)))->to($notifiable));
	}
}
