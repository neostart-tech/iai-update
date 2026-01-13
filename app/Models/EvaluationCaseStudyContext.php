<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluationCaseStudyContext extends Model
{
    protected $fillable = [
        'evaluation_id',
        'problematic',
        'resources',
        'instructions',
    ];

    public function evaluation()
    {
        return $this->belongsTo(Evaluation::class);
    }
}
