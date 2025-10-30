<?php

namespace App\Notifications;

class EnseignantEvaluationDeProgrammationNotification extends NotificationBase
{
	public function __construct(public string $content)
	{
		parent::__construct('Annonce de déprogrammation pour la surveillance à une évaluation', $content, 3);
	}

	public function via(object $notifiable): array
	{
		return ['database'];
	}
}
