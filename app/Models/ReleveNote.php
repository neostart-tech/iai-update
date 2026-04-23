<?php

namespace App\Models;

use App\Traits\Routing\GenerateUniqueSlugTrait;
use App\Traits\Routing\ModelsSlugKeyTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReleveNote extends Model
{
    use GenerateUniqueSlugTrait, ModelsSlugKeyTrait;
    use HasFactory;
    protected $fillable = [
        'etudiant_id',
        'annee_scolaire_id',
        'periode_id',
        'slug',
        'moyenne_generale',
        'total_credits_valides',
        'total_credits_non_valides',
        'metadata'
    ];

    public function hasSlugBaseKeyProvider(): bool
    {
        return false;
    }

    protected $casts = [
        'moyenne_generale' => 'decimal:2',
        'total_credits_valides' => 'integer',
        'total_credits_non_valides' => 'integer',
        'metadata' => 'json',
        'calcule_le' => 'datetime'
    ];

    public function etudiant(): BelongsTo
    {
        return $this->belongsTo(Etudiant::class);
    }

    public function anneeScolaire(): BelongsTo
    {
        return $this->belongsTo(AnneeScolaire::class);
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(Periode::class);
    }

    // public function ueValidations(): HasMany
    // {
    //     return $this->hasMany(UeValidation::class, 'etudiant_id', 'etudiant_id')
    //         ->where('annee_scolaire_id', $this->annee_scolaire_id)
    //         ->where('periode_id', $this->periode_id);
    // }

    public function ueValidations()
{
    return $this->hasMany(UeValidation::class);
}

public function uvValidations()
{
    return $this->hasMany(UvValidation::class);
}

//     public function ueValidations()
// {
//     return $this->hasMany(UeValidation::class, 'etudiant_id', 'etudiant_id');
// }



    // public function uvValidations(): HasMany
    // {
    //     return $this->hasMany(UvValidation::class, 'etudiant_id', 'etudiant_id')
    //         ->where('annee_scolaire_id', $this->annee_scolaire_id)
    //         ->where('periode_id', $this->periode_id);
    // }
}
