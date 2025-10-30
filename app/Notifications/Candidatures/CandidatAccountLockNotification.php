<?php

namespace App\Notifications\Candidatures;

use App\Mail\Candidatures\GroupAttributionMail;
use App\Notifications\NotificationBase;
use Illuminate\Bus\Queueable;

class CandidatAccountLockNotification extends NotificationBase
{
	use Queueable;

	static string $icon = '<i class="fas fa-info"></i> ';

	public function __construct(public string $content)
	{
		parent::__construct('Création d\'espace étudiant', $this->content);
	}

	public function via(object $notifiable): array
	{
		return ['database'];
	}
}
