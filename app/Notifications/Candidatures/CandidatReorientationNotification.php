<?php

namespace App\Notifications\Candidatures;

use App\Mail\Candidatures\CandidatureReorientationMail;
use App\Notifications\NotificationBase;
use Illuminate\Bus\Queueable;

class CandidatReorientationNotification extends NotificationBase
{
	use Queueable;

	static string $icon = '<i class="material-icons-two-tone"> swap_horiz</i> ';

	public function __construct(public string $content, public string $motif, public string $filiere, public string $niveau)
	{
		parent::__construct("Candidature réorientée", $this->content, 2);
	}

	public function toMail(object $notifiable): CandidatureReorientationMail
	{
		return ((new CandidatureReorientationMail($notifiable->greeting(true), $this->motif, $this->filiere, $this->niveau))->to($notifiable));
	}
}
