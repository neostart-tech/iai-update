<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Niveau extends Model
{
    protected $fillable = ['libelle'];

    public function fraisScolarites()
    {
        return $this->hasMany(FraisScolarite::class);
    }
}