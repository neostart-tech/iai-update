<?php

namespace App\Notifications\Candidatures;

use App\Mail\Candidatures\CandidatureRectificationMail;
use App\Notifications\NotificationBase;
use Illuminate\Bus\Queueable;

class CandidatRectificationNotification extends NotificationBase
{
	use Queueable;

	static string $icon = '<i class="material-icons-two-tone"> edit</i> ';

	public function __construct(public string $content, public string $motif)
	{
		parent::__construct("Rectification de dossier requise", $this->content, 2);
	}

	public function toMail(object $notifiable): CandidatureRectificationMail
	{
		return ((new CandidatureRectificationMail($notifiable->greeting(true), $this->motif))->to($notifiable));
	}
}
