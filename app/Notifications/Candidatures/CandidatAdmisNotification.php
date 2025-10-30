<?php

namespace App\Notifications\Candidatures;

use App\Mail\Candidatures\CandidatureAdmisMail;
use App\Notifications\NotificationBase;
use Illuminate\Bus\Queueable;

class CandidatAdmisNotification extends NotificationBase
{
	use Queueable;

	static string $icon = ' <i class="fas fa-user-graduate"></i> ';

	public function __construct(public string $content)
	{
		parent::__construct("Résultat du concours", $this->content);
	}

	public function toMail(object $notifiable): CandidatureAdmisMail
	{
		return ((new CandidatureAdmisMail($notifiable->greeting(true)))->to($notifiable));
	}
}
