<?php

namespace App\Models;

use App\Models\Scopes\CurrentAnneeScolaireScope;
use App\Traits\GetAnneeScolaireModelTrait;
use App\Traits\Routing\{GenerateUniqueSlugTrait, ModelsSlugKeyTrait};
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\{Builder, Collection, Model};
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};


/**
 * @method static self create(array $attributes)
 * @method static Builder orderBy(string $column)
 * @method static Builder orderByDesc(string $column)
 * @property mixed $name Periode periode
 * @property mixed $name Collection<array-key, UniteValeur> uniteDeValeurs
 */
#[ScopedBy([CurrentAnneeScolaireScope::class])]
class UniteEnseignement extends Model
{
	use GenerateUniqueSlugTrait, ModelsSlugKeyTrait, GetAnneeScolaireModelTrait;

	public $timestamps = false;

	protected $guarded = false;

	public function uniteDeValeurs(): HasMany
	{
		return $this->hasMany(UniteValeur::class);
	}

	public function periode(): BelongsTo
	{
		return $this->belongsTo(Periode::class);
	}

	public function filiere(): BelongsTo
	{
		return $this->belongsTo(Filiere::class);
	}

	public function gratifications(): HasMany
	{
		return $this->hasMany(Gratification::class);
	}
}
