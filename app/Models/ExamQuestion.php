<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ExamQuestion extends Model
{
    protected $table = 'exam_questions';

    protected $fillable = [
        'part_id',
        'content',
        'type',
        'config',
        'points',
        'order',
        'is_active',
        'metadata'
    ];

    protected $casts = [
        'config' => 'array',
        'metadata' => 'array',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function part(): BelongsTo
    {
        return $this->belongsTo(ExamPart::class, 'part_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(ExamQuestionOption::class, 'question_id')->orderBy('order');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(ExamSubmission::class, 'question_id');
    }

    /**
     * Relation pour les questions avec données complexes (TABLEAUX DYNAMIQUES)
     */
    public function complexData(): HasOne
    {
        return $this->hasOne(ExamQuestionComplexData::class, 'question_id');
    }

    /**
     * Relation pour les questions structurées
     */
    public function structuredData(): HasOne
    {
        return $this->hasOne(ExamQuestionStructuredData::class, 'question_id');
    }

    /**
     * Relation pour les questions à plusieurs parties
     */
    public function multiParts(): HasOne
    {
        return $this->hasOne(ExamQuestionMultiParts::class, 'question_id');
    }

    /**
     * Relation pour les questions de rédaction guidée
     */
    public function guidedWriting(): HasOne
    {
        return $this->hasOne(ExamQuestionGuidedWriting::class, 'question_id');
    }

    /**
     * Valider que les points ne dépassent pas 20
     */
    public static function validatePoints($points)
    {
        return $points >= 0 && $points <= 20;
    }

    /**
     * Obtenir la configuration pour texte long
     */
    public function getWordCountConfig(): array
    {
        if ($this->type !== 'texte_long') {
            return [];
        }

        return [
            'min_words' => $this->config['min_words'] ?? 50,
            'max_words' => $this->config['max_words'] ?? 500
        ];
    }

    /**
     * Obtenir la configuration pour texte court
     */
    public function getTextConfig(): array
    {
        if ($this->type !== 'texte_court') {
            return [];
        }

        return [
            'expected_answer' => $this->config['expected_answer'] ?? null,
            'case_sensitive' => $this->config['case_sensitive'] ?? false
        ];
    }
}