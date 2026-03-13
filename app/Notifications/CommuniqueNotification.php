<?php
// app/Notifications/CommuniqueNotification.php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class CommuniqueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected array $data;
    protected array $contexte;

    /**
     * @param array $data Les données du communiqué
     * @param array $contexte Comment la personne a été ciblée
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            // Infos du communiqué
            'titre' => $this->data['titre'],
            'contenu' => $this->data['contenu'],
            'date_publication' => $this->data['date_publication'],
            'date_expiration' => $this->data['date_expiration'] ?? null,
            'piece_jointe' => $this->data['piece_jointe'] ?? null,
            'piece_jointe_nom' => $this->data['piece_jointe_nom'] ?? null,
            
            // Qui a envoyé
            'expediteur' => [
                'id' => $this->data['expediteur_id'] ?? null,
                'nom' => $this->data['expediteur_nom'] ?? null,
            ],
            
            // Comment la personne a été ciblée
            'contexte_ciblage' => $this->contexte,
            
            // Pour l'affichage
            'icon' => $this->getIcone(),
            'level' => $this->data['level'] ?? 'info',
            'type' => 'communique',
            
            // Métadonnées
            'created_at' => now()->toDateTimeString(),
        ];
    }

    private function getIcone(): string
    {
        $type = $this->contexte['type_cible'] ?? 'general';
        
        $icones = [
            'etudiant' => '<svg class="pc-icon text-info" width="20" height="20">...</svg>',
            'groupe' => '<svg class="pc-icon text-success" width="20" height="20">...</svg>',
            'niveau' => '<svg class="pc-icon text-warning" width="20" height="20">...</svg>',
            'filiere' => '<svg class="pc-icon text-primary" width="20" height="20">...</svg>',
            'tous' => '<svg class="pc-icon text-danger" width="20" height="20">...</svg>',
            'general' => '<svg class="pc-icon text-secondary" width="20" height="20">...</svg>',
        ];

        return $icones[$type] ?? $icones['general'];
    }
}