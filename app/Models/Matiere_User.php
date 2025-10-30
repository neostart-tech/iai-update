<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Matiere_User extends Model
{
    use HasFactory;

    protected $guard = [
        'user_id',
        'unite_valeur_id',
    ];


  

    public function UniteValeur()
    {
        return $this->belongsTo(UniteValeur::class,'unite_valeur_id');
    }


}
