<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Sluggable;

class Echeancier extends Model
{
    use Sluggable;

    protected $table = 'echeanciers';

    protected $fillable = [
        'slug',
        'frais_etudiant_id',
        'created_by',
        'commentaire'
    ];

    protected $casts = [
        'date_creation' => 'datetime'
    ];


    public function fraisEtudiant()
    {
        return $this->belongsTo(FraisEtudiant::class);
    }

    public function echeances()
    {
        return $this->hasMany(Echeance::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }


    public function getTotalEcheancesAttribute()
    {
        return $this->echeances()->sum('montant');
    }

    public function getTotalPayeAttribute()
    {
        return $this->echeances()->sum('montant_paye');
    }

    public function getResteAPayerAttribute()
    {
        return $this->total_echeances - $this->total_paye;
    }

    public function getEstConformeAttribute()
    {
        return $this->total_echeances == $this->fraisEtudiant->montant_apres_bourse;
    }

    public function getEcheancesEnRetardAttribute()
    {
        return $this->echeances()
            ->where('date_limite', '<', now())
            ->where('statut', '!=', 'paye')
            ->get();
    }


    public function ajouterEcheances(array $echeancesData)
    {
        $echeances = [];
        
        foreach ($echeancesData as $data) {
            $echeances[] = $this->echeances()->create([
                'frais_etudiant_id' => $this->frais_etudiant_id,
                'libelle' => $data['libelle'],
                'montant' => $data['montant'],
                'montant_paye' => 0,
                'date_limite' => $data['date_limite'],
                'ordre' => $data['ordre'] ?? count($echeances) + 1,
                'statut' => 'en_attente'
            ]);
        }

        return $echeances;
    }

    public function updateStatuts()
    {
        foreach ($this->echeances as $echeance) {
            $echeance->updateStatut();
        }
        
        $this->fraisEtudiant->updateStatut();
        
        return $this;
    }
}