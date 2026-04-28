<?php

namespace App\Models;

use App\Models\Scopes\CurrentAnneeScolaireScope;
use App\Traits\Routing\{GenerateUniqueSlugTrait, ModelsSlugKeyTrait};
use Illuminate\Database\Eloquent\{Attributes\ScopedBy, Collection, Model};
use Illuminate\Database\Eloquent\Relations\{HasMany, HasManyThrough};
use Illuminate\Support\Facades\Storage;

/**
 * @method static self create(array $attributes)
 * @property Collection<array-key, Grade> grades
 */
// #[ScopedBy([CurrentAnneeScolaireScope::class])]
class Filiere extends Model
{
	use GenerateUniqueSlugTrait, ModelsSlugKeyTrait;

	public $timestamps = false;

	protected $guarded = false;

	public function grades(): HasMany
	{
		return $this->hasMany(Grade::class);
	}

	// public function groups(): HasMany
	// {
	// 	return $this->hasMany(Group::class);
	// }

	public function etudiants()
	{
		return $this->belongsToMany(Etudiant::class, 'etudiant_group')
			->withPivot('group_id', 'annee_scolaire_id');
	}


	public function anneesScolaires()
    {
        return $this->belongsToMany(
            AnneeScolaire::class,
            'annee_filiere'
        )->withPivot('date_debut', 'date_fin')
         ->withTimestamps();
    }

    /**
     * Date de rentrée réelle de la filière pour une année donnée
     */
    public function dateDebutAnnee(AnneeScolaire $annee): ?string
    {
        $relation = $this->anneesScolaires()
            ->where('annee_scolaire_id', $annee->id)
            ->first();

        if (! $relation) {
            return null;
        }

        return $relation->pivot->date_debut
            ?? $annee->date_debut;
    }

	public function dateFinAnnee(AnneeScolaire $annee): ?string
    {
        $relation = $this->anneesScolaires()
            ->where('annee_scolaire_id', $annee->id)
            ->first();

        if (! $relation) {
            return null;
        }

        return $relation->pivot->date_fin
            ?? $annee->date_fin;
    }


	public function pathImage()
	{
		return asset(Storage::url($this->image));
	}

	public function groups()
	{
		return $this->belongsToMany(
			Group::class,
			'filiere_group'
		);
	}

	public function unitesEnseignements(): HasMany
	{
		return $this->hasMany(UniteEnseignement::class);
	}
}
