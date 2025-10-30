<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reclamation extends Model
{
    use HasFactory;

   protected $fillable = [
        'etudiant_id',
        'evaluation_id',
        'motif',
        'fichier_justificatif',
        'statut',
        'commentaire_admin'
    ];

    public function etudiant() {
        return $this->belongsTo(Etudiant::class);
    }

    public function evaluation() {
        return $this->belongsTo(Evaluation::class);
    }
}
