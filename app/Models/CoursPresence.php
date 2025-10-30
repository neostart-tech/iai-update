<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoursPresence extends Model
{
    protected $fillable = [
        'cours_id',
        'emploi_du_temps_id',
        'etudiant_id',
        'statut',
        'commentaire',
        'needs_validation',
        'validated_by',
        'validated_at',
        'sanction_id',
    ];

    protected $casts = [
        'needs_validation' => 'boolean',
        'validated_at' => 'datetime',
    ];

    public function cours(): BelongsTo
    {
        return $this->belongsTo(Cours::class);
    }

    public function etudiant(): BelongsTo
    {
        return $this->belongsTo(Etudiant::class);
    }

    public function emploi(): BelongsTo
    {
        return $this->belongsTo(EmploiDuTemp::class, 'emploi_du_temps_id');
    }

    public function sanction(): BelongsTo
    {
        return $this->belongsTo(Sanction::class);
    }
}
