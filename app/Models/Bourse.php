<?php

namespace App\Models;

use App\Traits\Routing\GenerateUniqueSlugTrait;
use App\Traits\Routing\ModelsSlugKeyTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bourse extends Model
{
    use GenerateUniqueSlugTrait, ModelsSlugKeyTrait;

    use HasFactory;


    protected $guarded = ["id"];

     public function etudiants()
    {
        return $this->belongsToMany(Etudiant::class, 'bourse_etudiants');
    }
}
