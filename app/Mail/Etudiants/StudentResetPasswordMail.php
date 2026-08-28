<?php

namespace App\Mail\Etudiants;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\{Content, Envelope};
use Illuminate\Queue\SerializesModels;
use App\Helpers\ConfigHelper as AppGetters;
use App\Models\Etudiant;

class StudentResetPasswordMail extends Mailable
{
	use Queueable, SerializesModels;

	public function __construct(public Etudiant $etudiant, private readonly string $clearPassword)
	{
	}

	public function envelope(): Envelope
	{
		return new Envelope(
			to: $this->etudiant->getAttribute('email'),
			subject: 'Réinitialisation de votre mot de passe - ' . AppGetters::getAppName(),
		);
	}

	public function content(): Content
	{
		return new Content(
			view: 'mails.base',
			with: [
				'mailTitle' => 'Nouveau mot de passe',
				'mailContent' => $this->getMainContent(),
				'buttonText' => 'Se connecter',
				'buttonHref' => env('FRONTEND_URL', 'http://localhost:3000') . '/login',
			]
		);
	}

	private function getMainContent(): string
	{
		return
			sprintf("<p style='Margin-top: 20px;Margin-bottom: 0;'>&nbsp;<br/>
					Bonjour %s %s.
				</p>
				<p style='Margin-top: 20px;Margin-bottom: 0;'>
				Votre mot de passe a été réinitialisé par un administrateur. Voici votre nouveau mot de passe provisoire : <b>%s</b>.
				Veuillez l'utiliser pour vous connecter et nous vous recommandons fortement de le modifier dès votre première connexion pour des raisons de sécurité.
				</p>
				<p style='Margin-top: 20px;Margin-bottom: 20px;'>Merci pour votre compréhension.
			</p>", $this->etudiant->getAttribute('nom'), $this->etudiant->getAttribute('prenom'), $this->clearPassword);
	}
}
