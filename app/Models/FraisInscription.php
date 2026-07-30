<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FraisInscription extends Model
{
    use HasFactory;
    
    protected $guarded = ["id"];

    public function anneeScolaire()
    {
        return $this->belongsTo(AnneeScolaire::class, 'annee_scolaire_id');
    }

    public function niveau()
    {
        return $this->belongsTo(Niveau::class, 'niveau_id');
    }

    public function filiere()
    {
        return $this->belongsTo(Filiere::class, 'filiere_id');
    }

    public function paiements()
    {
        return $this->morphMany(Paiement::class, 'payable');
    }

    public function getHasPaymentsAttribute()
    {
        return $this->paiements()->exists();
    }
}
