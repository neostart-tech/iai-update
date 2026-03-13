<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Justificatif extends Model
{
    protected $fillable = [
        'etudiant_id',
        'presence_id',
        'type',
        'fichier_path',
        'description',
        'statut',
        'valide_par',
        'valide_le',
        'motif_refus',
        'date_debut_validite',
        'date_fin_validite'
    ];

    protected $casts = [
        'valide_le' => 'datetime',
        'date_debut_validite' => 'date',
        'date_fin_validite' => 'date'
    ];

    /**
     * Relation avec l'étudiant
     */
    public function etudiant(): BelongsTo
    {
        return $this->belongsTo(Etudiant::class);
    }

    /**
     * Relation avec la présence concernée
     */
    public function presence(): BelongsTo
    {
        return $this->belongsTo(CoursPresence::class, 'presence_id');
    }

    /**
     * Relation avec le validateur
     */
    public function validateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'valide_par');
    }

    /**
     * Vérifier si le justificatif est valide
     */
    public function estValide(): bool
    {
        return $this->statut === 'valide';
    }

    /**
     * Vérifier si le justificatif est en attente
     */
    public function estEnAttente(): bool
    {
        return $this->statut === 'en_attente';
    }

    /**
     * Vérifier si le justificatif est expiré
     */
    public function estExpire(): bool
    {
        if (!$this->date_fin_validite) {
            return false;
        }
        
        return now()->startOfDay()->gt($this->date_fin_validite);
    }
}