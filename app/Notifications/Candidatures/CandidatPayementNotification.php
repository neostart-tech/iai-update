<?php

namespace App\Notifications\Candidatures;

use App\Mail\Candidatures\CandidaturePayementMail;
use App\Notifications\NotificationBase;
use Illuminate\Bus\Queueable;

class CandidatPayementNotification extends NotificationBase
{
	use Queueable;

	static string $icon = ' <i class="material-icons-two-tone"> attach_money</i> ';

	public function __construct(public string $content)
	{
		parent::__construct("Payement de la quittance", $this->content);
	}

	public function toMail(object $notifiable): CandidaturePayementMail
	{
		return ((new CandidaturePayementMail($notifiable->greeting(true), $notifiable->getAttribute('code')))->to($notifiable));
	}
}
