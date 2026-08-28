<?php

namespace App\Mail\Etudiants;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\{Content, Envelope};
use Illuminate\Queue\SerializesModels;
use App\Helpers\ConfigHelper as AppGetters;

class WelcomeEtudiantMail extends Mailable
{
	use Queueable, SerializesModels;

	public function __construct(private readonly string $greeting, private readonly string $email, private readonly string $password = 'password')
	{
	}

	public function envelope(): Envelope
	{
		return new Envelope(
			subject: 'Bienvenue à ' . AppGetters::getAppName(),
		);
	}

	public function content(): Content
	{
		return new Content(
			view: 'mails.etudiants.welcome',
			with: [
				'mailTitle' => 'Création de votre compte Étudiant',
				'mailContent' => $this->getMainContent(),
				'buttonText' => 'Accéder à mon espace',
				'buttonHref' => env('FRONTEND_URL', 'http://localhost:3000') . '/login',
			]
		);
	}

	private function getMainContent(): string
	{
		return $this->greeting . ". Votre compte étudiant à ".' '.AppGetters::getAppName(). " a été créé avec succès par l'administration.<br><br>" .
            "Voici vos identifiants de connexion :<br>" .
            "Email : <strong>" . $this->email . "</strong><br>" .
            "Mot de passe temporaire : <strong>" . $this->password . "</strong><br><br>" .
			"Nous vous invitons à vous connecter et à modifier votre mot de passe dès votre première connexion.";
	}
}
