<?php

namespace App\Models;

use App\Models\Scopes\CurrentAnneeScolaireScope;
use App\Traits\Routing\{GenerateUniqueSlugTrait, ModelsSlugKeyTrait};
use Illuminate\Database\Eloquent\{Attributes\ScopedBy, Collection, Model};
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static self create(array $attributes)
 * @property Collection<array-key, EmploiDuTemp> emploiDuTemps
 */
#[ScopedBy([CurrentAnneeScolaireScope::class])]
class Grade extends Model
{
	use GenerateUniqueSlugTrait, ModelsSlugKeyTrait;

	public $timestamps = false;

	protected $guarded = false;

	public function emploiDuTemps(): HasMany
	{
		return $this->hasMany(EmploiDuTemp::class);
	}
}
