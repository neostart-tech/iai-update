<?php

namespace App\Mail\Candidatures;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\{Content, Envelope};
use Illuminate\Queue\SerializesModels;
use App\Helpers\ConfigHelper as AppGetters;

class CandidaturePayementMail extends Mailable
{
	use Queueable, SerializesModels;

	public function __construct(private readonly string $greeting, private readonly string $code)
	{
	}

	public function envelope(): Envelope
	{
		return new Envelope(
			subject: 'Payement de la quittance effectué avec succès',
		);
	}

	public function content(): Content
	{
		return new Content(
			view: 'mails.candidatures.depot',
			with: [
				'mailTitle' => 'Payement de la quittance',
				'mailContent' => $this->getMainContent(),
				'buttonText' => 'Accéder à mon espace candidat',
				'buttonHref' => rtrim(env('FRONTEND_URL', 'http://localhost:3000'), '/') . '/candidat/login',
			]
		);
	}

	private function getMainContent(): string
	{
		return $this->greeting . ". Le payement de votre quittance pour la participation au concours de sélection de" .' '.AppGetters::getAppName()." " .
			"a été enregistré avec succès. Votre code pour le concours est le suivant: " . $this->code .
			"<br><br>Connectez-vous à votre espace candidat régulièrement pour suivre l'état d'avancement de votre candidature.
	";
	}
}
