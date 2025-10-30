<?php

namespace App\Models;

use App\Models\Scopes\CurrentAnneeScolaireScope;
use App\Traits\Routing\{GenerateUniqueSlugTrait, ModelsSlugKeyTrait};
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\{Collection, Model};
use Illuminate\Database\Eloquent\Relations\{BelongsToMany, MorphTo};

/**
 * @method static self create(array $attributes)
 * @property Evaluation|EmploiDuTemp $controllable
 * @property Collection<array-key, User> $surveillants
 * @property Collection<array-key, Etudiant> $etudiants
 */
#[ScopedBy([CurrentAnneeScolaireScope::class])]
class FicheDePresence extends Model
{
	use GenerateUniqueSlugTrait, ModelsSlugKeyTrait;

	public function hasSlugBaseKeyProvider(): bool
	{
		return false;
	}

	public $timestamps = false;

	protected $guarded = false;

	protected $fillable = [
		'surveillant_1_id',
		'surveillant_2_id',
		'controllable_type',
		'controllable_id',
		'annee_scolaire_id',
		'submitted',
		'processed'
	];

	public function controllable(): MorphTo
	{
		return $this->morphTo();
	}

	public function surveillants(): BelongsToMany
	{
		return $this->belongsToMany(User::class);
	}

	public function etudiants(): BelongsToMany
	{
		return $this->belongsToMany(Etudiant::class)->using(FicheDePresenceUser::class);
	}
}
