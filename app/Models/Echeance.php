<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Sluggable;

class Echeance extends Model
{
    use Sluggable;

    protected $table = 'echeances';

    protected $fillable = [
        'slug',
        'frais_etudiant_id',
        'libelle',
        'montant',
        'montant_paye',
        'date_limite',
        'ordre',
        'statut'
    ];

    protected $casts = [
        'date_limite' => 'date',
        'montant' => 'decimal:0',
        'montant_paye' => 'decimal:0'
    ];

    const STATUTS = [
        'en_attente' => 'En attente',
        'partiel' => 'Partiellement payé',
        'paye' => 'Payé',
        'en_retard' => 'En retard'
    ];

    // ==================== RELATIONS ====================

    public function echeancier()
    {
        return $this->belongsTo(Echeancier::class);
    }

    public function fraisEtudiant()
    {
        return $this->belongsTo(FraisEtudiant::class);
    }

    public function paiements()
    {
        return $this->morphMany(Paiement::class, 'payable');
    }

    // ==================== ATTRIBUTS CALCULÉS ====================

    public function getResteAPayerAttribute()
    {
        return $this->montant - $this->montant_paye;
    }

    public function getEstEnRetardAttribute()
    {
        return $this->date_limite < now() && $this->montant_paye < $this->montant;
    }

    public function getProgressionAttribute()
    {
        if ($this->montant == 0) return 0;
        return round(($this->montant_paye / $this->montant) * 100);
    }

    public function getCouleurStatutAttribute()
    {
        return match($this->statut) {
            'paye' => 'green',
            'partiel' => 'orange',
            'en_retard' => 'red',
            default => 'gray'
        };
    }

    // ==================== MÉTHODES ====================

    public function updateMontantPaye()
    {
        $this->montant_paye = $this->paiements()->sum('montant');
        $this->updateStatut();
        $this->save();
        
        return $this;
    }

    public function updateStatut()
    {
        if ($this->montant_paye >= $this->montant) {
            $this->statut = 'paye';
        } elseif ($this->montant_paye > 0) {
            $this->statut = 'partiel';
        } elseif ($this->date_limite < now()) {
            $this->statut = 'en_retard';
        } else {
            $this->statut = 'en_attente';
        }
        
        return $this;
    }

  public function ajouterPaiement($montant, $data = [])
{
    if ($montant <= 0) {
        throw new \Exception("Le montant doit être supérieur à 0");
    }

    if ($montant > $this->reste_a_payer) {
        throw new \Exception("Le montant dépasse le reste à payer (" . $this->reste_a_payer . " FCFA)");
    }

    $paiement = new Paiement();
    $paiement->etudiant_id = $this->fraisEtudiant->etudiant_id;
    $paiement->montant = $montant;
    $paiement->mode_paiement = $data['mode_paiement'] ?? 'especes';
    $paiement->reference = $data['reference'] ?? null;
    $paiement->justificatif = $data['justificatif'] ?? null;
    $paiement->status = 'valide';
    $paiement->date_paiement = $data['date_paiement'] ?? now();
    $paiement->payable_type = 'App\\Models\\Echeance';
    $paiement->payable_id = $this->id;
    $paiement->save();

    $this->updateMontantPaye();
    $this->fraisEtudiant->updateStatut();

    return $paiement;
}

    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }

    public function scopeEnRetard($query)
    {
        return $query->where('statut', 'en_retard');
    }

    public function scopePaye($query)
    {
        return $query->where('statut', 'paye');
    }

    public function scopePartiel($query)
    {
        return $query->where('statut', 'partiel');
    }

    public function scopeAVenir($query)
    {
        return $query->where('date_limite', '>', now());
    }

    public function scopeEchues($query)
    {
        return $query->where('date_limite', '<', now());
    }

    public function scopePourPeriode($query, $debut, $fin)
    {
        return $query->whereBetween('date_limite', [$debut, $fin]);
    }
}