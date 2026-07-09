<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConcoursNote extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'note' => 'float',
    ];

    public function candidature(): BelongsTo
    {
        return $this->belongsTo(Candidature::class);
    }

    public function concoursSessionMatiere(): BelongsTo
    {
        return $this->belongsTo(ConcoursSessionMatiere::class);
    }
}
