<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluationSubmission extends Model
{
    protected $guarded = false;

    public function evaluation()
    {
        return $this->belongsTo(Evaluation::class);
    }

    public function etudiant()
    {
        return $this->belongsTo(Etudiant::class);
    }

    public function answers()
    {
        return $this->hasMany(EvaluationAnswer::class, 'submission_id');
    }
}

