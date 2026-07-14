<?php

namespace App\Mail\Candidatures;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\{Content, Envelope};
use Illuminate\Queue\SerializesModels;
use App\Helpers\ConfigHelper as AppGetters;

class CandidatureTransmisAcademieMail extends Mailable
{
	use Queueable, SerializesModels;

	public function __construct(private readonly string $greeting)
	{
	}

	public function envelope(): Envelope
	{
		return new Envelope(
			subject: 'Votre dossier est en cours d\'étude académique',
		);
	}

	public function content(): Content
	{
		return new Content(
			view: 'mails.candidatures.valide',
			with: [
				'mailTitle' => 'Dossier transmis à l\'académie',
				'mailContent' => $this->getMainContent(),
				'buttonText' => 'Suivre mon dossier',
				'buttonHref' => rtrim(env('FRONTEND_URL', 'http://localhost:3000'), '/') . '/candidat/login',
			]
		);
	}

	private function getMainContent(): string
	{
		return $this->greeting .
			". Votre dossier de candidature a été vérifié et transmis à l'académie de " . AppGetters::getAppName() . " pour étude.<br><br>
			Vous serez informé(e) par email dès qu'une décision sera prise concernant votre candidature.
		";
	}
}
