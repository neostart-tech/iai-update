<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comportement extends Model
{
    protected $fillable = [
        'etudiant_id',
        'presence_id',
        'user_id',
        'type',
        'categorie',
        'libelle',
        'description',
        'intensite',
        'a_communiquer_parents',
        'a_remonter_conseil',
        'traite_le'
    ];

    protected $casts = [
        'intensite' => 'integer',
        'a_communiquer_parents' => 'boolean',
        'a_remonter_conseil' => 'boolean',
        'traite_le' => 'datetime'
    ];

    /**
     * Relation avec l'étudiant
     */
    public function etudiant(): BelongsTo
    {
        return $this->belongsTo(Etudiant::class);
    }

    /**
     * Relation avec la présence
     */
    public function presence(): BelongsTo
    {
        return $this->belongsTo(CoursPresence::class, 'presence_id');
    }

    /**
     * Relation avec l'utilisateur qui a noté
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtenir la couleur selon le type
     */
    public function getCouleurAttribute(): string
    {
        return match($this->type) {
            'positif' => 'green',
            'negatif' => 'red',
            'neutre' => 'gray',
            'alerte' => 'orange',
            default => 'blue'
        };
    }

    /**
     * Obtenir l'icône selon le type
     */
   public function getIconeAttribute(): string
{
    return match($this->type) {
        'positif' => '<svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>',
        
        'negatif' => '<svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>',
        
        'neutre' => '<svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>',
        
        'alerte' => '<svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
        </svg>',
        
        default => '<svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z"></path>
        </svg>'
    };
}
}