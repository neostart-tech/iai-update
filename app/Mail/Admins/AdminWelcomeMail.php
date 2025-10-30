<?php

namespace App\Mail\Admins;

use App\Models\{User};
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\{Content, Envelope};
use Illuminate\Queue\SerializesModels;
use App\Helpers\ConfigHelper as AppGetters;

class AdminWelcomeMail extends Mailable implements ShouldQueue
{
	use Queueable, SerializesModels;

	public function __construct(public User $user, private readonly string $clearPassword)
	{
	}

	public function envelope(): Envelope
	{
		return new Envelope(
			to: $this->user->getAttribute('email'),
			subject: 'Bienvenue au sein de l\'administration de' . ' ' . AppGetters::getAppName(),
		);
	}

	public function content(): Content
	{
		dump(url(route('login')));
		return new Content(
			view: 'mails.base',
			with: [
				'mailTitle' => 'Bienvenue dans l\'administration de ' . ' ' . AppGetters::getAppName(),
				'mailContent' => $this->getMainContent(),
				'buttonText' => 'Cliquez-ici pour finaliser la création de votre compte',
				'buttonHref' => url(route('login')),
				//				'moreInfo' => '<span class="text-center">Ce mot de passe est valable pour 60 prochaines minutes après réception du présent mail.</span>'
			]
		);
	}

	private function getMainContent(): string
	{
		return
			sprintf("<p style='Margin-top: 20px;Margin-bottom: 0;'>&nbsp;<br/>
					Bonjour %s Bienvenue à vous au sein de l'administration.
				</p>
				<p style='Margin-top: 20px;Margin-bottom: 0;'>
				Ce mail vous a été envoyé pour la finalisation de la création de votre espace. Voici votre mot passe par défaut <b>%s</b>.
				Utilisez-le pour accéder à votre espace et redéfinissez-en un nouveau plus sûr.
				</p>
				<p style='Margin-top: 20px;Margin-bottom: 20px;'>Merci pour votre compréhension.
			</p>", $this->user->getAttribute('genre')->greeting(), $this->clearPassword);
	}
}
