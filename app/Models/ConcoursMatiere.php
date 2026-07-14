<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConcoursMatiere extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function sessionMatieres(): HasMany
    {
        return $this->hasMany(ConcoursSessionMatiere::class);
    }
}
