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
 * @property User $enseignant
 * @property Collection<array-key, Note> $notes
 * @property Collection<array-key, EmploiDuTemp> $emploiDuTemps
 * @property UniteEnseignement $uniteEnseignement
 * @property UniteEnseignement $ue
 * @property Filiere $filiere
 */
// #[ScopedBy([CurrentAnneeScolaireScope::class])]
class UniteValeur extends Model
{
	use GetAnneeScolaireModelTrait, ModelsSlugKeyTrait, GenerateUniqueSlugTrait;

	public $timestamps = false;

    protected static function booted()
    {
        static::creating(function ($uv) {
            if (empty($uv->slug)) {
                // Ensure matiere is loaded to get its nom
                if (!$uv->relationLoaded('matiere') && $uv->matiere_id) {
                    $uv->load('matiere');
                }
                
                $nom = $uv->nom ?? 'matiere';
                $slug = \Illuminate\Support\Str::slug($nom);
                
                // Check uniqueness
                $originalSlug = $slug;
                $i = 1;
                while (\App\Models\UniteValeur::where('slug', $slug)->exists()) {
                    $slug = $originalSlug . '-' . $i;
                    $i++;
                }
                
                $uv->slug = $slug;
            }
        });
    }

	protected $guarded = false;

    protected $appends = ['nom', 'code'];

	public function notes(): HasMany
	{
		return $this->hasMany(Note::class);
	}

	public function emploiDuTemps(): HasMany
	{
		return $this->hasMany(EmploiDuTemp::class, 'uv_id');
	}

	public function filiere()
	{
		return $this->belongsTo(Filiere::class, 'filiere_id');
	}

	public function niveau()
	{
		return $this->belongsTo(Niveau::class);
	}

	public function periode()
	{
		return $this->belongsTo(Periode::class);
	}

	// public function enseignants(): BelongsToMany
	// {
	// 	return $this->belongsToMany(User::class, 'enseignant_id')->using(UserUniteValeur::class);
	// }

	public function enseignants(): BelongsToMany
	{
		return $this->belongsToMany(User::class, 'user_unite_valeur', 'unite_valeur_id', 'user_id')->using(UserUniteValeur::class);
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

    /**
     * Le syllabus associé à cette UV
     */
    public function syllabus(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Syllabus::class, 'unite_valeur_id');
    }

    /**
     * La matière globale à laquelle cette UV (programmation) appartient.
     */
    public function matiere(): BelongsTo
    {
        return $this->belongsTo(Matiere::class, 'matiere_id');
    }

    // Accessors to ensure legacy code still working when doing $uv->nom
    public function getNomAttribute()
    {
        return $this->matiere ? $this->matiere->nom : null;
    }

    public function getCodeAttribute()
    {
        return $this->matiere ? $this->matiere->code : null;
    }
}
