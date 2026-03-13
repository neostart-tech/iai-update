<?php

namespace App\Models;

use App\Enums\TypeProgrammeEnum;
use App\Models\Scopes\CurrentAnneeScolaireScope;
use App\Traits\Routing\{GenerateUniqueSlugTrait, ModelsSlugKeyTrait};
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, HasOne, MorphOne, MorphTo};

/**
 * @property User|Etudiant $owner
 * @method static self create(array $attributes)
 */
#[ScopedBy([CurrentAnneeScolaireScope::class])]
class EmploiDuTemp extends Model
{
	use ModelsSlugKeyTrait, GenerateUniqueSlugTrait;

	public function hasSlugBaseKeyProvider(): bool
	{
		return false;
	}

	public $timestamps = false;

	protected $guarded = ["id"];

	protected $casts = [
		'debut' => 'datetime',
		'fin' => 'datetime',
		'type_programme' => TypeProgrammeEnum::class
	];

	/**
	 * Relation : Une programmation d'emploi du temps peut être liée à une matière
	 * @return BelongsTo
	 */
	public function uv(): BelongsTo
	{
		return $this->belongsTo(UniteValeur::class, 'uv_id');
	}

	/**
	 * Relation : Une programmation d'emploi du temps doit être liée à une salle
	 * @return BelongsTo
	 */
	public function salle(): BelongsTo
	{
		return $this->belongsTo(Salle::class);
	}

	/**
	 * Relation : Une programmation d'emploi du temps peut être liée à un groupe d'étudiants
	 * @return BelongsTo
	 */
	public function group(): BelongsTo
	{
		return $this->belongsTo(Group::class);
	}

	/**
	 * Relation : Une programmation d'emploi du temps (considérée comme un cours) peut avoir une fiche de présence
	 * @return MorphOne
	 */
	public function fiche(): MorphOne
	{
		return $this->morphOne(FicheDePresence::class, 'controllable');
	}

	/**
	 * Relation : Une programmation d'emploi du temps peut soit être liée à un étudiant ou un enseignant
	 * @return MorphTo
	 */
	public function owner(): MorphTo
	{
		return $this->morphTo();
	}

	public function evenement(){
		return $this->belongsTo(EmploiDuTemp::class);
	}

	   public function cours()
    {
        return $this->belongsTo(Cours::class);
    }

    public function cahierTexte()
    {
        return $this->hasOne(CahierTexte::class);
    }

	 public function evaluation()
    {
        return $this->belongsTo(Evaluation::class);
    }

	public function presences(){
		return $this->hasMany(CoursPresence::class,'emploi_du_temps_id');
	}

	public function enseignantPresence(): HasOne
{
    return $this->hasOne(EnseignantPresence::class, 'emploi_du_temps_id');
}


public function seances(): HasMany
{
    return $this->hasMany(Seance::class, 'emploi_du_temps_id');
}

/**
 * Relation avec les présences (via les séances)
 */
public function toutesPresences()
{
    return $this->hasManyThrough(
        CoursPresence::class,
        Seance::class,
        'emploi_du_temps_id', // Clé étrangère sur seances
        'seance_id',          // Clé étrangère sur presences
        'id',                 // Clé locale sur emploi_du_temps
        'id'                  // Clé locale sur seances
    );
}

/**
 * Obtenir la séance du jour pour ce cours
 */
public function seanceDuJour($date = null)
{
    $date = $date ?? now()->toDateString();
    
    return $this->seances()
        ->whereDate('date_seance', $date)
        ->first();
}

/**
 * Créer une séance pour une date donnée
 */
public function creerSeance($date, $options = [])
{
    return $this->seances()->create(array_merge([
        'date_seance' => $date,
        'heure_debut_prevue' => $this->debut,
        'heure_fin_prevue' => $this->fin,
        'statut' => 'planifie'
    ], $options));
}


}
