<?php

namespace App\Mail\Candidatures;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\{Content, Envelope};
use Illuminate\Queue\SerializesModels;
use App\Helpers\ConfigHelper as AppGetters;

class CandidatureDepotMail extends Mailable
{
	use Queueable, SerializesModels;

	public function __construct(private readonly string $greeting, private readonly string $email = '', private readonly string $password = 'password')
	{
	}

	public function envelope(): Envelope
	{
		return new Envelope(
			subject: 'Candidature déposée avec succès',
		);
	}

	public function content(): Content
	{
		return new Content(
			view: 'mails.candidatures.depot',
			with: [
				'mailTitle' => 'Dépôt de candidature',
				'mailContent' => $this->getMainContent(),
				'buttonText' => 'Cliquez-ici pour accéder à votre compte',
				'buttonHref' => env('FRONTEND_CANDIDAT_URL', 'http://localhost:3000/candidat/login'),
			]
		);
	}

	private function getMainContent(): string
	{
		return $this->greeting . ". Votre dossier d'inscription en ligne à ".' '.AppGetters::getAppName(). " a été déposé avec succès.<br><br>" .
            "Vos identifiants de connexion :<br>" .
            "Email : <strong>" . $this->email . "</strong><br>" .
            "Mot de passe par défaut : <strong>" . $this->password . "</strong><br><br>" .
			"Connectez-vous à votre compte régulièrement pour suivre l'état d'avancement de votre inscription.";
	}
}
