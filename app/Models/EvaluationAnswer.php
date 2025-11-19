<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluationAnswer extends Model
{
    protected $guarded = false;

    protected $casts = [
        'answer_options' => 'array'
    ];

    public function submission()
    {
        return $this->belongsTo(EvaluationSubmission::class);
    }

    public function question()
    {
        return $this->belongsTo(EvaluationQuestion::class);
    }
}
