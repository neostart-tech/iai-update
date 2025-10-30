<?php

namespace App\Mail\Candidatures;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\{Content, Envelope};
use Illuminate\Queue\SerializesModels;
use App\Helpers\ConfigHelper as AppGetters;

class CandidatureRecaleMail extends Mailable
{
	use Queueable, SerializesModels;

	public function __construct(private readonly string $greeting)
	{
	}

	public function envelope(): Envelope
	{
		return new Envelope(
			subject: 'Résultats du concours '.' '.AppGetters::getAppName(),
		);
	}

	public function content(): Content
	{
		return new Content(
			view: 'mails.candidatures.depot',
			with: [
				'mailTitle' => 'Résultats du concours '.' '. AppGetters::getAppName(),
				'mailContent' => $this->getMainContent(),
				'buttonText' => 'Cliquez-ici pour accéder à votre compte',
				'buttonHref' => route('officiel.login'),
			]
		);
	}

	private function getMainContent(): string
	{
		return $this->greeting . ",<br>		
		Nous vous remercions d'avoir participé au concours d'admission à l'Université " .' '.AppGetters::getAppName() ." ". " <br>
		
		Après une évaluation minutieuse de toutes les candidatures, nous regrettons de vous informer que vous n'avez pas été
		sélectionné(e) pour cette année. La compétition a été particulièrement intense et de nombreux candidats talentueux
		ont postulé. <br>
		
		Nous comprenons que cette nouvelle peut être décevante. Nous tenons à souligner que votre candidature a été 
		appréciée et que nous vous encourageons à continuer à poursuivre vos objectifs académiques et professionnels. <br>
		
		Nous vous souhaitons beaucoup de succès dans vos projets futurs. <br>
		
		Cordialement, <br>
		
		" .' '.AppGetters::getAppName() . ".";
	}
}
