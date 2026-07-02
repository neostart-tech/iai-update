<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Seance extends Model
{
    protected $fillable = [
        'emploi_du_temps_id',
        'date_seance',
        'heure_debut_prevue',
        'heure_fin_prevue',
        'heure_debut_reelle',
        'heure_fin_reelle',
        'statut',
        'motif_annulation',
        'remplacant_id',
        'salle_reelle_id',
        'notes_seance',
        'metadata',
        'qr_token',
        'qr_expires_at'
    ];

    protected $casts = [
        'date_seance' => 'date',
        'heure_debut_prevue' => 'datetime',
        'heure_fin_prevue' => 'datetime',
        'heure_debut_reelle' => 'datetime',
        'heure_fin_reelle' => 'datetime',
        'metadata' => 'array',
        'qr_expires_at' => 'datetime'
    ];

    public function emploiDuTemps(): BelongsTo
    {
        return $this->belongsTo(EmploiDuTemp::class, 'emploi_du_temps_id');
    }

    /**
     * Relation avec le remplaçant éventuel
     */
    public function remplacant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'remplacant_id');
    }

    /**
     * Relation avec la salle réelle
     */
    public function salleReelle(): BelongsTo
    {
        return $this->belongsTo(Salle::class, 'salle_reelle_id');
    }

    /**
     * Relation avec les présences de cette séance
     */
    public function presences(): HasMany
    {
        return $this->hasMany(CoursPresence::class, 'seance_id');
    }

    /**
     * Vérifier si la séance est terminée
     */
    public function estTerminee(): bool
    {
        return $this->statut === 'termine';
    }

    /**
     * Vérifier si la séance est en cours
     */
    public function estEnCours(): bool
    {
        return $this->statut === 'en_cours';
    }

    /**
     * Vérifier si la séance est annulée
     */
    public function estAnnulee(): bool
    {
        return $this->statut === 'annule';
    }

    /**
     * Obtenir le nombre d'étudiants présents
     */
    public function getNombrePresentsAttribute(): int
    {
        return $this->presences()->where('statut', 'present')->count();
    }

    /**
     * Obtenir le nombre d'absents
     */
    public function getNombreAbsentsAttribute(): int
    {
        return $this->presences()->whereIn('statut', ['absent', 'absent_justifie'])->count();
    }

    /**
     * Obtenir le nombre de retards
     */
    public function getNombreRetardsAttribute(): int
    {
        return $this->presences()->whereIn('statut', ['retard', 'retard_justifie'])->count();
    }
}