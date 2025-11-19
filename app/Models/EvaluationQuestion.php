<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluationQuestion extends Model
{
    protected $guarded = false;

    public function evaluation()
    {
        return $this->belongsTo(Evaluation::class);
    }

    public function options()
    {
        return $this->hasMany(EvaluationQuestionOption::class, 'question_id');
    }
}






