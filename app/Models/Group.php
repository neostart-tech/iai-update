<?php

namespace App\Models;

use App\Traits\{Routing\GenerateUniqueSlugTrait, Routing\ModelsSlugKeyTrait};
use Illuminate\Database\Eloquent\{Builder, Collection};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, BelongsToMany, HasMany};

/**
 * @method static self create(array $attributes)
 * @property Filiere $filiere
 * @property AnneeScolaire $anneeScolaire
 * @property EmploiDuTemp $emploiDuTemps
 * @property Collection<array-key, Etudiant> $etudiants
 */
class Group extends Model
{
	use ModelsSlugKeyTrait, GenerateUniqueSlugTrait;

	public function hasComplexSlug(): bool
	{
		return true;
	}

	public $timestamps = false;

	protected $fillable = [
		'nom',
		'slug',
		'niveau_id',
		'annee_scolaire_id'
	];

	// public function filiere(): BelongsTo
	// {
	// 	return $this->belongsTo(Filiere::class);
	// }
	public function filieres()
	{
		return $this->belongsToMany(
			Filiere::class,
			'filiere_group'
		);
	}

	public function niveau(): BelongsTo
	{
		return $this->belongsTo(Niveau::class, 'niveau_id');
	}



	public function lesetudiants(): BelongsToMany
	{
		return $this->belongsToMany(Etudiant::class, 'etudiant_group')->using(EtudiantGroup::class)->withPivot('annee_scolaire_id')->orderByDesc('annee_scolaire_id');
	}

	public function etudiants()
{
    return $this->belongsToMany(Etudiant::class, 'etudiant_group')
        ->withPivot(['filiere_id', 'niveau_id', 'annee_scolaire_id']);
}


	public function anneeScolaire(): BelongsTo
	{
		return $this->belongsTo(AnneeScolaire::class);
	}

	public function emploiDuTemps(): HasMany
	{
		return $this->hasMany(EmploiDuTemp::class);
	}

	public function matieresQueryBuilder(): Builder
	{
		return UniteValeur::query()->whereHas('ue', function (Builder $builder) {
			return $builder->where('filiere_id', $this->getAttribute('filiere_id'));
		});
	}

	public function matieres(): Collection
	{
		return $this->matieresQueryBuilder()->get();
	}

	public function uniteValeurs()
	{
		return $this->hasMany(UniteValeur::class);
	}




	//	public function etudiantsNotFromThisGroup()
	//	{
	//		return $this->belongsToMany(Etudiant::class)
	//		->wherePivot()
	//	}
}
