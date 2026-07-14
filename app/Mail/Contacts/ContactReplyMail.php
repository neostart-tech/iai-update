<?php

namespace App\Mail\Contacts;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\{Content, Envelope};
use Illuminate\Queue\SerializesModels;
use App\Helpers\ConfigHelper as AppGetters;

class ContactReplyMail extends Mailable
{
	use Queueable, SerializesModels;

	public function __construct(
		private readonly string $recipientName,
		private readonly string $replyMessage,
		private readonly string $originalMessage,
	) {
	}

	public function envelope(): Envelope
	{
		return new Envelope(
			subject: 'Réponse à votre message - ' . AppGetters::getAppName(),
		);
	}

	public function content(): Content
	{
		return new Content(
			view: 'mails.contacts.reply',
			with: [
				'mailTitle' => 'Réponse à votre message',
				'mailContent' => $this->getMainContent(),
			]
		);
	}

	private function getMainContent(): string
	{
		return '<p>Bonjour ' . e($this->recipientName) . ',</p>'
			. '<p>' . nl2br(e($this->replyMessage)) . '</p>'
			. '<div style="margin-top:20px;padding:12px 16px;background-color:#f9f9f9;border-left:3px solid #ddd;color:#888;font-size:13px;">'
			. '<strong>Votre message initial :</strong><br>'
			. nl2br(e($this->originalMessage))
			. '</div>'
			. '<p style="margin-top:20px;">Bien cordialement,<br>' . e(AppGetters::getAppName()) . '</p>';
	}
}
