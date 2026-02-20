<?php
// app/Mail/Externals/DepotCandidatureSentMail.php

namespace App\Mail\Externals;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Helpers\ConfigHelper as AppGetters;
use Illuminate\Support\Facades\Storage;

class DepotCandidatureSentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        private readonly string $title,
        private readonly ?string $filePath = null  // Le CV uploadé
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmation de dépôt de candidature',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.base',
            with: [
                'mailTitle' => "Dépôt de candidature",
                'mailContent' => $this->mainContent(),
            ]
        );
    }

    private function mainContent(): string
    {
        $hasAttachment = !empty($this->filePath) && Storage::disk('public')->exists($this->filePath);
        
        $content = "Bonjour, <br> Vous recevez cet e-mail, suite au dépôt de candidature d'un de nos étudiants à votre offre intitulée: <b> {$this->title}</b>. <br>";
        
        if ($hasAttachment) {
            $content .= "Vous trouverez ci-joint le Curriculum Vitae du dit étudiant. <br>";
        }
        
        $content .= "Merci pour votre confiance, <br>
        Cordialement, " . AppGetters::getAppName() . ".";
        
        return $content;
    }

    public function attachments(): array
    {
        // Joindre le CV uploadé s'il existe
        if (!empty($this->filePath) && Storage::disk('public')->exists($this->filePath)) {
            return [
                Attachment::fromStorageDisk('public', $this->filePath)
                    ->as('CV_Candidat.pdf')  // Nom plus générique
                    ->withMime('application/pdf'),
            ];
        }
        
        return [];
    }
}