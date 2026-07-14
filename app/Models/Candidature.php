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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @method static self create(array $attributes)
 * @property Album $album
 * @property Tuteur $tuteur
 * @property ResponsableFrais $responsable
 */
// #[ScopedBy(CurrentAnneeScolaireScope::class)]
class Candidature extends Authenticatable
{
	use \Laravel\Sanctum\HasApiTokens, Notifiable, HasFactory, ModelsSlugKeyTrait, GenerateUniqueSlugTrait, UserIdentityTrait;

	protected $guarded = false;

	protected $hidden = ['password', 'remember_token'];


	protected $casts = [
		'validation_date' => 'datetime',
		'frai_paye_date' => 'datetime',
		'participation_date' => 'datetime',
		'admission_date' => 'datetime',
		'date_naissance' => 'datetime',
		'acceptation_date' => 'datetime',
		'end_accessibility_date' => 'datetime',
		'transmis_academie_date' => 'datetime',
		'genre' => GenreEnum::class,
		'transmis_academie' => 'boolean',
		'dossier_valide' => 'boolean',
		'frais_paye' => 'boolean',
		'participation' => 'boolean',
		'admission' => 'boolean',
		'rectification_expected' => 'boolean',
	];

	public function album(): MorphOne
	{
		return $this->morphOne(Album::class, 'owner');
	}

	public function submittedDocuments(): \Illuminate\Database\Eloquent\Relations\MorphMany
	{
		return $this->morphMany(Document::class, 'owner');
	}


	public function getFilePath(string $path)
	{
		return asset(Storage::url($path));
	}


	public function tuteur(): MorphOne
	{
		return $this->morphOne(Tuteur::class, 'owner');
	}

	public function tuteurs(): \Illuminate\Database\Eloquent\Relations\MorphMany
	{
		return $this->morphMany(Tuteur::class, 'owner');
	}

	public function responsable(): MorphOne
	{
		return $this->morphOne(ResponsableFrais::class, 'owner');
	}

	public function Reorientations()
	{
		return $this->hasMany(Reorientation::class);
	}

	public function hasComplexSlug(): bool
	{
		return true;
	}

	protected static function boot()
	{
		parent::boot();

		static::creating(function ($model) {
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

	public function niveau()
	{
		return $this->belongsTo(Niveau::class, 'niveau_id');
	}

	public function filiere()
	{
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

	public function advertiser(): BelongsTo
	{
		return $this->belongsTo(Advertiser::class);
	}

	public function concoursSession(): BelongsTo
	{
		return $this->belongsTo(ConcoursSession::class);
	}

	/**
	 * Moyenne pondérée par coefficient sur les matières de la session de concours
	 * (niveau/filière du candidat). Retourne null si aucune note n'a été saisie.
	 */
	public function moyenneConcours(): ?float
	{
		if (!$this->concours_session_id) {
			return null;
		}

		$sessionMatieres = ConcoursSessionMatiere::where('concours_session_id', $this->concours_session_id)
			->where('niveau_id', $this->niveau_id)
			->where(function ($q) {
				$q->whereNull('filiere_id')->orWhere('filiere_id', $this->filiere_id);
			})
			->pluck('coefficient', 'id');

		if ($sessionMatieres->isEmpty()) {
			return null;
		}

		$notes = ConcoursNote::where('candidature_id', $this->id)
			->whereIn('concours_session_matiere_id', $sessionMatieres->keys())
			->whereNotNull('note')
			->get();

		if ($notes->isEmpty()) {
			return null;
		}

		$totalPoints = 0;
		$totalCoefficients = 0;

		foreach ($notes as $note) {
			$coefficient = (float) $sessionMatieres->get($note->concours_session_matiere_id, 0);
			$totalPoints += ((float) $note->note) * $coefficient;
			$totalCoefficients += $coefficient;
		}

		if ($totalCoefficients <= 0) {
			return null;
		}

		return round($totalPoints / $totalCoefficients, 2);
	}
}
