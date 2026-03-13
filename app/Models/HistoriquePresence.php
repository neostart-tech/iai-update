<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoriquePresence extends Model
{
    protected $table = 'historique_presences';
    
    protected $fillable = [
        'presence_id',
        'user_id',
        'action',
        'anciennes_valeurs',
        'nouvelles_valeurs',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'anciennes_valeurs' => 'array',
        'nouvelles_valeurs' => 'array'
    ];

    /**
     * Relation avec la présence
     */
    public function presence(): BelongsTo
    {
        return $this->belongsTo(CoursPresence::class, 'presence_id');
    }

    /**
     * Relation avec l'utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtenir les différences entre anciennes et nouvelles valeurs
     */
    public function getDifferencesAttribute(): array
    {
        $ancien = $this->anciennes_valeurs ?? [];
        $nouveau = $this->nouvelles_valeurs ?? [];
        
        $differences = [];
        
        foreach ($nouveau as $key => $value) {
            if (!isset($ancien[$key]) || $ancien[$key] != $value) {
                $differences[$key] = [
                    'ancien' => $ancien[$key] ?? null,
                    'nouveau' => $value
                ];
            }
        }
        
        return $differences;
    }
}