<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class EnseignantPresence extends Model
{
    protected $table = 'enseignant_presences';

    protected $fillable = [
        'emploi_du_temps_id',
        'date_cours',             
        'heure_debut_prevue',     
        'heure_fin_prevue',         
        'enseignant_id',
        'statut',                 
        'heure_arrivee',
        'heure_depart',
        'heure_depart_reelle',      
        'duree_reelle_minutes',    
        'duree_calculee_minutes',  
        'type_pointage',            
        'arrivee_enregistree_at',
        'depart_enregistree_at',
        'est_termine',              
        'meta_data',            
        'commentaire',
    ];

    /**
     * Conversion automatique des types
     */
    protected $casts = [
        'arrivee_enregistree_at' => 'datetime',
        'depart_enregistree_at' => 'datetime',
        'est_termine' => 'boolean',
        'meta_data' => 'array',
        'date_cours' => 'date',
        'heure_debut_prevue' => 'string',
        'heure_fin_prevue' => 'string',
    ];

    /**
     * Attributs calculés automatiquement
     */
    protected $appends = [
        'duree_reelle_heures',
        'duree_calculee_heures',
        'est_aujourdhui',
    ];

    /**
     * Relation avec l'emploi du temps
     */
    public function emploiDuTemps(): BelongsTo
    {
        return $this->belongsTo(EmploiDuTemp::class, 'emploi_du_temps_id');
    }

    /**
     * Relation avec l'enseignant (User)
     */
    public function enseignant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enseignant_id');
    }

    /**
     * Getter: convertit les minutes en heures pour l'affichage
     */
    public function getDureeReelleHeuresAttribute(): float
    {
        return round($this->duree_reelle_minutes / 60, 2);
    }

    /**
     * Getter: convertit les minutes en heures pour l'affichage
     */
    public function getDureeCalculeeHeuresAttribute(): float
    {
        return round($this->duree_calculee_minutes / 60, 2);
    }

    /**
     * Getter: vérifie si c'est aujourd'hui
     */
    public function getEstAujourdhuiAttribute(): bool
    {
        return $this->date_cours && Carbon::parse($this->date_cours)->isToday();
    }

    /**
     * Scope: filtre par date
     */
    public function scopePourDate($query, $date)
    {
        return $query->whereDate('date_cours', $date);
    }

    /**
     * Scope: présences en cours (non terminées)
     */
    public function scopeEnCours($query)
    {
        return $query->where('est_termine', false);
    }

    /**
     * Scope: présences terminées
     */
    public function scopeTerminees($query)
    {
        return $query->where('est_termine', true);
    }

    /**
     * Scope: pour un enseignant spécifique
     */
    public function scopePourEnseignant($query, $enseignantId)
    {
        return $query->where('enseignant_id', $enseignantId);
    }
}