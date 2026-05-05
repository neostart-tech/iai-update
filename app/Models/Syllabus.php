<?php

namespace App\Models;

use App\Traits\Routing\GenerateUniqueSlugTrait;
use App\Traits\Routing\ModelsSlugKeyTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Syllabus extends Model
{
    use GenerateUniqueSlugTrait, ModelsSlugKeyTrait;

    protected $table = 'syllabuses';

    protected $guarded = [];

    protected $casts = [
        'files' => 'array',
    ];

    /**
     * L'UV à laquelle appartient ce syllabus
     */
    public function uniteValeur(): BelongsTo
    {
        return $this->belongsTo(UniteValeur::class, 'unite_valeur_id');
    }

    /**
     * Alias pour rester cohérent avec le reste du projet
     */
    public function uv(): BelongsTo
    {
        return $this->uniteValeur();
    }
}
