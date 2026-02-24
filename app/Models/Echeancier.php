<?php

namespace App\Models;

use App\Traits\Routing\GenerateUniqueSlugTrait;
use App\Traits\Routing\ModelsSlugKeyTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Echeancier extends Model
{
    use GenerateUniqueSlugTrait, ModelsSlugKeyTrait;

    use HasFactory;

    protected $table="echanciers";

    protected $guarded = [];

    public function etudiant()
    {
        return $this->belongsTo(Etudiant::class);
    }

    public function echeances()
    {
        return $this->hasMany(Echeance::class);
    }
}
