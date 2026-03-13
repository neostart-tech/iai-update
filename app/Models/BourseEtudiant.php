<?php

namespace App\Models;

use App\Traits\Routing\GenerateUniqueSlugTrait;
use App\Traits\Routing\ModelsSlugKeyTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BourseEtudiant extends Model
{
    use GenerateUniqueSlugTrait, ModelsSlugKeyTrait;

    use HasFactory;

    protected $table='bourse_etudiants';

    protected $guarded = ["id"];

    public function bourse(){
        return $this->belongsTo(Bourse::class);
    }

     public function etudiant(){
        return $this->belongsTo(Etudiant::class);
    }

    public function anneeScolaire(){
        return $this->belongsTo(AnneeScolaire::class);
    }
}
