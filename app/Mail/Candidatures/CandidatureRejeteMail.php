<?php

namespace App\Mail\Candidatures;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\{Content, Envelope};
use Illuminate\Queue\SerializesModels;
use App\Helpers\ConfigHelper as AppGetters;

class CandidatureRejeteMail extends Mailable
{
	use Queueable, SerializesModels;

	public function __construct(private readonly string $greeting, private readonly string $motif)
	{
	}

	public function envelope(): Envelope
	{
		return new Envelope(
			subject: 'Votre candidature a été rejetée',
		);
	}

	public function content(): Content
	{
		return new Content(
			view: 'mails.candidatures.valide',
			with: [
				'mailTitle' => 'Rejet de candidature',
				'mailContent' => $this->getMainContent(),
				'buttonText' => 'Accéder à votre compte',
				'buttonHref' => env('FRONTEND_CANDIDAT_URL', 'http://localhost:3000/candidat/login'),
			]
		);
	}

	private function getMainContent(): string
	{
		return $this->greeting .
			". Nous sommes au regret de vous informer que votre dossier de candidature a été rejeté pour le motif suivant :<br><br><strong>" . $this->motif . "</strong><br><br>
			Connectez-vous à votre espace numérique pour plus de détails.
		";
	}
}
