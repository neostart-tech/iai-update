<?php

namespace App\Mail\Candidatures;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\{Content, Envelope};
use Illuminate\Queue\SerializesModels;
use App\Helpers\ConfigHelper as AppGetters;

class GroupAttributionMail extends Mailable
{
	use Queueable, SerializesModels;

	public function __construct(private readonly string $greeting, private readonly string $email, private readonly string $password)
	{
	}

	public function envelope(): Envelope
	{
		return new Envelope(
			subject: 'Création d\'espace étudiant',
		);
	}

	public function content(): Content
	{
		return new Content(
			view: 'mails.candidatures.group-attribution',
			with: [
				'mailTitle' => "Création d'espace étudiant",
				'mailContent' => $this->mainContent(),
				'buttonHref' => env('FRONTEND_URL', 'http://localhost:3000') . '/etudiant/login',
				'buttonText' => 'Cliquez-ici pour accéder à votre espace'
			]
		);
	}

	private function mainContent(): string
	{
		return $this->greeting . ". Nous avons le plaisir de vous annoncer que suite à votre admission à " .' '.AppGetters::getAppName()." ".'
				 nous vous avons créé un compte étudiant. <br><br>
				 Voici vos identifiants de connexion : <br>
				 <b>Email :</b> ' . $this->email . '<br>
				 <b>Mot de passe :</b> ' . $this->password . '<br><br>
				 Cliquez sur le lien suivant pour accéder à votre espace académique.';
	}
}
