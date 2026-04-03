<?php

namespace App\Models;

use App\Traits\Routing\GenerateUniqueSlugTrait;
use App\Traits\Routing\ModelsSlugKeyTrait;
use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    use GenerateUniqueSlugTrait, ModelsSlugKeyTrait;

    protected $guarded = ['id'];

    public function etudiant()
    {
        return $this->belongsTo(Etudiant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'annule_par');
    }


    public function tranchePaiement()
    {
        return $this->belongsTo(TranchePaiement::class, 'payable_id');
    }

    public function payable()
    {
        return $this->morphTo();
    }


    const MODES_PAIEMENT = [
        'especes' => 'Espèces',
        'carte' => 'Carte bancaire',
        'virement' => 'Virement',
        'cheque' => 'Chèque',
        'mobile_money' => 'Mobile Money'
    ];

    const NATURES_PAIEMENT = [
        'inscription' => 'Frais d\'Inscription',
        'scolarite' => 'Frais de Scolarité'
    ];

    const STATUTS = [
        'en_attente' => 'En attente',
        'valide' => 'Validé',
        'rejete' => 'Rejeté',
        'rembourse' => 'Remboursé'
    ];

    // ==================== RELATIONS ====================

 

    public function annulePar()
    {
        return $this->belongsTo(User::class, 'annule_par');
    }

    // ==================== ATTRIBUTS CALCULÉS ====================

    public function getEstAnnuleAttribute()
    {
        return $this->annule;
    }

    public function getLibellePayableAttribute()
    {
        if (!$this->payable) return 'N/A';
        
        if ($this->payable instanceof Echeance) {
            return "Échéance: " . $this->payable->libelle;
        }
        
        if ($this->payable instanceof FraisEtudiant) {
            return "Frais: " . $this->payable->fraisScolarite?->niveau?->libelle;
        }
        
        return class_basename($this->payable);
    }

    // ==================== MÉTHODES ====================

    public function valider()
    {
        $this->status = 'valide';
        $this->save();

        // Mettre à jour l'échéance liée si c'est le cas
        if ($this->payable instanceof Echeance) {
            $this->payable->updateMontantPaye();
        } elseif ($this->payable instanceof FraisEtudiant) {
            // Si paiement direct sur le frais
            $this->payable->updateStatut();
        }

        return $this;
    }

    public function annuler($motif, $userId)
    {
        $this->annule = true;
        $this->motif_annulation = $motif;
        $this->date_annulation = now();
        $this->annule_par = $userId;
        $this->status = 'rejete';
        $this->save();

        // Mettre à jour l'échéance liée
        if ($this->payable instanceof Echeance) {
            $this->payable->updateMontantPaye();
        }

        return $this;
    }

    public function genererRecu()
    {
        // Logique pour générer un reçu
        $this->recu = "RECU-" . $this->id . "-" . now()->format('Ymd');
        $this->save();
        
        return $this->recu;
    }

    // ==================== SCOPES ====================

    public function scopeValides($query)
    {
        return $query->where('status', 'valide');
    }

    public function scopeNonAnnules($query)
    {
        return $query->where('annule', false);
    }

    public function scopePourEtudiant($query, $etudiantId)
    {
        return $query->where('etudiant_id', $etudiantId);
    }

    public function scopePourPeriode($query, $debut, $fin)
    {
        return $query->whereBetween('date_paiement', [$debut, $fin]);
    }
}
