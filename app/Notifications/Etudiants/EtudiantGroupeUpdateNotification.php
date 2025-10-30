<?php

namespace App\Notifications\Etudiants;

use App\Notifications\NotificationBase;

class EtudiantGroupeUpdateNotification extends NotificationBase
{
	public static string $icon = '<i class="material-icons-two-tone">warning</i>';

	public function __construct(public string $content)
	{
		parent::__construct("Changement de groupe", $this->content);
	}

	public function via(object $notifiable): array
	{
		return ['database'];
	}
}
