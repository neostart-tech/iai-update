<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamSubmission extends Model
{
    protected $table = 'exam_submissions';

    protected $fillable = [
        'evaluation_id',
        'etudiant_id',
        'question_id',
        'reponse',
        'is_correct',
        'points_obtenus',
        'submitted_at',
        'auto_saved_at',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'reponse' => 'array',
        'is_correct' => 'boolean',
        'points_obtenus' => 'float',
        'submitted_at' => 'datetime',
        'auto_saved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(Evaluation::class);
    }

    public function etudiant(): BelongsTo
    {
        return $this->belongsTo(Etudiant::class, 'etudiant_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(ExamQuestion::class, 'question_id');
    }
}