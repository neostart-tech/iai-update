<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FraisScolarite extends Model
{
    protected $fillable = ['annee_scolaire_id', 'niveau_id', 'montant'];

    public function anneeScolaire()
    {
        return $this->belongsTo(AnneeScolaire::class);
    }

     public function tranchepaiement()
    {
        return $this->hasMany(TranchePaiement::class);
    }
    public function niveau()
    {
        return $this->belongsTo(Niveau::class);
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class);
    }
}