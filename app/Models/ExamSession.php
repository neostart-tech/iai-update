<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamSession extends Model
{
    protected $table = 'exam_sessions';

    protected $fillable = [
        'evaluation_id',
        'etudiant_id',
        'started_at',
        'last_activity_at',
        'submitted_at',
        'status',
        'progress',
        'session_token'
    ];

    protected $casts = [
        'progress' => 'array',
        'started_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'submitted_at' => 'datetime',
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

    /**
     * Vérifier si la session est active
     */
    public function isActive(): bool
    {
        return $this->status === 'en_cours' && is_null($this->submitted_at);
    }

    /**
     * Mettre à jour la dernière activité
     */
    public function updateActivity(): void
    {
        $this->update(['last_activity_at' => now()]);
    }
}