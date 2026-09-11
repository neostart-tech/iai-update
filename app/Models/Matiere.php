<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\Routing\GenerateUniqueSlugTrait;
use App\Traits\Routing\ModelsSlugKeyTrait;

class Matiere extends Model
{
    use HasFactory, GenerateUniqueSlugTrait, ModelsSlugKeyTrait;

    public $timestamps = false;

    protected $fillable = [
        'nom',
        'code',
        'slug',
    ];

    /**
     * Les programmations (UniteValeurs) de cette matière.
     */
    public function uniteValeurs(): HasMany
    {
        return $this->hasMany(UniteValeur::class, 'matiere_id');
    }
}
