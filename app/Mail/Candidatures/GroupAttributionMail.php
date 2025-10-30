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

	public function __construct(private readonly string $greeting)
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
				'buttonHref' => route('etudiants.auth.login'),
				'buttonText' => 'Cliquez-ici pour accéder à votre espace'
			]
		);
	}

	private function mainContent(): string
	{
		return $this->greeting . ". Nous avons le plaisir de vous annoncer que suite à votre admission à " .' '.AppGetters::getAppName()." "."
				 nous vous avons crée un compte étudiant. Cliquez sur le lien suivant pour accéder à votre espace et explorer votre compte d'étudiant.Votre mot de passe par défaut est password";
	}
}
