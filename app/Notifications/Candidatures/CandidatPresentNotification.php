<?php

namespace App\Notifications\Candidatures;

use App\Mail\Candidatures\CandidaturePayementMail;
use App\Notifications\NotificationBase;
use Illuminate\Bus\Queueable;

class CandidatPresentNotification extends NotificationBase
{
	static string $icon = ' <i class="ti ti-list-check"></i>  ';

	public function via(object $notifiable): array
	{
		return ['database'];
	}

	public function __construct(public string $content)
	{
		parent::__construct("Participation au concours", $this->content);
	}
}
