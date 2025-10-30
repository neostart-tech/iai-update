<?php

namespace App\Traits;

use App\Models\AnneeScolaire;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property AnneeScolaire $anneeScolaire
 */
trait GetAnneeScolaireModelTrait
{
	public function anneeScolaire(): BelongsTo
	{
		/**
		 * @var $this Model
		 */
		return $this->belongsTo(AnneeScolaire::class);
	}
}
