<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluationOptions extends Model
{
    use HasFactory;

      protected $guarded = [];

    public function question() {
        return $this->belongsTo(EvaluationQuestion::class, 'question_id');
    }
}

