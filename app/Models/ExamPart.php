<?php
// app/Models/ExamPart.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamPart extends Model
{
    protected $table = 'exam_parts';

    protected $fillable = [
        'evaluation_id',
        'titre',
        'description',
        'contexte',
        'is_case_study', // NOUVEAU
        'order',
        'metadata'
    ];

    protected $casts = [
        'is_case_study' => 'boolean', // NOUVEAU
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(Evaluation::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(ExamQuestion::class, 'part_id')->orderBy('order');
    }

    /**
     * Vérifier si c'est une étude de cas
     */
    public function isCaseStudy(): bool
    {
        return $this->is_case_study && !empty($this->contexte);
    }

    /**
     * Obtenir le contexte formaté
     */
    public function getFormattedContexte(): ?string
    {
        return $this->isCaseStudy() ? $this->contexte : null;
    }
}