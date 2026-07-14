<?php

namespace App\Mail\Candidatures;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\{Content, Envelope};
use Illuminate\Queue\SerializesModels;
use App\Helpers\ConfigHelper as AppGetters;

class CandidatureRectificationMail extends Mailable
{
	use Queueable, SerializesModels;

	public function __construct(private readonly string $greeting, private readonly string $motif)
	{
	}

	public function envelope(): Envelope
	{
		return new Envelope(
			subject: 'Demande de rectification de votre dossier',
		);
	}

	public function content(): Content
	{
		return new Content(
			view: 'mails.candidatures.valide',
			with: [
				'mailTitle' => 'Rectification requise',
				'mailContent' => $this->getMainContent(),
				'buttonText' => 'Modifier mon dossier',
				'buttonHref' => rtrim(env('FRONTEND_URL', 'http://localhost:3000'), '/') . '/candidat/login',
			]
		);
	}

	private function getMainContent(): string
	{
		return $this->greeting .
			". L'administration a examiné votre dossier et a demandé une rectification pour le motif suivant :<br><br><strong>" . $this->motif . "</strong><br><br>
			Merci de vous connecter au plus vite à votre espace candidat pour apporter les modifications demandées.
		";
	}
}
