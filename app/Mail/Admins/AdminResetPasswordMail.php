<?php

namespace App\Mail\Admins;

use App\Models\{User};
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\{Content, Envelope};
use Illuminate\Queue\SerializesModels;
use App\Helpers\ConfigHelper as AppGetters;

class AdminResetPasswordMail extends Mailable implements ShouldQueue
{
	use Queueable, SerializesModels;

	public function __construct(public User $user, private readonly string $clearPassword)
	{
	}

	public function envelope(): Envelope
	{
		return new Envelope(
			to: $this->user->getAttribute('email'),
			subject: 'Réinitialisation de votre mot de passe - ' . AppGetters::getAppName(),
		);
	}

	public function content(): Content
	{
		return new Content(
			view: 'mails.base',
			with: [
				'mailTitle' => 'Réinitialisation de votre mot de passe',
				'mailContent' => $this->getMainContent(),
				'buttonText' => 'Cliquez-ici pour vous connecter',
				'buttonHref' => "https://escendemo.neostart.tech/login",
			]
		);
	}

	private function getMainContent(): string
	{
		return
			sprintf("<p style='Margin-top: 20px;Margin-bottom: 0;'>&nbsp;<br/>
					Bonjour %s %s %s.
				</p>
				<p style='Margin-top: 20px;Margin-bottom: 0;'>
				Votre mot de passe a été réinitialisé par un administrateur. Voici votre nouveau mot de passe provisoire : <b>%s</b>.
				Veuillez l'utiliser pour vous connecter et nous vous recommandons fortement de le modifier dès votre première connexion pour des raisons de sécurité.
				</p>
				<p style='Margin-top: 20px;Margin-bottom: 20px;'>Merci pour votre compréhension.
			</p>", $this->user->getAttribute('genre')->greeting(), $this->user->getAttribute('nom'), $this->user->getAttribute('prenom'), $this->clearPassword);
	}
}
