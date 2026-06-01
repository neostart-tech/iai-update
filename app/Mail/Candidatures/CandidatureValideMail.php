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
			". Nous avons le plaisir de vous informer que, suite à l'étude approfondie de votre dossier par la commission d'admission, votre candidature pour " . AppGetters::getAppName() . " a été approuvée.
			Connectez-vous régulièrement à votre espace numérique pour suivre les prochaines étapes de votre procédure d'admission.
		";
	}
}
