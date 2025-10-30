<?php

namespace App\Models;

use App\Enums\GenreEnum;
use App\Models\Scopes\CurrentAnneeScolaireScope;
use App\Notifications\Candidatures\PasswordResetLinkSentNotification;
use App\Traits\UserIdentityTrait;
use App\Traits\Routing\{GenerateUniqueSlugTrait, ModelsSlugKeyTrait};
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, HasOne, MorphOne};
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

/**
 * @method static self create(array $attributes)
 * @property Album album
 * @property Tuteur $tuteur
 * @property ResponsableFrais $responsable
 */
#[ScopedBy(CurrentAnneeScolaireScope::class)]
class Candidature extends Authenticatable
{
	use Notifiable, HasFactory, ModelsSlugKeyTrait, GenerateUniqueSlugTrait, UserIdentityTrait;

	protected $guarded = false;


	protected $casts = [
		'validation_date' => 'datetime',
		'frai_paye_date' => 'datetime',
		'participation_date' => 'datetime',
		'admission_date' => 'datetime',
		'date_naissance' => 'datetime',
		'acceptation_date' => 'datetime',
		'end_accessibility_date' => 'datetime',
		'genre' => GenreEnum::class
	];

	public function album(): MorphOne
	{
		return $this->morphOne(Album::class, 'owner');
	}

	public function tuteur(): MorphOne
	{
		return $this->morphOne(Tuteur::class, 'owner');
	}

	public function responsable(): MorphOne
	{
		return $this->morphOne(ResponsableFrais::class, 'owner');
	}

	/**
	 * Documents multiples (bulletins/relevés) liés à la candidature
	 */
	public function documents(): HasMany
	{
		return $this->hasMany(CandidatureDocument::class);
	}

	public function hasComplexSlug(): bool
	{
		return true;
	}

	protected static function boot()
	{
		parent::boot();

		static::saving(function ($model) {
			$slug = $model->generateUniqueSlug(Str::slug($model->nom . '-' . $model->prenom));
			if ($model->hasComplexSlug()) {
				$slug = uniqid($slug . '-');
			}
			$model->slug = $slug;
		});
	}

	public function etudiant(): BelongsTo
	{
		return $this->belongsTo(Etudiant::class);
	}

	public function sendPasswordResetNotification($token)
	{
		$this->notify(new PasswordResetLinkSentNotification($token, $this->getAttribute('email')));
	}

	public function niveau(){
		return $this->belongsTo(Niveau::class, 'niveau_id');
	}

	public function filiere(){
		return $this->belongsTo(Filiere::class, 'filiere_id');
	}

	public function tranches()
    {
        return $this->hasManyThrough(
            TranchePaiement::class,
            FraisScolarite::class,
            'niveau_id',          
            'frais_scolarite_id', 
            'niveau_id',          
            'id'                 
        );
    }

	
}
