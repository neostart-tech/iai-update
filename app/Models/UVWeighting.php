<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UVWeighting extends Model
{
    public $timestamps = false;
    protected $guarded = false;

    public function uv(): BelongsTo
    {
        return $this->belongsTo(UniteValeur::class, 'unite_valeur_id');
    }

    public function filiere(): BelongsTo
    {
        return $this->belongsTo(Filiere::class);
    }
}
