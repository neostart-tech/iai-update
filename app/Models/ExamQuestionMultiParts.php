<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamQuestionMultiParts extends Model
{
    protected $table = 'exam_question_multi_parts';

    protected $fillable = [
        'question_id',
        'configuration',  
        'parts'
    ];

    protected $casts = [
        'configuration' => 'array',
        'parts' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(ExamQuestion::class, 'question_id');
    }
}