<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UvValidation extends Model
{
    use HasFactory;
     protected $fillable = [
        'etudiant_id',
        'unite_valeur_id',
        'annee_scolaire_id',
        'periode_id',
        'moyenne',
        'note_devoir',
        'note_examen',
        'coefficient',
        'credit_obtenu',
        'validee'
    ];

    protected $casts = [
        'moyenne' => 'decimal:2',
        'note_devoir' => 'decimal:2',
        'note_examen' => 'decimal:2',
        'coefficient' => 'integer',
        'credit_obtenu' => 'integer',
        'validee' => 'boolean'
    ];

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
