<?php

namespace App\Models;

use App\Enums\{TypeAnnonceEnum, TypeContratEnum};
use App\Traits\Routing\{GenerateUniqueSlugTrait, ModelsSlugKeyTrait};
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property Collection<array-key, Etudiant> $etudiants
 * @property Collection<array-key, AnnouncementEtudiant> $applications
 * @property Advertiser $advertiser
 */
class Announcement extends Model
{
	use ModelsSlugKeyTrait, GenerateUniqueSlugTrait;

	public function hasSlugBaseKeyProvider(): string
	{
		return 'title';
	}

	public function hasComplexSlug(): bool
	{
		return true;
	}

	protected $fillable = [
		'advertiser_id',
		'content',
		'type',
		'ville',
		'title',
		'file_path',
		'status',
		'annee_scolaire_id',
		'duration'
	];

	protected $casts = [
		'type_annonce' => TypeAnnonceEnum::class,
		'type_contrat' => TypeContratEnum::class,
	];

	public function advertiser(): BelongsTo
	{
		return $this->belongsTo(Advertiser::class);
	}

	public function etudiants(): BelongsToMany
	{
		return $this->belongsToMany(Etudiant::class)
			->using(AnnouncementEtudiant::class)
			->withPivot('applied')
			->withTimestamps();
	}

	public function announcementEtudiants(): HasMany
	{
		return $this->hasMany(AnnouncementEtudiant::class);
	}

	public function applications(): HasMany
	{
		return $this->announcementEtudiants();
	}
}
