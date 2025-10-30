<?php

namespace App\Models;

use App\Enums\TypeProgrammeEnum;
use App\Models\Scopes\CurrentAnneeScolaireScope;
use App\Traits\Routing\{GenerateUniqueSlugTrait, ModelsSlugKeyTrait};
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, MorphOne, MorphTo};

/**
 * @property User|Etudiant $owner
 * @method static self create(array $attributes)
 */
#[ScopedBy([CurrentAnneeScolaireScope::class])]
class EmploiDuTemp extends Model
{
	use ModelsSlugKeyTrait, GenerateUniqueSlugTrait;

	public function hasSlugBaseKeyProvider(): bool
	{
		return false;
	}

	public $timestamps = false;

	protected $fillable = [
		'debut',
		'fin',
		'group_id',
		'salle_id',
		'uv_id',
		'unite_enseignement_id',
		'evenement_id',
		'type_programme',
		'owner_type',
		'owner_id',
		'slug',
		'details',
		'annee_scolaire_id'
	];

	protected $casts = [
		'debut' => 'datetime',
		'fin' => 'datetime',
		'type_programme' => TypeProgrammeEnum::class
	];

	/**
	 * Relation : Une programmation d'emploi du temps peut être liée à une matière
	 * @return BelongsTo
	 */
	public function uv(): BelongsTo
	{
		return $this->belongsTo(UniteValeur::class, 'uv_id');
	}

	/**
	 * Relation : Une programmation d'emploi du temps doit être liée à une salle
	 * @return BelongsTo
	 */
	public function salle(): BelongsTo
	{
		return $this->belongsTo(Salle::class);
	}

	/**
	 * Relation : Une programmation d'emploi du temps peut être liée à un groupe d'étudiants
	 * @return BelongsTo
	 */
	public function group(): BelongsTo
	{
		return $this->belongsTo(Group::class);
	}

	/**
	 * Relation : Une programmation d'emploi du temps (considérée comme un cours) peut avoir une fiche de présence
	 * @return MorphOne
	 */
	public function fiche(): MorphOne
	{
		return $this->morphOne(FicheDePresence::class, 'controllable');
	}

	/**
	 * Relation : Une programmation d'emploi du temps peut soit être liée à un étudiant ou un enseignant
	 * @return MorphTo
	 */
	public function owner(): MorphTo
	{
		return $this->morphTo();
	}

	public function evenement(){
		return $this->belongsTo(EmploiDuTemp::class);
	}

	   public function cours()
    {
        return $this->belongsTo(Cours::class);
    }

    public function cahierTexte()
    {
        return $this->hasOne(CahierTexte::class);
    }
}