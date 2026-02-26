<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $token;
    public string $email;

    /**
     * Create a new message instance.
     */
    public function __construct(string $token, string $email)
    {
        $this->token = $token;
        $this->email = $email;
    }

    /**
     * Sujet du mail
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Réinitialisation de votre mot de passe'
        );
    }

    /**
     * Contenu du mail
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.reset-password', 
            with: [
                'token' => $this->token,
                'email' => $this->email,
            ],
        );
    }

    /**
     * Pièces jointes (si besoin)
     */
    public function attachments(): array
    {
        return [];
    }
}