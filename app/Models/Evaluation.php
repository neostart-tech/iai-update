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
 *
 * @property Group $group
 * @property Salle $salle
 * @property UniteValeur $matiere
 * @property Collection<array-key, Note> $notes
 */
#[ScopedBy(CurrentAnneeScolaireScope::class)]
class Evaluation extends Model
{
    use GenerateUniqueSlugTrait, ModelsSlugKeyTrait;

    // Niveau relation added for filtering by level
    // public function niveau(): BelongsTo
    // {
    //     return $this->belongsTo(Niveau::class);
    // }

    //   public function periode(): BelongsTo
    // {
    //     return $this->belongsTo(Periode::class);
    // }

    public $timestamps = false;

    protected $guarded = false;

    protected $casts = [
        'type' => TypeEvaluationEnum::class,
        'debut' => 'datetime',
        'fin' => 'datetime',
        'date' => 'date',
        'correction_end_date' => 'datetime',
        'correction_submission_date' => 'datetime',
    ];

    public function hasSlugBaseKeyProvider(): bool
    {
        return false;
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
            $this->getAttribute('type')->value . ' de ' . $this->matiere->getAttribute('nom') . ' le ' .
            $this->getAttribute('debut')->translatedFormat('d F Y') . ' de ' .
            $this->getAttribute('debut')->translatedFormat('H:i') . ' à ' . $this->getAttribute('fin')
            ->translatedFormat('H:i') . ' dans la salle ' . $this->salle->getAttribute('nom') .
            '.';
    }

    public function getInformationsForMessaging(): string
    {
        return
            $this->getAttribute('type')->value . ' de ' . $this->matiere->getAttribute('nom') . " qui s'est tenu aujourd'hui de " .
            $this->getAttribute('debut')->format('H:i') . ' à ' . $this->getAttribute('fin')->format('H:i');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    public function anonymousCodes(): HasMany
    {
        return $this->hasMany(EvaluationAnonymous::class);
    }

    // public function questions()
    // {
    //     return $this->hasMany(EvaluationQuestion::class);
    // }

    // public function submissions()
    // {
    //     return $this->hasMany(EvaluationSubmission::class);
    // }

    public function emploiDutemp()
    {
        return $this->belongsTo(EmploiDuTemp::class, 'emploi_du_temps_id');
    }

    // public function caseStudyContext()
    // {
    //     return $this->hasOne(EvaluationCaseStudyContext::class);
    // }

    //  public function parts(): HasMany
    // {
    //     return $this->hasMany(Part::class)->orderBy('order');
    // }

    /**
     * Récupérer toutes les questions à travers les parties
     */
    // public function allQuestions()
    // {
    //     return $this->hasManyThrough(
    //         EvaluationQuestion::class,
    //         Part::class,
    //         'evaluation_id', // Clé étrangère sur parts
    //         'part_id',       // Clé étrangère sur evaluation_questions
    //         'id',            // Clé locale sur evaluations
    //         'id'             // Clé locale sur parts
    //     );
    // }
}

