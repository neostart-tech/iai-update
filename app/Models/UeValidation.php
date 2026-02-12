<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UeValidation extends Model
{
    use HasFactory;
    protected $fillable = [
        'etudiant_id',
        'unite_enseignement_id',
        'annee_scolaire_id',
        'periode_id',
        'moyenne',
        'credit_obtenu',
        'validee',
        'type_validation'
    ];



    protected $casts = [
        'moyenne' => 'decimal:2',
        'credit_obtenu' => 'integer',
        'validee' => 'boolean'
    ];

    public function etudiant(): BelongsTo
    {
        return $this->belongsTo(Etudiant::class);
    }

    public function uniteEnseignement(): BelongsTo
    {
        return $this->belongsTo(UniteEnseignement::class);
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
