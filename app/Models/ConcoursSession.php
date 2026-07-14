<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class ConcoursSession extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'date_debut_depot' => 'date',
        'date_fin_depot' => 'date',
        'date_epreuve' => 'date',
        'date_publication_resultats' => 'date',
        'avec_epreuve_ecrite' => 'boolean',
        'est_publiee' => 'boolean',
    ];

    public function anneeScolaire(): BelongsTo
    {
        return $this->belongsTo(AnneeScolaire::class);
    }

    public function candidatures(): HasMany
    {
        return $this->hasMany(Candidature::class);
    }

    public function matieres(): HasMany
    {
        return $this->hasMany(ConcoursSessionMatiere::class);
    }

    public function scopeOuverte($query)
    {
        return $query->where('statut', 'ouvert');
    }
}
