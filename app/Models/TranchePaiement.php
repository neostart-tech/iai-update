<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TranchePaiement extends Model
{
    protected $fillable = ['frais_scolarite_id','annee_scolaire_id', 'libelle', 'montant', 'date_limite'];

    public function anneeScolaire()
    {
        return $this->belongsTo(AnneeScolaire::class);
    }

    
    public function fraiscolarite()
    {
        return $this->belongsTo(FraisScolarite::class);
    }

        public function paiements() {
             return $this->hasMany(Paiement::class);
             }

}