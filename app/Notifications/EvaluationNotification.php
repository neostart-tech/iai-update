<?php

namespace App\Notifications;

class EvaluationNotification extends NotificationBase
{
	public string $title = "Annonce d'évaluation";

	public static string $icon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2-event pc-icon text-warning" viewBox="0 0 16 16">
	  <path d="M11 7.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5z"/>
	  <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1z"/>
	  <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5z"/>
	</svg>';

	public function __construct(
		private readonly string $identity,
		private readonly string $evaluationDataAsString
	)
	{
		parent::__construct($this->title, $this->generateMessage());
	}

	public function via(object $notifiable): array
	{
		return ['database'];
	}

	private function generateMessage(): string
	{
		return "Bonjour " . $this->identity . ". Vous avez un " . $this->evaluationDataAsString;
	}

//	public function setColor(): string
//	{
//		return Str::replace('text-', 'text-'. $this->getColor(), self::$icon);
//	}
//
//	public function getColor(): string
//	{
//		return $this->level === 1 ? 'info' : ($this->level === 2 ? 'warning' : 'danger');
//	}
}
