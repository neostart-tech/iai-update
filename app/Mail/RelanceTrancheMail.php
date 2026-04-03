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

    public function __construct(public Etudiant $etudiant, public string $contenu)
    {}

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
				'etudiant' => $this->etudiant,
				'contenu' => $this->contenu,
				'mailTitle' => 'Relance de paiement des frais de scolarité',
				'buttonText' => 'Cliquez-ici pour accéder à votre compte',
				'buttonHref' => route('officiel.login'),
			]
		);
	}

	

	
}
