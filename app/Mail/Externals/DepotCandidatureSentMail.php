<?php

namespace App\Mail\Externals;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Helpers\ConfigHelper as AppGetters;

class DepotCandidatureSentMail extends Mailable
{
	use Queueable, SerializesModels;

	private string $route;

	public function __construct(private readonly string $title, private readonly string $filePath)
	{
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
				'mailTitle' => "Dépôt de candidature",
				'mailContent' => $this->mainContent(),
			]
		);
	}

	private function mainContent(): string
	{
		return "Bonjour, <br> Vous recevez cet e-mail, suite au dépôt de candidature d'un de nos étudiants à votre offre intitulée: <b> {$this->title}</b>. <br>
		Vous trouverez ci-joint le Curriculum Vitae du dit étudiant. <br> Merci pour votre confiance, <br>
    Cordialement, " .' '.AppGetters::getAppName() . ".";
	}

	public function attachments(): array
	{
		return [
			Attachment::fromStorageDisk('public', $this->filePath)->withMime('application/pdf'),
		];
	}
}
