<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluationQuestionOption  extends Model
{
    use HasFactory;
     protected $table = 'evaluation_question_options'; 

      protected $guarded = [];

    public function question() {
        return $this->belongsTo(EvaluationQuestion::class, 'question_id');
    }
}

