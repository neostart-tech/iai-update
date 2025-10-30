<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class EvaluationRoom extends Model
{
    public $timestamps = false;
    protected $guarded = false;

    public function evaluation(): BelongsTo { return $this->belongsTo(Evaluation::class); }
    public function salle(): BelongsTo { return $this->belongsTo(Salle::class); }

    public function supervisors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'evaluation_room_supervisors');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Etudiant::class, 'evaluation_room_students');
    }
}
