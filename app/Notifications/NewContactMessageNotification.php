<?php

namespace App\Notifications;

use App\Models\Contact;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewContactMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $contact;

    /**
     * Create a new notification instance.
     */
    public function __construct(Contact $contact)
    {
        $this->contact = $contact;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Envoi par email et en base de données (pour le dashboard)
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mailContent = "
            <p style='margin-bottom: 15px; font-size: 16px;'>Bonjour <strong>{$notifiable->nom}</strong>,</p>
            <p style='margin-bottom: 20px; font-size: 15px;'>Un nouveau message a été reçu via le formulaire de contact du site.</p>
            
            <div style='background-color: #f9f9f9; padding: 20px; border-radius: 8px; border: 1px solid #eaeaea; margin-bottom: 25px;'>
                <p style='margin: 0 0 10px 0;'><strong>Nom :</strong> {$this->contact->nom}</p>
                <p style='margin: 0 0 10px 0;'><strong>Email :</strong> <a href='mailto:{$this->contact->email}' style='color: #80BF2E; text-decoration: none;'>{$this->contact->email}</a></p>
                <p style='margin: 0 0 10px 0;'><strong>Téléphone :</strong> <a href='tel:{$this->contact->tel}' style='color: #80BF2E; text-decoration: none;'>{$this->contact->tel}</a></p>
                <div style='margin-top: 15px; padding-top: 15px; border-top: 1px dashed #cccccc;'>
                    <p style='margin: 0 0 5px 0;'><strong>Message :</strong></p>
                    <p style='margin: 0; font-style: italic; color: #555555; white-space: pre-wrap;'>{$this->contact->message}</p>
                </div>
            </div>
        ";

        return (new MailMessage)
                    ->subject('Nouveau message de contact - ' . $this->contact->nom)
                    ->view('mails.base', [
                        'mailTitle' => 'Nouveau Contact Reçu',
                        'mailContent' => $mailContent,
                        'buttonText' => 'Voir dans le tableau de bord',
                        'buttonHref' => rtrim(env('FRONTEND_URL', 'http://localhost:3001'), '/') . '/messages',
                        'moreInfo' => 'Merci de traiter cette demande dans les plus brefs délais.'
                    ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'contact_id' => $this->contact->id,
            'nom' => $this->contact->nom,
            'email' => $this->contact->email,
            'message' => 'Nouveau message de contact de ' . $this->contact->nom,
            'type' => 'contact_message'
        ];
    }
}
