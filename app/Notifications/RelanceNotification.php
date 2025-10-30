<?php

namespace App\Notifications;

use App\Mail\RelanceTrancheMail;
use App\Models\Etudiant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RelanceNotification extends Notification
{
   use Queueable;

	static string $icon = ' <i class="fas fa-user-alt-slash"></i>  ';

	public function __construct(public string $content)
	{
		parent::__construct("Retard sur le paiement des frais de scolarité", $this->content);
	}

	public function toMail(object $notifiable): RelanceTrancheMail
	{
		return (new RelanceTrancheMail($notifiable->greeting()))->to($notifiable);
	}
}
