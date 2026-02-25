<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TranchePaiement extends Model
{
    protected $guarded = ['id'];

        protected $table = 'tranche_paiements';


    public function anneeScolaire()
    {
        return $this->belongsTo(AnneeScolaire::class);
    }




    public function fraiscolarite()
    {
        return $this->belongsTo(FraisScolarite::class,'frais_scolarite_id');
    }

    public function paiements()
    {
        return $this->morphMany(Paiement::class, 'payable');
    }
}
