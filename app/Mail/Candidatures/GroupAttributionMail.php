<?php

namespace App\Mail\Candidatures;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\{Content, Envelope};
use Illuminate\Queue\SerializesModels;
use App\Helpers\ConfigHelper as AppGetters;

class GroupAttributionMail extends Mailable
{
	use Queueable, SerializesModels;

	public function __construct(
		private readonly string $greeting, 
		private readonly string $email, 
		private readonly string $password,
		private readonly bool $fraisInscriptionPaye = true
	)
	{
	}

	public function envelope(): Envelope
	{
		return new Envelope(
			subject: 'Création d\'espace étudiant',
		);
	}

	public function content(): Content
	{
		return new Content(
			view: 'mails.candidatures.group-attribution',
			with: [
				'mailTitle' => "Création d'espace étudiant",
				'mailContent' => $this->mainContent(),
				'buttonHref' => env('FRONTEND_URL', 'http://localhost:3000') . '/etudiant/login',
				'buttonText' => 'Cliquez-ici pour accéder à votre espace'
			]
		);
	}

	private function mainContent(): string
	{
		$content = $this->greeting . ". Nous avons le plaisir de vous annoncer que suite à votre admission à " .' '.AppGetters::getAppName()." ".',
				 nous vous avons créé un compte étudiant. <br><br>
				 Voici vos identifiants de connexion : <br>
				 <b>Email :</b> ' . $this->email . '<br>
				 <b>Mot de passe :</b> ' . $this->password . '<br><br>';

		if (!$this->fraisInscriptionPaye) {
			$content .= '
			<div style="margin: 20px 0; padding-top: 10px; font-family: Ubuntu, sans-serif;">
				<p style="margin: 0 0 6px 0; font-size: 13px; font-weight: 700; color: #1e293b; text-transform: uppercase; letter-spacing: 0.5px;">
					Information – Règlement des frais d\'inscription
				</p>
				<p style="margin: 0; font-size: 14px; line-height: 22px; color: #475569;">
					Le paiement de vos frais d\'inscription n\'a pas encore été comptabilisé. Afin de vous garantir un accès complet à l\'ensemble des services académiques, veillez à régulariser votre situation financière dans les meilleurs délais.
				</p>
			</div>
			';
		}

		$content .= 'Cliquez sur le lien suivant pour accéder à votre espace académique.';

		return $content;
	}
}
