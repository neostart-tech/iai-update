<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Releve extends Model
{
    use HasFactory;
    protected $fillable=[
       'etudiant_id',
        'annee_scolaire_id',
        'periode_id',
        'chemin_pdf',
           'est_publie',
            'date_publication',
            'qr_hash',
            'verification_data',
    ];

    public function etudiant(){
        return $this->belongsTo(Etudiant::class);
    }

     public function periode(){
        return $this->belongsTo(Periode::class);
    }

     public function anneeScolaire(){
        return $this->belongsTo(AnneeScolaire::class);
    }

    protected $casts = [
        'verification_data' => 'array',
        'date_publication' => 'datetime',
    ];
}
