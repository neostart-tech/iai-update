<?php

namespace App\Mail\Etudiants;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Helpers\ConfigHelper as AppGetters;

class PasswordResetLinkSentMail extends Mailable
{
	use Queueable, SerializesModels;

	private string $route;

	public function __construct(private readonly string $token, private readonly string $email, private readonly string $greeting)
	{
		$this->route = url(route('etudiants.password.reset', ['token' => $this->token . '?email=' . $this->email]));
	}

	public function envelope(): Envelope
	{
		return new Envelope(
			subject: 'Réinitialisation de mot de passe',
		);
	}

	public function content(): Content
	{
		return new Content(
			view: 'mails.candidatures.group-attribution',
			with: [
				'mailTitle' => "Réinitialisation de mot de passe",
				'mailContent' => $this->mainContent(),
				'buttonHref' => $this->route,
				'buttonText' => 'Réinitialiser mon mot de passe',
				'moreInfo' => $this->moreInfo()
			]
		);
	}

	private function mainContent(): string
	{
		return $this->greeting . ". Vous recevez cet e-mail, car nous avons reçu une demande de réinitialisation du mot de passe de votre compte. <br>
		Ce lien de réinitialisation de mot de passe expirera dans 60 minutes. Si vous n'avez pas demandé de réinitialisation du mot de passe, aucune autre action n'est requise.
    Cordialement, " .' '.AppGetters::getAppName() . ".";
	}

	private function moreInfo(): string
	{
		return "<div style='margin-top: 20px;'></div>
    Si vous rencontrez des difficultés pour cliquer sur le bouton « Réinitialiser le mot de passe », copiez et collez 
    l'URL ci-dessous dans votre navigateur Web: <a href='" . $this->route . "'>" . $this->route . "</a>";
	}
}
