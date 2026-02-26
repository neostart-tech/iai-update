<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Sluggable;

class FraisEtudiant extends Model
{
    use Sluggable;

    protected $table = 'frais_etudiants';

    protected $fillable = [
        'slug',
        'etudiant_id',
        'frais_scolarite_id',
        'annee_scolaire_id',
        'montant_initial',
        'montant_apres_bourse',
        'bourse_etudiant_id',
        'type_paiement',
        'frequence_paiement',
        'statut'
    ];

    protected $casts = [
        'montant_initial' => 'decimal:0',
        'montant_apres_bourse' => 'decimal:0'
    ];

    const TYPE_PAIEMENT = [
        'tranches_globales' => 'Tranches par défaut',
        'negociation' => 'Négociation personnalisée'
    ];

    const FREQUENCE_PAIEMENT = [
        'annuel' => 'Paiement annuel (1 fois)',
        'trimestriel' => 'Paiement trimestriel (4 fois)',
        'bimestriel' => 'Paiement bimestriel (6 fois)',
        'mensuel' => 'Paiement mensuel (12 fois)'
    ];

    const STATUTS = [
        'en_cours' => 'En cours',
        'solde' => 'Soldé',
        'en_retard' => 'En retard'
    ];


    public function etudiant()
    {
        return $this->belongsTo(Etudiant::class);
    }

    public function fraisScolarite()
    {
        return $this->belongsTo(FraisScolarite::class);
    }

    public function anneeScolaire()
    {
        return $this->belongsTo(AnneeScolaire::class);
    }

    public function bourseEtudiant()
    {
        return $this->belongsTo(BourseEtudiant::class);
    }

    public function echeancier()
    {
        return $this->hasOne(Echeancier::class);
    }

    public function echeances()
    {
        return $this->hasMany(Echeance::class, 'frais_etudiant_id');
    }

    public function paiements()
    {
        return $this->morphMany(Paiement::class, 'payable');
    }


    public function getTotalPayeAttribute()
    {
        return $this->paiements()->sum('montant');
    }

    public function getResteAPayerAttribute()
    {
        return $this->montant_apres_bourse - $this->total_paye;
    }

    public function getNombreEcheancesParFrequenceAttribute()
    {
        return match($this->frequence_paiement) {
            'annuel' => 1,
            'trimestriel' => 4,
            'bimestriel' => 6,
            'mensuel' => 12,
            default => 1
        };
    }

    public function getMontantParEcheanceAttribute()
    {
        if ($this->nombre_echeances_par_frequence == 0) return 0;
        return $this->montant_apres_bourse / $this->nombre_echeances_par_frequence;
    }

   public function getEcheancesActivesAttribute()
{
    if ($this->type_paiement === 'tranches_globales') {
        // Récupérer les tranches globales sans ordre (ou avec un ordre différent)
        return TranchePaiement::where('frais_scolarite_id', $this->frais_scolarite_id)
            ->orderBy('id') // Ou orderBy('created_at') si vous préférez
            ->get()
            ->map(function($tranche, $index) {
                return (object)[
                    'id' => $tranche->id,
                    'libelle' => $tranche->libelle,
                    'montant' => $tranche->montant,
                    'date_limite' => $tranche->date_limite,
                    'ordre' => $index + 1, // Ordre basé sur l'index
                    'est_globale' => true
                ];
            });
    }
    
    // Récupérer les échéances négociées
    return $this->echeances()->orderBy('id')->get();
}

    public function getEstEnRetardAttribute()
    {
        if ($this->reste_a_payer <= 0) return false;
        
        foreach ($this->echeances_actives as $echeance) {
            if (isset($echeance->est_en_retard) && $echeance->est_en_retard) {
                return true;
            }
        }
        
        return false;
    }

    public function getProchaineEcheanceAttribute()
    {
        $echeances = $this->echeances_actives;
        
        return $echeances
            ->where('date_limite', '>=', now())
            ->where('statut', '!=', 'paye')
            ->sortBy('date_limite')
            ->first();
    }

    public function getEcheancesEnRetardAttribute()
    {
        $echeances = $this->echeances_actives;
        
        return $echeances
            ->filter(function($echeance) {
                return $echeance->est_en_retard ?? false;
            });
    }

    // ==================== MÉTHODES ====================

    public function updateStatut()
    {
        if ($this->reste_a_payer <= 0) {
            $this->statut = 'solde';
        } elseif ($this->est_en_retard) {
            $this->statut = 'en_retard';
        } else {
            $this->statut = 'en_cours';
        }
        
        $this->saveQuietly();
        
        return $this;
    }

    public function genererEcheancesParFrequence($dateDebut = null)
    {
        if ($this->type_paiement !== 'tranches_globales') {
            return null;
        }

        $dateDebut = $dateDebut ?? now();
        $echeances = [];

        for ($i = 0; $i < $this->nombre_echeances_par_frequence; $i++) {
            $dateLimite = match($this->frequence_paiement) {
                'trimestriel' => $dateDebut->copy()->addMonths(3 * ($i + 1)),
                'bimestriel' => $dateDebut->copy()->addMonths(2 * ($i + 1)),
                'mensuel' => $dateDebut->copy()->addMonths($i + 1),
                default => $dateDebut->copy()->addMonths(12)
            };

            $echeances[] = [
                'frais_etudiant_id' => $this->id,
                'libelle' => $this->getLibelleEcheance($i + 1),
                'montant' => $this->montant_par_echeance,
                'montant_paye' => 0,
                'date_limite' => $dateLimite,
                'ordre' => $i + 1,
                'statut' => 'en_attente'
            ];
        }

        return $echeances;
    }

    // public function creerEcheancesDepuisTranchesGlobales()
    // {
    //     if ($this->type_paiement !== 'tranches_globales') {
    //         return false;
    //     }

    //     $tranches = TranchePaiement::where('frais_scolarite_id', $this->frais_scolarite_id)
    //         ->orderBy('id')
    //         ->get();

    //     if ($tranches->isEmpty()) {
    //         // Si pas de tranches définies, utiliser la fréquence
    //         $echeancesData = $this->genererEcheancesParFrequence();
            
    //         foreach ($echeancesData as $data) {
    //             Echeance::create($data);
    //         }
    //     } else {
    //         // Utiliser les tranches existantes
    //         foreach ($tranches as $index => $tranche) {
    //             Echeance::create([
    //                 'frais_etudiant_id' => $this->id,
    //                 'libelle' => $tranche->libelle,
    //                 'montant' => $tranche->montant,
    //                 'montant_paye' => 0,
    //                 'date_limite' => $tranche->date_limite,
    //                 'ordre' => $tranche->ordre ?? $index + 1,
    //                 'statut' => 'en_attente'
    //             ]);
    //         }
    //     }

    //     return true;
    // }
    public function creerEcheancesDepuisTranchesGlobales()
{
    if ($this->type_paiement !== 'tranches_globales') {
        return false;
    }

    // Récupérer les VRAIES tranches globales
    $tranches = TranchePaiement::where('frais_scolarite_id', $this->frais_scolarite_id)
        ->orderBy('id')
        ->get();

    if ($tranches->isNotEmpty()) {
        // Utiliser les tranches réelles
        foreach ($tranches as $index => $tranche) {
            Echeance::create([
                'frais_etudiant_id' => $this->id,
                'libelle' => $tranche->libelle,
                'montant' => $tranche->montant,
                'montant_paye' => 0,
                'date_limite' => $tranche->date_limite,
                'ordre' => $index + 1,
                'statut' => 'en_attente'
            ]);
        }
    } else {
        // Fallback: générer selon la fréquence
        $echeancesData = $this->genererEcheancesParFrequence();
        foreach ($echeancesData as $data) {
            Echeance::create($data);
        }
    }

    return true;
}

    private function getLibelleEcheance($ordre)
    {
        return match($this->frequence_paiement) {
            'trimestriel' => "Trimestre $ordre",
            'bimestriel' => "Bimestre $ordre",
            'mensuel' => "Mois $ordre",
            default => "Paiement annuel"
        };
    }


    public function scopeForEtudiant($query, $etudiantId)
    {
        return $query->where('etudiant_id', $etudiantId);
    }

    public function scopeForAnnee($query, $anneeScolaireId)
    {
        return $query->where('annee_scolaire_id', $anneeScolaireId);
    }

    public function scopeEnCours($query)
    {
        return $query->where('statut', 'en_cours');
    }

    public function scopeEnRetard($query)
    {
        return $query->where('statut', 'en_retard');
    }

    public function scopeSolde($query)
    {
        return $query->where('statut', 'solde');
    }
}