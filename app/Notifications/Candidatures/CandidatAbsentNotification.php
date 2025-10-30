<?php

namespace App\Notifications\Candidatures;

use App\Mail\Candidatures\CandidatureAbsentMail;
use App\Notifications\NotificationBase;
use Illuminate\Bus\Queueable;

class CandidatAbsentNotification extends NotificationBase
{
	use Queueable;

	static string $icon = ' <i class="fas fa-user-alt-slash"></i>  ';

	public function __construct(public string $content)
	{
		parent::__construct("Absence et Élimination", $this->content);
	}

	public function toMail(object $notifiable): CandidatureAbsentMail
	{
		return (new CandidatureAbsentMail($notifiable->greeting()))->to($notifiable);
	}
}
