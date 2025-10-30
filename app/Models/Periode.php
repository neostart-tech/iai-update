<?php

namespace App\Models;

use App\Models\Scopes\CurrentAnneeScolaireScope;
use App\Traits\Routing\{GenerateUniqueSlugTrait, ModelsSlugKeyTrait};
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static self create(array $attributes)
 * @property AnneeScolaire $anneeScolaire
 */
#[ScopedBy([CurrentAnneeScolaireScope::class])]
class Periode extends Model
{
	use GenerateUniqueSlugTrait, ModelsSlugKeyTrait;

	public $timestamps = false;

	protected $guarded = false;

	protected $casts = [
		'debut' => 'datetime',
		'fin' => 'datetime',
	];

	public function hasComplexSlug(): bool
	{
		return true;
	}

	public function anneeScolaire(): BelongsTo
	{
		return $this->belongsTo(AnneeScolaire::class);
	}
}
