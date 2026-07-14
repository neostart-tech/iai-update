<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class ConcoursSessionMatiere extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'coefficient' => 'float',
    ];

    public function concoursSession(): BelongsTo
    {
        return $this->belongsTo(ConcoursSession::class);
    }

    public function concoursMatiere(): BelongsTo
    {
        return $this->belongsTo(ConcoursMatiere::class);
    }

    public function niveau(): BelongsTo
    {
        return $this->belongsTo(Niveau::class);
    }

    public function filiere(): BelongsTo
    {
        return $this->belongsTo(Filiere::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(ConcoursNote::class);
    }
}
