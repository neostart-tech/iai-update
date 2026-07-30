<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CandidatureFieldConfig extends Model
{
    protected $fillable = ['champ_key', 'label', 'obligatoire', 'afficher'];

    protected $casts = [
        'obligatoire' => 'boolean',
        'afficher' => 'boolean',
    ];
}
