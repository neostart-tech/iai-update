<?php

namespace App\Notifications\Candidatures;

use App\Mail\Candidatures\CandidatureDepotMail;
use App\Notifications\NotificationBase;
use Illuminate\Bus\Queueable;

class CandidatWelcomeNotification extends NotificationBase
{
	use Queueable;

	static string $icon = '<i data-feather="user-check"></i> ';

	public function __construct(private readonly string $greeting, public string $content)
	{
		parent::__construct("Dépôt de candidature", $this->content);
	}

	public function toMail(object $notifiable): CandidatureDepotMail
	{
		return ((new CandidatureDepotMail($this->greeting))->to($notifiable));
	}
}
