<?php

namespace App\Notifications\Etudiants;

use App\Notifications\NotificationBase;

class EtudiantAbsentNotification extends NotificationBase
{
	public static string $icon = '<i data-feather="check-square"></i>';

	public function __construct(public string $title, public string $content)
	{
		parent::__construct($this->title, $this->content);
	}

	public function via(object $notifiable): array
	{
		return ['database'];
	}
}
