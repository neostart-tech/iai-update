<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Club extends Model
{
    use HasFactory;
    protected $guarded = ["id"];

    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    // Membres (étudiants)
    public function etudiants()
    {
        return $this->belongsToMany(Etudiant::class, 'club_etudiants')
            ->withPivot('date_adhesion')
            ->withTimestamps();
    }
}
