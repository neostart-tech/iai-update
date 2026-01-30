<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    protected $guarded = ['id'];

    public function etudiant()
    {
        return $this->belongsTo(Etudiant::class);
    }

     public function user()
    {
        return $this->belongsTo(User::class,'annule_par');
    }


    public function tranchePaiement()
    {
        return $this->belongsTo(TranchePaiement::class,'payable_id');
    }

     public function payable()
    {
        return $this->morphTo();
    }

}