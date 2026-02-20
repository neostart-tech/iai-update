<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoursPresence extends Model
{
    protected $guarded = ['id'];
    protected $table="presences";

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

   
}
