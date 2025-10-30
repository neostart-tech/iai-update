<?php

namespace App\Models;

use App\Models\Scopes\CurrentAnneeScolaireScope;
use App\Traits\Routing\{GenerateUniqueSlugTrait, ModelsSlugKeyTrait};
use Illuminate\Database\Eloquent\{Attributes\ScopedBy, Collection, Model};
use Illuminate\Database\Eloquent\Relations\{HasMany, HasManyThrough};

/**
 * @method static self create(array $attributes)
 * @property Collection<array-key, Grade> grades
 */
#[ScopedBy([CurrentAnneeScolaireScope::class])]
class Filiere extends Model
{
	use GenerateUniqueSlugTrait, ModelsSlugKeyTrait;

	public $timestamps = false;

	protected $guarded = false;

	public function grades(): HasMany
	{
		return $this->hasMany(Grade::class);
	}

	public function groups(): HasMany
	{
		return $this->hasMany(Group::class);
	}

	public function etudiants(): HasManyThrough
	{
		return $this->hasManyThrough(EtudiantGroup::class, Group::class);
	}
}
