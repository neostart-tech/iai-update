<?php

namespace App\Mail\Candidatures;

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
		$resetUrl = rtrim(env('FRONTEND_CANDIDAT_RESET_URL', 'http://localhost:3000/candidat/reinitialiser-mot-de-passe'), '/');
		$this->route = $resetUrl . '?token=' . $this->token . '&email=' . urlencode($this->email);
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
			view: 'mails.base',
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
		return $this->greeting . ". Vous recevez cet e-mail, car nous avons reçu une demande de réinitialisation du mot de passe de votre compte. <br><br>
		Ce lien de réinitialisation de mot de passe expirera dans 60 minutes. Si vous n'avez pas demandé de réinitialisation du mot de passe, aucune autre action n'est requise.<br><br><br>
		Cordialement,<br><strong>" . AppGetters::getAppName() . "</strong>";
	}

	private function moreInfo(): string
	{
		return "";
	}
}
