<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Configuration;

class NewsletterNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $type;
    public $model;

    /**
     * Create a new message instance.
     *
     * @param string $type 'blog' or 'event'
     * @param mixed $model Blog or Evenement instance
     */
    public function __construct(string $type, $model)
    {
        $this->type = $type;
        $this->model = $model;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $isBlog = $this->type === 'blog';
        $title = $isBlog ? $this->model->title : $this->model->nom;
        
        $excerpt = $isBlog 
            ? mb_strimwidth(html_entity_decode(strip_tags($this->model->content)), 0, 150, '...') 
            : mb_strimwidth(html_entity_decode(strip_tags($this->model->details)), 0, 150, '...');
            
        $slug = $isBlog ? $this->model->slug : ('evt_' . $this->model->id);
        
        // Récupérer l'URL du site web depuis la configuration (sinon utiliser la variable d'environnement)
        $configUrl = Configuration::where('key', 'URL du site web')->value('value');
        $frontendUrl = $configUrl ?: env('FRONTEND_URL', 'http://localhost:3000');
        
        $link = rtrim($frontendUrl, '/') . '/blogs/' . $slug;
        
        $imageUrl = $this->model->image 
            ? asset(\Illuminate\Support\Facades\Storage::url($this->model->image)) 
            : 'https://images.unsplash.com/photo-1498050108023-c5249f4df085';

        $mailTitle = $isBlog ? 'Nouvel Article' : 'Nouvel Événement';
        $subject = $mailTitle . ' : ' . $title;

        // Construire le contenu HTML injecté dans le template de base
        $mailContent = "
            <div style='text-align: center; margin-bottom: 20px;'>
                <img src='{$imageUrl}' alt='" . htmlspecialchars($title) . "' style='max-width: 100%; border-radius: 8px;' />
            </div>
            <h2 style='font-size: 20px; font-weight: 700; color: #0f172a; margin-top: 0; margin-bottom: 15px;'>
                {$title}
            </h2>
            <p style='color: #64748b; font-size: 15px; margin-bottom: 25px;'>
                {$excerpt}
            </p>
        ";

        return $this->subject($subject)
                    ->view('mails.base')
                    ->with([
                        'mailTitle' => $mailTitle,
                        'mailContent' => $mailContent,
                        'buttonText' => 'Découvrir maintenant',
                        'buttonHref' => $link,
                        'moreInfo' => 'Vous recevez cet email car vous êtes inscrit à notre newsletter.'
                    ]);
    }
}
