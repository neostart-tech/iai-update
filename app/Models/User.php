<?php

namespace App\Models;

use App\Enums\GenreEnum;
use App\Notifications\admins\PasswordResetLinkSentNotification;
use App\Traits\Routing\{GenerateUniqueSlugTrait, ModelsSlugKeyTrait};
use App\Traits\UserIdentityTrait;
use Illuminate\Database\Eloquent\{Builder, Collection};
use Illuminate\Database\Eloquent\Relations\{BelongsToMany, HasMany, HasManyThrough, MorphMany};
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection as SupportCollection;

/**
 * @method static self create(array $attributes)
 * @property Collection<array-key, Role> $roles
 * @property Collection<array-key, Permission> $permissions
 * @property Collection<array-key, EmploiDuTemp> $emploiDuTemps
 * @property Collection<array-key, FicheDePresence> $fiches
 * @property Collection<array-key, UniteValeur> $matieres
 */
class User extends Authenticatable
{
	use Notifiable, GenerateUniqueSlugTrait, ModelsSlugKeyTrait, UserIdentityTrait;

	public function hasComplexSlug(): bool
	{
		return true;
	}

	public static array $enseignantRolesId = [2];

	protected $fillable = [
		'nom',
		'prenom',
		'password',
		'email',
		'genre',
		'email_validated_at',
		'image',
		'matricule',
		'remember_token',
		'grade_id',
		'biographie',
		'annee_admission',
		'slug',
		'tel',
		'slug',
		'nom_jeune_fille',
		'date_naissance',
		'lieu_naissance',
		'nationalite',
		'group_id',
		'supervisor_type',
		'supervisor_notes',
	];

	protected $hidden = [
		'password',
		'remember_token',
	];

	protected $casts = [
		'email_verified_at' => 'datetime',
		'genre' => GenreEnum::class
	];

	public function roles(): BelongsToMany
	{
		return $this->belongsToMany(Role::class)->using(RoleUser::class);
	}

	public function permissions(): HasManyThrough
	{
		return $this->hasManyThrough(Permission::class, Role::class);
	}

	public function rolesId(): SupportCollection
	{
		return $this->roles->count() > 0 ? $this->roles->pluck('id') : collect();
	}

   


	/**
	 * @param ...$roles
	 * @return bool
	 */
	public function hasRoles(...$roles): bool
	{
		$true = 0;
		$rolesCollection = $this->rolesId();
		foreach ($roles as $role) {
			if ($rolesCollection->contains($role)) {
				$true++;
			}
		}
		return $true === count($roles);
	}

	public function hasAnyRoles(...$roles): bool
	{
		foreach ($roles as $role) {
			if ($this->rolesId()->contains($role)) {
				return true;
			}
		}
		return false;
	}

	public static function enseignants(): Builder
	{
		return static::query()->whereRelation('roles', fn(Builder $builder) => $builder->whereIn('role_id', static::$enseignantRolesId));
	}

	public static function surveillants(): Builder
	{
		return static::query()->whereIn('supervisor_type', ['interne', 'externe']);
	}

	public static function surveillantsInternes(): Builder
	{
		return static::query()->where('supervisor_type', 'interne');
	}

	public static function surveillantsExternes(): Builder
	{
		return static::query()->where('supervisor_type', 'externe');
	}

	public function isSurveillant(): bool
	{
		return in_array($this->supervisor_type, ['interne', 'externe']);
	}

	/**
	 * Accesseur pour afficher le nom en majuscule
	 */
	public function getNomCompletAttribute(): string
	{
		return strtoupper($this->nom) . ' ' . $this->prenom;
	}

	/**
	 * Accesseur pour le nom en majuscule seul
	 */
	public function getNomUpperAttribute(): string
	{
		return strtoupper($this->nom);
	}

	public function emploiDuTemps(): MorphMany
	{
		return $this->morphMany(EmploiDuTemp::class, 'owner');
	}

	public function fiches(): BelongsToMany
	{
		return $this->BelongsToMany(FicheDePresence::class);
	}

	public function unitValeurs(): BelongsToMany
	{
		return $this->BelongsToMany(UniteValeur::class)->using(UserUniteValeur::class);
	}

	public function matieres(): BelongsToMany
	{
		return $this->unitValeurs();
	}

	public function userUniteValeurs(): HasMany
	{
		return $this->hasMany(UserUniteValeur::class);
	}

	public function sendPasswordResetNotification($token)
	{
		$this->notifyNow(new PasswordResetLinkSentNotification($token, $this->getAttribute('email')));
	}
}
