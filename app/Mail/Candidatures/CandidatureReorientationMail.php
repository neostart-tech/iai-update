<?php

namespace App\Mail\Candidatures;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\{Content, Envelope};
use Illuminate\Queue\SerializesModels;

class CandidatureReorientationMail extends Mailable
{
	use Queueable, SerializesModels;

	public function __construct(private readonly string $greeting, private readonly string $motif, private readonly string $filiere, private readonly string $niveau)
	{
	}

	public function envelope(): Envelope
	{
		return new Envelope(
			subject: 'Réorientation de votre candidature',
		);
	}

	public function content(): Content
	{
		return new Content(
			view: 'mails.candidatures.valide',
			with: [
				'mailTitle' => 'Réorientation de candidature',
				'mailContent' => $this->getMainContent(),
				// Pas encore d'espace candidat fonctionnel : bouton désactivé pour le moment (à réactiver plus tard).
				// 'buttonText' => 'Accéder à votre compte',
				// 'buttonHref' => env('FRONTEND_CANDIDAT_URL', 'http://localhost:3000/candidat/login'),
			]
		);
	}

	private function getMainContent(): string
	{
		return $this->greeting .
			". Nous vous informons que votre dossier de candidature a été réorienté vers la filière <strong>" . $this->filiere . "</strong> (Niveau: <strong>" . $this->niveau . "</strong>) pour le motif suivant :<br><br><strong>" . $this->motif . "</strong><br><br>
			<!-- Connectez-vous à votre espace numérique pour plus de détails sur la suite de votre procédure. -->
		";
	}
}
