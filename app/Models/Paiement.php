<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    protected $fillable = [
        'etudiant_id',
        'tranche_paiement_id',
        'montant',
        'mode_paiement',
        'reference',
        'justificatif',
        'status',
        'recu',
        'date_paiement',
        'annule',
    'motif_annulation',
    'date_annulation',
    'annule_par',
    ];

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
        return $this->belongsTo(TranchePaiement::class);
    }

}