<?php
// app/Models/ExamQuestionStructuredData.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamQuestionStructuredData extends Model
{
    protected $table = 'exam_question_structured_data';

    protected $fillable = [
        'question_id',
        'structure_type',
        'structure',
        'items',
        'bareme'
    ];

    protected $casts = [
        'structure' => 'array',
        'items' => 'array',
        'bareme' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(ExamQuestion::class, 'question_id');
    }
}