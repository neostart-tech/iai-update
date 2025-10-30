<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Devoir extends Model
{
    use HasFactory;

    protected $fillable = [
        'emploi_du_temps_id',
        'titre',
        'consignes',
        'fichier',
        'date_limite',
        'correction',
    ];
}
