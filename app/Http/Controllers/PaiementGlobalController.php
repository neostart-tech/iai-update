<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaiementGlobalRequest;
use App\Models\Etudiant;
use App\Models\AnneeScolaire;
use App\Models\FraisEtudiant;
use App\Models\FraisScolarite;
use App\Models\Paiement;
use App\Models\TranchePaiement;
use App\Services\PaiementIntelligentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaiementGlobalController extends Controller
{
  protected $paiementService;

    public function __construct(PaiementIntelligentService $paiementService)
    {
        $this->paiementService = $paiementService;
    }

    /**
     * Rechercher un étudiant
     */
    public function rechercherEtudiant(Request $request)
    {
        $request->validate([
            'search' => 'required|string|min:2'
        ]);

        $search = $request->search;
        
        $anneeScolaireId = getAnneeScolaireId();
        
        $etudiants = Etudiant::with(['niveau', 'filiere'])
            ->where(function($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%")
                  ->orWhere('matricule', 'like', "%{$search}%");
            })
            ->whereDoesntHave('fraisEtudiant', function($q) use ($anneeScolaireId) {
                $q->where('annee_scolaire_id', $anneeScolaireId)
                  ->where('est_en_abandon', true);
            })
            ->limit(10)
            ->get()
            ->map(function($etudiant) {
                return [
                    'id' => $etudiant->id,
                    'nom_complet' => $etudiant->nom . ' ' . $etudiant->prenom,
                    'nom' => $etudiant->nom,
                    'prenom' => $etudiant->prenom,
                    'matricule' => $etudiant->matricule,
                    'niveau' => $etudiant->niveau?->libelle,
                    'niveau_id' => $etudiant->niveau_id,
                    'filiere' => $etudiant->filiere?->nom
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $etudiants
        ]);
    }

    /**
     * Obtenir les informations de paiement d'un étudiant
     */
    public function getInfosEtudiant(Request $request)
    {
        $request->validate([
            'etudiant_id' => 'required|exists:etudiants,id',
        ]);

        $anneeScolaire = AnneeScolaire::where('active', true)->first();
        
        if (!$anneeScolaire) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune année scolaire active trouvée'
            ], 404);
        }

        $etudiant = Etudiant::with(['etudiantGroups.niveau', 'etudiantGroups.filiere'])->find($request->etudiant_id);
        
        // Vérifier si l'étudiant a une négociation ou des frais
        $fraisEtudiant = FraisEtudiant::with(['echeances' => function($q) {
                $q->orderBy('ordre');
            }])
            ->where('etudiant_id', $request->etudiant_id)
            ->where('annee_scolaire_id', $anneeScolaire->id)
            ->first();

        $resteAPayer = 0;
        $prochainesEcheances = [];
        $typePaiement = null;

        if ($fraisEtudiant) {
            // L'étudiant a déjà un frais enregistré (négociation ou global)
            $resteAPayer = $fraisEtudiant->reste_a_payer;
            $typePaiement = $fraisEtudiant->type_paiement;
            
            $prochainesEcheances = $fraisEtudiant->echeances()
                ->where('statut', '!=', 'paye')
                ->orderBy('ordre')
                ->get()
                ->map(function($e) {
                    return [
                        'id' => $e->id,
                        'libelle' => $e->libelle,
                        'montant' => (float) $e->montant,
                        'montant_paye' => (float) $e->montant_paye,
                        'reste' => (float) $e->reste_a_payer,
                        'date_limite' => $e->date_limite instanceof \Carbon\Carbon 
                            ? $e->date_limite->format('Y-m-d') 
                            : date('Y-m-d', strtotime($e->date_limite)),
                        'date_limite_formatted' => $e->date_limite instanceof \Carbon\Carbon 
                            ? $e->date_limite->format('d/m/Y') 
                            : date('d/m/Y', strtotime($e->date_limite)),
                        'statut' => $e->statut,
                        'ordre' => $e->ordre
                    ];
                });
        } else {
            // Pas de frais existant, on va utiliser les tranches globales
            $fraisScolarite = FraisScolarite::where('niveau_id', $etudiant->niveau_id)
                ->where('annee_scolaire_id', $anneeScolaire->id)
                ->first();

            if ($fraisScolarite) {
                $tranches = TranchePaiement::where('frais_scolarite_id', $fraisScolarite->id)
                    ->orderBy('id')
                    ->get();
                
                $resteAPayer = $fraisScolarite->montant;
                $typePaiement = 'tranches_globales';
                
                $prochainesEcheances = $tranches->map(function($t, $index) {
                    return [
                        'id' => null,
                        'libelle' => $t->libelle,
                        'montant' => (float) $t->montant,
                        'montant_paye' => 0,
                        'reste' => (float) $t->montant,
                        'date_limite' => $t->date_limite instanceof \Carbon\Carbon 
                            ? $t->date_limite->format('Y-m-d') 
                            : date('Y-m-d', strtotime($t->date_limite)),
                        'date_limite_formatted' => $t->date_limite instanceof \Carbon\Carbon 
                            ? $t->date_limite->format('d/m/Y') 
                            : date('d/m/Y', strtotime($t->date_limite)),
                        'statut' => 'en_attente',
                        'ordre' => $index + 1,
                        'tranche_id' => $t->id
                    ];
                });
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'etudiant' => [
                    'id' => $etudiant->id,
                    'nom' => $etudiant->nom,
                    'prenom' => $etudiant->prenom,
                    'nom_complet' => $etudiant->nom . ' ' . $etudiant->prenom,
                    'matricule' => $etudiant->matricule,
                    'niveau' => $etudiant->niveau?->libelle,
                    'filiere' => $etudiant->filiere?->nom,
                    'telephone' => $etudiant->tel,
                    'email' => $etudiant->email
                ],
                'annee_scolaire' => [
                    'id' => $anneeScolaire->id,
                    'nom' => $anneeScolaire->nom
                ],
                'frais' => $fraisEtudiant ? [
                    'id' => $fraisEtudiant->id,
                    'type' => $fraisEtudiant->type_paiement,
                    'type_label' => $fraisEtudiant->type_paiement === 'negociation' ? 'Négociation' : 'Tranches globales',
                    'montant_initial' => (float) $fraisEtudiant->montant_initial,
                    'montant_apres_bourse' => (float) $fraisEtudiant->montant_apres_bourse,
                    'total_paye' => (float) $fraisEtudiant->total_paye,
                    'reste_a_payer' => (float) $fraisEtudiant->reste_a_payer,
                    'statut' => $fraisEtudiant->statut,
                    'statut_label' => $this->getStatutLabel($fraisEtudiant->statut)
                ] : null,
                'prochaines_echeances' => $prochainesEcheances,
                'reste_a_payer_total' => (float) $resteAPayer,
                'type_paiement' => $typePaiement
            ]
        ]);
    }

    /**
     * Traiter un paiement global
     */
    public function store(PaiementGlobalRequest $request)
    {
        try {
            $resultat = $this->paiementService->traiterPaiement(
                $request->etudiant_id,
                $request->montant,
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'message' => $resultat['message'],
                'data' => $resultat
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du traitement du paiement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir l'historique des paiements d'un étudiant
     */
    public function historique($etudiantId)
    {
        $anneeScolaire = AnneeScolaire::where('active', true)->first();
        
        if (!$anneeScolaire) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune année scolaire active trouvée'
            ], 404);
        }
        
        $paiements = Paiement::with(['payable'])
            ->where('etudiant_id', $etudiantId)
            ->whereHas('payable', function($q) use ($anneeScolaire) {
                $q->whereHas('fraisEtudiant', function($q2) use ($anneeScolaire) {
                    $q2->where('annee_scolaire_id', $anneeScolaire->id);
                });
            })
            ->orderBy('date_paiement', 'desc')
            ->get()
            ->map(function($p) {
                $libelle = 'N/A';
                if ($p->payable) {
                    if (get_class($p->payable) === 'App\Models\Echeance') {
                        $libelle = "Échéance: " . $p->payable->libelle;
                    }
                }
                
                return [
                    'id' => $p->id,
                    'date' => $p->date_paiement instanceof \Carbon\Carbon 
                        ? $p->date_paiement->format('Y-m-d') 
                        : date('Y-m-d', strtotime($p->date_paiement)),
                    'date_formatted' => $p->date_paiement instanceof \Carbon\Carbon 
                        ? $p->date_paiement->format('d/m/Y') 
                        : date('d/m/Y', strtotime($p->date_paiement)),
                    'montant' => (float) $p->montant,
                    'mode' => $p->mode_paiement,
                    'mode_label' => $this->getModePaiementLabel($p->mode_paiement),
                    'reference' => $p->reference,
                    'libelle' => $libelle,
                    'status' => $p->status,
                    'status_label' => $this->getPaiementStatusLabel($p->status)
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $paiements
        ]);
    }

    /**
     * Obtenir le récapitulatif des paiements d'un étudiant
     */
  public function recap($etudiantId)
{
    $anneeScolaire = AnneeScolaire::where('active', true)->first();
    
    if (!$anneeScolaire) {
        return response()->json([
            'success' => false,
            'message' => 'Aucune année scolaire active trouvée'
        ], 404);
    }
    
    $fraisEtudiant = FraisEtudiant::with(['echeances'])
        ->where('etudiant_id', $etudiantId)
        ->where('annee_scolaire_id', $anneeScolaire->id)
        ->first();

    if (!$fraisEtudiant) {
        $etudiant = Etudiant::find($etudiantId);
        return response()->json([
            'success' => true,
            'data' => [
                'montant_total' => 0,
                'total_paye' => 0,
                'reste_a_payer' => 0,
                'pourcentage' => 0,
                'nombre_echeances' => 0,
                'echeances_payees' => 0,
                'echeances_en_cours' => 0,
                'echeances_en_attente' => 0,
                'echeances_en_retard' => 0,
                'frais_inscription_paye' => (bool) ($etudiant?->frais_inscription_paye ?? false),
            ]
        ]);
    }

    // Récupérer tous les paiements liés aux échéances de ce frais
    $echeanceIds = $fraisEtudiant->echeances->pluck('id');
    
    // Calculer le total payé à partir des paiements des échéances
    $totalPaye = Paiement::whereIn('payable_id', $echeanceIds)
        ->where('payable_type', 'App\Models\Echeance')
        ->where('status', 'valide')
        ->sum('montant');

 

    $stats = [
        'montant_total' => (float) $fraisEtudiant->montant_apres_bourse,
        'total_paye' => (float) $totalPaye,
        'reste_a_payer' => (float) ($fraisEtudiant->montant_apres_bourse - $totalPaye),
        'pourcentage' => $fraisEtudiant->montant_apres_bourse > 0 
            ? round(($totalPaye / $fraisEtudiant->montant_apres_bourse) * 100, 2)
            : 0,
        'nombre_echeances' => $fraisEtudiant->echeances->count(),
        'echeances_payees' => $fraisEtudiant->echeances->where('statut', 'paye')->count(),
        'echeances_en_cours' => $fraisEtudiant->echeances->where('statut', 'partiel')->count(),
        'echeances_en_attente' => $fraisEtudiant->echeances->where('statut', 'en_attente')->count(),
        'echeances_en_retard' => $fraisEtudiant->echeances->where('statut', 'en_retard')->count(),
        'frais_inscription_paye' => (bool) ($fraisEtudiant->etudiant?->frais_inscription_paye ?? false),
    ];

    return response()->json([
        'success' => true,
        'data' => $stats
    ]);
}
    /**
     * Obtenir le libellé du statut
     */
    private function getStatutLabel($statut)
    {
        $labels = [
            'en_cours' => 'En cours',
            'solde' => 'Soldé',
            'en_retard' => 'En retard'
        ];
        return $labels[$statut] ?? $statut;
    }

    /**
     * Obtenir le libellé du mode de paiement
     */
    private function getModePaiementLabel($mode)
    {
        $labels = [
            'especes' => 'Espèces',
            'banque' => 'Banque',
            'semoa' => 'SEMOA',
            'caisse' => 'Caisse',
            'carte' => 'Carte',
            'virement' => 'Virement',
            'cheque' => 'Chèque'
        ];
        return $labels[$mode] ?? $mode;
    }

    /**
     * Obtenir le libellé du statut de paiement
     */
    private function getPaiementStatusLabel($status)
    {
        $labels = [
            'en_attente' => 'En attente',
            'valide' => 'Validé',
            'rejete' => 'Rejeté',
            'rembourse' => 'Remboursé'
        ];
        return $labels[$status] ?? $status;
    }
}