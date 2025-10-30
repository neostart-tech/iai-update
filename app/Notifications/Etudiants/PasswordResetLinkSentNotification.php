<?php

namespace App\Notifications\Etudiants;

use App\Mail\Etudiants\PasswordResetLinkSentMail;
use App\Notifications\NotificationBase;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;

class PasswordResetLinkSentNotification extends NotificationBase
{
	use Queueable;

	public function __construct(private readonly string $token, private readonly string $email)
	{
		parent::__construct();
	}

	public function via(object $notifiable): array
	{
		return ['mail'];
	}

	public function toMail(object $notifiable): Mailable
	{
		return (new PasswordResetLinkSentMail($this->token, $this->email, $notifiable->greeting()))->to($notifiable);
	}
}
