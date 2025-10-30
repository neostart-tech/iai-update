<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cours extends Model
{
    use HasFactory;

    protected $fillable = [
        'titre',
        'groupe_id',
        'uv_id',
        'date_cours',
        // autres champs nécessaires
    ];

    public function absences()
    {
        return $this->hasMany(Absence::class);
    }
    public function emploisDuTemps()
    {
        return $this->hasMany(EmploiDuTemp::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'groupe_id');
    }

    public function uv(): BelongsTo
    {
        return $this->belongsTo(UniteValeur::class, 'uv_id');
    }
}
