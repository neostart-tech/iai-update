<?php

namespace App\Notifications\Candidatures;

use App\Mail\Candidatures\CandidatureRejeteMail;
use App\Notifications\NotificationBase;
use Illuminate\Bus\Queueable;

class CandidatRejeteNotification extends NotificationBase
{
	use Queueable;

	static string $icon = '<i class="material-icons-two-tone"> cancel</i> ';

	public function __construct(public string $content, public string $motif)
	{
		parent::__construct("Candidature rejetée", $this->content, 3);
	}

	public function toMail(object $notifiable): CandidatureRejeteMail
	{
		return ((new CandidatureRejeteMail($notifiable->greeting(true), $this->motif))->to($notifiable));
	}
}
