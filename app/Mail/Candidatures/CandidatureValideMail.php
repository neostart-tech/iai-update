<?php

namespace App\Mail\Candidatures;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\{Content, Envelope};
use Illuminate\Queue\SerializesModels;
use App\Helpers\ConfigHelper as AppGetters;

class CandidatureValideMail extends Mailable
{
	use Queueable, SerializesModels;

	public function __construct(private readonly string $greeting)
	{
	}

	public function envelope(): Envelope
	{
		return new Envelope(
			subject: 'Candidature Validée',
		);
	}

	public function content(): Content
	{
		return new Content(
			view: 'mails.candidatures.valide',
			with: [
				'mailTitle' => 'Validation de candidature',
				'mailContent' => $this->getMainContent(),
				'buttonText' => 'Cliquez-ici pour accéder à votre compte',
				'buttonHref' => route('officiel.login'),
			]
		);
	}

	private function getMainContent(): string
	{
		return $this->greeting .
			". Votre dossier de candidature pour le concours de sélection de" .' '.AppGetters::getAppName()." "."a été approuvé.
			Connectez-vous à votre compte régulièrement pour suivre l'état d'avancement de votre candidature.
		";
	}
}
