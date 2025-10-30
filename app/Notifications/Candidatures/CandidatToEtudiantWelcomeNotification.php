<?php

namespace App\Notifications\Candidatures;

use App\Mail\Candidatures\GroupAttributionMail;
use App\Notifications\NotificationBase;
use Illuminate\Bus\Queueable;

class CandidatToEtudiantWelcomeNotification extends NotificationBase
{
	use Queueable;

	static string $icon = '<i class="ph-duotone ph-student"></i> ';

	public function __construct(public string $content)
	{
		parent::__construct('Création d\'espace étudiant', $this->content);
	}

	public function toMail(object $notifiable): GroupAttributionMail
	{
		return (new GroupAttributionMail($notifiable->greeting()))->to($notifiable);
	}
}
