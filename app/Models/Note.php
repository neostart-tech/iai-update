<?php

namespace App\Models;

use App\Models\Scopes\CurrentAnneeScolaireScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

/**
 * @property UniteValeur $uv
 * @property Etudiant $etudiant
 * @property Evaluation $evaluation
 */
#[ScopedBy([CurrentAnneeScolaireScope::class])]
class Note extends Model
{
	protected $guarded = false;

	public function etudiant(): BelongsTo
	{
		return $this->belongsTo(Etudiant::class);
	}

	public function variations(): HasMany
	{
		return $this->hasMany(NoteVariation::class);
	}

	public function evaluation(): BelongsTo
	{
		return $this->belongsTo(Evaluation::class);
	}

	public function uv(): BelongsTo
	{
		return $this->belongsTo(UniteValeur::class);
	}
}
