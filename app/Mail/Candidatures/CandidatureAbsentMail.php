<?php

namespace App\Mail\Candidatures;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\{Content, Envelope};
use Illuminate\Queue\SerializesModels;
use App\Helpers\ConfigHelper as AppGetters;

class CandidatureAbsentMail extends Mailable
{
	use Queueable, SerializesModels;

	public function __construct(private readonly string $greeting)
	{
	}

	public function envelope(): Envelope
	{
		return new Envelope(
			subject: 'Participation manquée',
		);
	}

	public function content(): Content
	{
		return new Content(
			view: 'mails.candidatures.depot',
			with: [
				'mailTitle' => 'Participation manquée à une épreuve',
				'mailContent' => $this->getMainContent(),
				'buttonText' => 'Cliquez-ici pour accéder à votre compte',
				'buttonHref' => route('officiel.login'),
			]
		);
	}

	private function getMainContent(): string
	{
		return $this->greeting . ". Nous avons constaté votre absence lors de l'épreuve du concours [Nom du concours] qui a eu lieu le [Date de l'épreuve].
			 Conformément au règlement du concours, cette absence entraîne malheureusement votre élimination. <br>
			Nous comprenons que des imprévus peuvent survenir et nous regrettons sincèrement que vous n'ayez pas pu participer cette 
			 fois-ci. Nous vous encourageons à rester informés de nos futurs concours et événements, où nous espérons vous voir nombreux. <br>
			Merci pour votre compréhension. <br>
			Bien cordialement, <br>
			" . AppGetters::getAppName() . "
	";
	}
}
