<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @method static self create(array $attributes)
 */
class Album extends Model
{
	use HasFactory;

	public $timestamps = false;

	protected $fillable = [
		'lettre',
		'naissance',
		'diplome',
		'nationalite',
		'photo',
		'type_diplome',
		'certificat_medical',
		'coupon',
		'cv',
		'lettre_motivation',
       'bulletins_lycee_paths',
        'releve_bac1_path',
        'releve_bac2_path',
		'owner_id',
		'owner_type',
	];

	public function owner(): MorphTo
	{
		return $this->morphTO();
	}

	
}
