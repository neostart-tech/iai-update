<?php

namespace App\Models;

use App\Traits\Routing\GenerateUniqueSlugTrait;
use App\Traits\Routing\ModelsSlugKeyTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UvValidation extends Model
{
    use GenerateUniqueSlugTrait, ModelsSlugKeyTrait;
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'moyenne' => 'decimal:2',
        'note_devoir' => 'decimal:2',
        'note_examen' => 'decimal:2',
        'coefficient' => 'integer',
        'credit_obtenu' => 'integer',
        'validee' => 'boolean'
    ];


    public function hasComplexSlug(): bool
    {
        return true;
    }

    public function releveNote()
    {
        return $this->belongsTo(ReleveNote::class);
    }


    public function etudiant(): BelongsTo
    {
        return $this->belongsTo(Etudiant::class);
    }

    public function uniteValeur(): BelongsTo
    {
        return $this->belongsTo(UniteValeur::class);
    }

    public function anneeScolaire(): BelongsTo
    {
        return $this->belongsTo(AnneeScolaire::class);
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(Periode::class);
    }
}
