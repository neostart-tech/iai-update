<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnseignantPresence extends Model
{
    protected $table = 'enseignant_presences';

    protected $fillable = [
        'emploi_du_temps_id',
        'enseignant_id',
        'statut',
        'commentaire',
    ];

    public function enseignant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enseignant_id');
    }
}
