<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CoursPresence extends Model
{
    protected $table = "presences";
    
    protected $fillable = [
        'emploi_du_temps_id',
        'seance_id',
        'etudiant_id',
        'date',
        'heure_arrivee',
        'heure_depart',
        'minutes_retard',
        'statut',
        'statut_detaille',
        'commentaire',
        'participation',
        'attitude',
        'observations_comportement',
        'points_attention',
        'points_positifs',
        'a_signalement',
        'a_remonter_conseil',
        'notification_envoyee',
        'notification_envoyee_le',
        'parent_informe',
        'parent_informe_le',
        'justificatif_id',
        'needs_validation',
        'validated_by',
        'validated_at',
        'motif_refus',
        'sanction',
        'metadata',
        'source',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'date' => 'date',
        'heure_arrivee' => 'string',
        'heure_depart' => 'string',
        'minutes_retard' => 'integer',
        'needs_validation' => 'boolean',
        'validated_at' => 'datetime',
        'a_signalement' => 'boolean',
        'a_remonter_conseil' => 'boolean',
        'notification_envoyee' => 'boolean',
        'parent_informe' => 'boolean',
        'points_attention' => 'array',
        'points_positifs' => 'array',
        'metadata' => 'array'
    ];

    /**
     * Relation avec le cours (emploi du temps)
     */
    public function cours(): BelongsTo
    {
        return $this->belongsTo(Cours::class);
    }

    /**
     * Relation avec l'emploi du temps
     */
    public function emploi(): BelongsTo
    {
        return $this->belongsTo(EmploiDuTemp::class, 'emploi_du_temps_id');
    }

    /**
     * Relation avec la séance (nouveau)
     */
    public function seance(): BelongsTo
    {
        return $this->belongsTo(Seance::class, 'seance_id');
    }

    /**
     * Relation avec l'étudiant
     */
    public function etudiant(): BelongsTo
    {
        return $this->belongsTo(Etudiant::class);
    }

    /**
     * Relation avec le justificatif
     */
    public function justificatif(): BelongsTo
    {
        return $this->belongsTo(Justificatif::class);
    }

    /**
     * Relation avec le validateur
     */
    public function validateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    /**
     * Relation avec les comportements détaillés
     */
    public function comportements(): HasMany
    {
        return $this->hasMany(Comportement::class, 'presence_id');
    }


    /**
     * Relation avec l'historique
     */
    public function historique(): HasMany
    {
        return $this->hasMany(HistoriquePresence::class, 'presence_id');
    }

    /**
     * Vérifier si la présence nécessite une validation
     */
    public function needsValidation(): bool
    {
        return $this->needs_validation || $this->validation_statut === 'en_attente';
    }

    /**
     * Vérifier si la présence est validée
     */
    public function estValidee(): bool
    {
        return in_array($this->validation_statut, ['valide_auto', 'valide_manuel']);
    }

    /**
     * Obtenir le libellé du statut
     */
    public function getStatutLibelleAttribute(): string
    {
        $statuts = [
            'present' => 'Présent',
            'absent' => 'Absent',
            'retard' => 'Retard',
            'retard_justifie' => 'Retard justifié',
            'absent_justifie' => 'Absence justifiée',
            'dispense' => 'Dispensé',
            'exclu_temporairement' => 'Exclu temporairement',
            'malade' => 'Malade',
            'sortie_anticipee' => 'Sortie anticipée'
        ];
        
        return $statuts[$this->statut] ?? $this->statut;
    }

    /**
     * Obtenir la couleur associée au statut
     */
    public function getStatutCouleurAttribute(): string
    {
        $couleurs = [
            'present' => 'green',
            'absent' => 'red',
            'retard' => 'yellow',
            'retard_justifie' => 'orange',
            'absent_justifie' => 'orange',
            'dispense' => 'blue',
            'exclu_temporairement' => 'purple',
            'malade' => 'gray',
            'sortie_anticipee' => 'indigo'
        ];
        
        return $couleurs[$this->statut] ?? 'gray';
    }

    /**
     * Calculer les minutes de retard automatiquement
     */
    public function calculerMinutesRetard(): ?int
    {
        if (!$this->heure_arrivee || !$this->seance || !$this->seance->heure_debut_prevue) {
            return null;
        }
        
        $arrivee = strtotime($this->heure_arrivee);
        $debut = strtotime($this->seance->heure_debut_prevue);
        
        $retard = max(0, ($arrivee - $debut) / 60);
        
        return round($retard);
    }
}