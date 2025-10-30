<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inscription extends Model
{
    use HasFactory;

    /**
     * Les attributs pouvant être assignés en masse.
     */
    protected $fillable = [
        'numero_table',
        'annee_bac',
        'lettre_motivation',
        'serie',
        'email',
        'phone1',
        'phone2',
        'phone3',
        'tuteur_lieu',
        'accepte',
        'certificat_medical_path',
        'bulletins_lycee_paths',
        'releve_bac1_path',
        'releve_bac2_path',
        'status',
    ];

    /**
     * Les conversions de types.
     */
    protected $casts = [
        'annee_bac' => 'integer',
        'bulletins_lycee_paths' => 'array',
    ];
}
