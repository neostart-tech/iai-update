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

    public function __construct(
        public Etudiant $etudiant, 
        public string $mailContent,
        public string $moreInfo = '',
        public string $mailTitle = 'Relance de paiement des frais de scolarité',
        public ?string $buttonText = 'Accéder à mon compte',
        public ?string $buttonHref = null
    ) {
        if (!$this->buttonHref) {
            $this->buttonHref = env('FRONTEND_URL', 'http://localhost:3000') . '/login';
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $schoolName = \App\Helpers\ConfigHelper::getAppName();
        return new Envelope(
            subject: "Avis de retard de paiement - $schoolName",
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
				'mailContent' => $this->mailContent,
				'mailTitle' => $this->mailTitle,
				'buttonText' => $this->buttonText,
				'buttonHref' => $this->buttonHref,
                'moreInfo' => $this->moreInfo
			]
		);
	}

	

	
}
