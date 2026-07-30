<?php

namespace App\Models;

use App\Models\Scopes\CurrentAnneeScolaireScope;
use App\Traits\LogsActivityWithDefaults;
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
	use LogsActivityWithDefaults;

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

	public function reclamations()
{
    return $this->hasMany(Reclamation::class);
}

public function historiqueModifications()
{
    return $this->hasMany(NoteHistorique::class);
}

public function aDesReclamationsEnCours()
{
    return $this->reclamations()
        ->where('statut', 'en_attente')
        ->exists();
}



}
