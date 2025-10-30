<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CahierTexte extends Model
{
    protected $fillable = [
        'emploi_du_temps_id',
        'titre',
        'contenu',
        'piece_jointe',
        'created_by_user_id',
        'created_by_role',
        'approved_by_user_id',
        'approved_at',
        'remarks',
        'group_id',
        'niveau_id',
        'etudiant_id',
        'incoherent',
        'incoherence_notes',
    ];

    public function emploiDuTemps()
    {
        return $this->belongsTo(EmploiDuTemp::class);
    }
}
