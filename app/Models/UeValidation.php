<?php

namespace App\Models;

use App\Traits\Routing\GenerateUniqueSlugTrait;
use App\Traits\Routing\ModelsSlugKeyTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UeValidation extends Model
{
    use GenerateUniqueSlugTrait, ModelsSlugKeyTrait;
    use HasFactory;

    protected $table = "ue_validations";
    protected $guarded = ['id'];



    protected $casts = [
        'moyenne' => 'decimal:2',
        'credit_obtenu' => 'integer',
        'validee' => 'boolean'
    ];

    public function hasComplexSlug(): bool
    {
        return true;
    }


    public function etudiant(): BelongsTo
    {
        return $this->belongsTo(Etudiant::class);
    }

    public function releveNote()
    {
        return $this->belongsTo(ReleveNote::class);
    }

    public function uvValidations()
    {
        return $this->hasMany(UvValidation::class, 'releve_note_id', 'releve_note_id')
            ->whereColumn('unite_enseignement_id', 'unite_enseignement_id');
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
