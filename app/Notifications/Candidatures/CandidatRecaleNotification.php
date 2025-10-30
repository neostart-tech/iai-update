<?php

namespace App\Notifications\Candidatures;

use App\Mail\Candidatures\CandidatureRecaleMail;
use App\Notifications\NotificationBase;
use Illuminate\Bus\Queueable;

class CandidatRecaleNotification extends NotificationBase
{
	use Queueable;

	static string $icon = ' <i class="fas fa-user-graduate"></i> ';

	public function __construct(public string $content)
	{
		parent::__construct("Résultat du concours", $this->content);
	}

	public function toMail(object $notifiable): CandidatureRecaleMail
	{
		return ((new CandidatureRecaleMail($notifiable->greeting(true)))->to($notifiable));
	}
}
