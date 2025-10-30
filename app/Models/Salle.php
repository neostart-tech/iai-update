<?php

namespace App\Models;

use App\Traits\Routing\{GenerateUniqueSlugTrait, ModelsSlugKeyTrait};
use Illuminate\Database\Eloquent\{Collection, Model};
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property Collection<array-key, EmploiDuTemp>|null emploiDuTemps
 */
class Salle extends Model
{
	use GenerateUniqueSlugTrait, ModelsSlugKeyTrait;

	public $timestamps = false;

	protected $fillable = [
		'nom',
		'effectif',
		'annee_scolaire_id'
	];

	public function emploiDuTemps(): HasMany
	{
		return $this->hasMany(EmploiDuTemp::class);
	}
}
