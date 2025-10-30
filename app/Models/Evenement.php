<?php

namespace App\Models;

use App\Enums\TypeEvaluationEnum;
use App\Models\Scopes\CurrentAnneeScolaireScope;
use App\Traits\Routing\GenerateUniqueSlugTrait;
use App\Traits\Routing\ModelsSlugKeyTrait;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @method static self create(array $attributes)
 * @property Group $group
 * @property Salle $salle
 * @property UniteValeur $matiere
 * @property Collection<array-key, Note> $notes
 */
 #[ScopedBy(CurrentAnneeScolaireScope::class)]

class Evenement extends Model
{
	public function comments()
	{
		return $this->hasMany(Comment::class);
	}
	use ModelsSlugKeyTrait, GenerateUniqueSlugTrait;

	public $timestamps = false;

	protected $guarded = false;

	protected $dates = [
		'created_at',
		'updated_at',
		'start_date',
		'end_date',
		'debut',
		'fin',
		'correction_end_date',
		'correction_submission_date',
	];

	protected $casts = [
		'type' => TypeEvaluationEnum::class,
		'debut' => 'datetime',
		'fin' => 'datetime',
		'start_date' => 'datetime',
		'end_date' => 'datetime',
		'correction_end_date' => 'datetime',
		'correction_submission_date' => 'datetime',
	];

	public function hasSlugBaseKeyProvider(): bool
	{
		return false;
	}

    /**
     * Force implicit route-model binding to use the primary key 'id'.
     */
    public function getRouteKeyName(): string
    {
        return 'id';
    }

	public function group(): BelongsTo
	{
		return $this->belongsTo(Group::class);
	}

	public function salle(): BelongsTo
	{
		return $this->belongsTo(Salle::class);
	}

	public function uniteValeur(): BelongsTo
	{
		return $this->belongsTo(UniteValeur::class);
	}

	public function matiere(): BelongsTo
	{
		return $this->uniteValeur();
	}

	public function fiche(): MorphOne
	{
		return $this->morphOne(FicheDePresence::class, 'controllable');
	}

	public function getDataAsString(): string
	{
		return
			$this->getAttribute('type')->value . " de " . $this->matiere->getAttribute('nom') . " le " .
			$this->getAttribute('debut')->translatedFormat('d F Y') . " de " .
			$this->getAttribute('debut')->translatedFormat('H:i') . " à " . $this->getAttribute('fin')
				->translatedFormat('H:i') . ' dans la salle ' . $this->salle->getAttribute('nom') .
			'.';
	}

	public function getInformationsForMessaging(): string
	{
		return
			$this->getAttribute('type')->value . " de " . $this->matiere->getAttribute('nom') . " qui s'est tenu aujourd'hui de " .
			$this->getAttribute('debut')->format('H:i') . " à " . $this->getAttribute('fin')->format('H:i');
	}

	public function notes(): HasMany
	{
		return $this->hasMany(Note::class);
	}
}
