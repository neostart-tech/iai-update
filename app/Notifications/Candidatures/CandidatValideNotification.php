<?php

namespace App\Notifications\Candidatures;

use App\Mail\Candidatures\CandidatureValideMail;
use App\Notifications\NotificationBase;
use Illuminate\Bus\Queueable;

class CandidatValideNotification extends NotificationBase
{
	use Queueable;

	static string $icon = '<i class="material-icons-two-tone"> playlist_add_check</i> ';

	public function __construct(public string $content)
	{
		parent::__construct("Candidature accepté", $this->content);
	}

	public function toMail(object $notifiable): CandidatureValideMail
	{
		return ((new CandidatureValideMail($notifiable->greeting(true)))->to($notifiable));
	}
}
