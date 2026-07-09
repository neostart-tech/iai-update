<?php

namespace App\Models;

use App\Traits\Routing\{GenerateUniqueSlugTrait, ModelsSlugKeyTrait};
use Illuminate\Database\Eloquent\Model;

class AnneeScolaire extends Model
{
	use GenerateUniqueSlugTrait, ModelsSlugKeyTrait;

	public $timestamps = false;

	protected $guarded = false;

	protected $casts = [
		'active' => 'boolean',
	];

	public function hasComplexSlug(): bool
	{
		return true;
	}

	protected static function booted(): void
	{
		// Chaque année scolaire doit disposer d'une fiche de sélection des candidats
		// dès sa création (par défaut "dossier uniquement"), pour qu'aucune candidature
		// ne soit jamais créée pendant une fenêtre où le mode de l'année est indéterminé.
		static::created(function (AnneeScolaire $anneeScolaire) {
			ConcoursSession::firstOrCreate(
				['annee_scolaire_id' => $anneeScolaire->id],
				[
					'libelle' => 'Sélection des candidats — ' . $anneeScolaire->nom,
					'avec_epreuve_ecrite' => false,
					'statut' => 'brouillon',
				]
			);
		});
	}

	public function concoursSession()
	{
		return $this->hasOne(ConcoursSession::class);
	}

	public function scopeActive($q)
	{
		return $q->where('active', true);
	}
	public static function courante()
	{
		return static::active()->first();
	}

	public function filieres()
	{
		return $this->belongsToMany(
			Filiere::class,
			'annee_filiere'
		)->withPivot('date_rentree')
			->withTimestamps();
	}
}
