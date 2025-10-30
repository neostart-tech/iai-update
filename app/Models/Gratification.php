<?php

namespace App\Models;

use App\Models\Scopes\CurrentAnneeScolaireScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property Etudiant $etudiant
 * @property UniteEnseignement $uniteEnseignement
 * @property User $approuvateurUser
 */
#[ScopedBy(CurrentAnneeScolaireScope::class)]
class Gratification extends Model
{
    protected $guarded = false;

    protected $casts = [
        'validee' => 'boolean',
        'date_approbation' => 'datetime',
    ];

    public function etudiant(): BelongsTo
    {
        return $this->belongsTo(Etudiant::class);
    }

    public function uniteEnseignement(): BelongsTo
    {
        return $this->belongsTo(UniteEnseignement::class);
    }

    public function approuvateurUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approuvee_par');
    }

    public function anneeScolaire(): BelongsTo
    {
        return $this->belongsTo(AnneeScolaire::class);
    }
}