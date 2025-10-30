<?php

namespace App\Mail\Candidatures;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\{Content, Envelope};
use Illuminate\Queue\SerializesModels;
use App\Helpers\ConfigHelper as AppGetters;

class CandidatureAdmisMail extends Mailable
{
	use Queueable, SerializesModels;

	public function __construct(private readonly string $greeting)
	{
	}

	public function envelope(): Envelope
	{
		return new Envelope(
			subject: 'Résultats du concours '. AppGetters::getAppName(),
		);
	}

	public function content(): Content
	{
		return new Content(
			view: 'mails.candidatures.depot',
			with: [
				'mailTitle' => 'Résultats du concours '. AppGetters::getAppName(),
				'mailContent' => $this->getMainContent(),
				'buttonText' => 'Procéder à l\'inscription définitive',
				'buttonHref' => route('officiel.my-space.constitution'),
			]
		);
	}

	private function getMainContent(): string
	{
		return $this->greeting . ",<br>		
		Félicitations ! Nous avons le plaisir de vous annoncer que vous avez été admis(e) à 
		" . AppGetters::getAppName() ." ". "après votre réussite au concours d'admission. <br>
		
		Votre passion, votre dévouement et vos compétences ont brillé lors du concours, et nous sommes très enthousiastes à 
		l'idée de vous compter parmi nos nouveaux étudiants. <br>
		
		<strong>Prochaine étape :</strong> Pour finaliser votre admission, vous devez maintenant procéder à votre inscription définitive.
		Cliquez sur le bouton ci-dessous pour constituer votre dossier et compléter les formalités d'inscription. <br><br>
		
		Cette étape est obligatoire pour confirmer votre place au sein de notre établissement. <br>

		Bienvenue dans la famille " . AppGetters::getAppName() . " ! <br>
	
		Avec nos sincères félicitations, <br>
		
		" . AppGetters::getAppName() . ".";
	}
}
