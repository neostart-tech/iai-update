<?php

namespace App\Models;

use App\Models\Scopes\CurrentAnneeScolaireScope;
use App\Traits\GetAnneeScolaireModelTrait;
use App\Traits\Routing\{GenerateUniqueSlugTrait, ModelsSlugKeyTrait};
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\{Collection, Model, Relations\BelongsTo, Relations\BelongsToMany};
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static self create(array $attributes)
 * @property User enseignant
 * @property Collection<array-key, Note> notes
 * @property Collection<array-key, EmploiDuTemp> emploiDuTemps
 * @property UniteEnseignement $uniteEnseignement
 * @property UniteEnseignement $ue
 * @property Filiere $filiere
 */
#[ScopedBy([CurrentAnneeScolaireScope::class])]
class UniteValeur extends Model
{
	use GenerateUniqueSlugTrait, ModelsSlugKeyTrait, GetAnneeScolaireModelTrait;

	public $timestamps = false;

	protected $guarded = false;

	public function notes(): HasMany
	{
		return $this->hasMany(Note::class);
	}

	public function emploiDuTemps(): HasMany
	{
		return $this->hasMany(EmploiDuTemp::class, 'uv_id');
	}

	// public function enseignants(): BelongsToMany
	// {
	// 	return $this->belongsToMany(User::class, 'enseignant_id')->using(UserUniteValeur::class);
	// }

	public function enseignants(): BelongsToMany
	{
		return $this->belongsToMany(User::class, 'user_unite_valeur', 'unite_valeur_id', 'user_id', 'annee_scolaire_id')->using(UserUniteValeur::class);
	}


	public function enseignantsMatieres(): HasMany
	{
		return $this->hasMany(UserUniteValeur::class);
	}

	public function uniteEnseignement(): BelongsTo
	{
		return $this->belongsTo(UniteEnseignement::class);
	}

	public function ue(): BelongsTo
	{
		return $this->uniteEnseignement();
	}
	public function user()
    {
        return $this->belongsToMany(User::class, 'user_unite_valeur', 'unite_valeur_id', 'user_id');
    
    }

	public function evaluations(): HasMany
	{
		return $this->hasMany(Evaluation::class);
	}

    public function weightings(): HasMany
    {
        return $this->hasMany(UVWeighting::class, 'unite_valeur_id');
    }


}
