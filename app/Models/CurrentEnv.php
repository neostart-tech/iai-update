<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AnneeScolaire;

/**
 * @method static self create(array $attributes)
 */
class CurrentEnv extends Model
{
	public $timestamps = false;

	protected $guarded = false;

	public static function getAnneeScolaireId(): int
	{
		// return (int)static::query()->firstWhere('nom', '=', 'annee_scolaire_id')->getAttribute('valeur');
		return AnneeScolaire::query()->where('active', true)->first()->id;
		
	}

	public static function getPeriodeAnneeScolaireId(): int
	{
		// return (int)static::query()->firstWhere('nom', '=', 'annee_scolaire_id')->getAttribute('valeur');
		return AnneeScolaire::query()->where('active', true)->first()->id;
		
	}
}