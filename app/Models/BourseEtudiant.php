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
}
