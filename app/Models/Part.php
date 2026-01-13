<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Part extends Model
{
    protected $fillable = [
        'evaluation_id',
        'identifier',
        'title',
        'question_type',
        'order',
        'description',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    /**
     * Relation avec l'évaluation
     */
    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(Evaluation::class);
    }

    /**
     * Relation avec les questions
     */
    public function questions(): HasMany
    {
        return $this->hasMany(EvaluationQuestion::class)->orderBy('order_in_part');
    }

    /**
     * Calculer le total des points de la partie
     */
    public function getTotalPointsAttribute(): float
    {
        return $this->questions->sum('points');
    }

    /**
     * Nombre de questions dans la partie
     */
    public function getQuestionCountAttribute(): int
    {
        return $this->questions->count();
    }

      public function caseStudyContext()
    {
        return $this->hasOne(EvaluationCaseStudyContext::class);
    }
}