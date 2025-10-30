<?php

namespace App\Models;

use App\Traits\UserIdentityTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Notifications\Notifiable;

/**
 * @method static self create(array $attributes)
 */
class Tuteur extends Model
{
	use HasFactory, UserIdentityTrait, Notifiable;

	public $timestamps = false;

	protected $fillable = [
		'nom',
		'prenom',
		'profession',
		'employeur',
		'email',
		'tel',
		'adresse',
		'fax',
		'bp',
		'owner_id',
		'owner_type',
	];

	public function owner(): MorphTo
	{
		return $this->morphTO();
	}
}
