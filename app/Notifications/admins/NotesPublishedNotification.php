<?php

namespace App\Notifications\admins;

use App\Notifications\NotificationBase;

class NotesPublishedNotification extends NotificationBase
{
	public static string $icon = '<i class="fas fa-info"></i>';

	public function __construct(public string $name)
	{
		parent::__construct("Publication de notes", 'Les notes de l\'évaluation ' . $this->name . ' ont été publiées.');
	}

	public function via(object $notifiable): array
	{
		return ['database'];
	}
}
