<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use App\Models\Etudiant;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RelanceTrancheMail extends Mailable
{
  use Queueable, SerializesModels;

	public function __construct(private readonly string $greeting)
	{
	}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Relance de paiement - Tranche non soldée',
        );
    }
    
    /**
     * Get the message content definition.
     */
   
	public function content(): Content
	{
		return new Content(
		   view: 'mails.relance_tranche',
			with: [
				'mailTitle' => 'Relance de paiement des frais de scolarité',
				'mailContent' => $this->getMainContent(),
				'buttonText' => 'Cliquez-ici pour accéder à votre compte',
				'buttonHref' => route('officiel.login'),
			]
		);
	}

	
   private function getMainContent(): string
	{
		return $this->greeting .
			"Nous vous envoyons ce message pour vous inviter a soldé votre frais de scolarité
		";
	}
	
}
