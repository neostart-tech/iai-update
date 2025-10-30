<?php

namespace App\Models;

use App\Models\Scopes\CurrentAnneeScolaireScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Relations\Pivot;

#[ScopedBy(CurrentAnneeScolaireScope::class)]
class EtudiantFicheDePresence extends Pivot
{
	public $incrementing = true;

	public $timestamps = false;

	protected $fillable = [
		'etudiant_id',
		'fiche_de_presence_id',
		'was_present',
		'annee_scolaire_id'
	];
}
