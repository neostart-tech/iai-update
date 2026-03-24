<?php
// app/Models/ExamQuestionGuidedWriting.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamQuestionGuidedWriting extends Model
{
    protected $table = 'exam_question_guided_writing';

    protected $fillable = [
        'question_id',
        'instructions',
        'criteria',
        'min_words',
        'max_words'
    ];

    protected $casts = [
        'instructions' => 'array',
        'criteria' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(ExamQuestion::class, 'question_id');
    }
}