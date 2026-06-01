<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Depense extends Model
{
    use HasFactory;

    protected $fillable = [
        'titre',
        'montant',
        'date_depense',
        'categorie',
        'description',
        'reference',
        'mode_paiement',
        'user_id',
        'annee_scolaire_id'
    ];

    protected $casts = [
        'date_depense' => 'date',
        'montant' => 'float'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function anneeScolaire()
    {
        return $this->belongsTo(AnneeScolaire::class);
    }
}
