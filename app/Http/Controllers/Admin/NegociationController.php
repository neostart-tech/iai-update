<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\NegociationRequest;
use App\Models\AnneeScolaire;
use App\Models\FraisEtudiant;
use App\Models\Echeancier;
use App\Models\Echeance;
use App\Models\Etudiant;
use App\Models\FraisScolarite;
use App\Models\BourseEtudiant;
use App\Models\Paiement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class NegociationController extends Controller
{
    /**
     * Afficher la liste des frais étudiants
     */
    public function index(Request $request)
    {
        $anneeId = $request->annee_scolaire_id ?? \App\Models\AnneeScolaire::where('active', true)->first()?->id;
        
        $query = FraisEtudiant::with(['etudiant', 'fraisScolarite.niveau', 'anneeScolaire', 'bourseEtudiant.bourse', 'echeances.paiements'])
            ->where(function($q) {
                $q->where('est_en_abandon', false)
                  ->orWhereNull('est_en_abandon');
            });
            
        if ($anneeId) {
            $query->where('annee_scolaire_id', $anneeId);
        }
            
        $results = $query->orderBy('created_at', 'desc')->get();
            
        return response()->json($results);
    }

    public function store(NegociationRequest $request)
    {
        $frais = FraisScolarite::findOrFail($request->frais_scolarite_id);
        $anneeScolaireId = $request->annee_scolaire_id ?? AnneeScolaire::courante()->id;

        $fraisExistant = FraisEtudiant::where('etudiant_id', $request->etudiant_id)
            ->where('annee_scolaire_id', $anneeScolaireId)
            ->first();

        if ($fraisExistant) {
            return response()->json([
                'error' => 'Cet étudiant a déjà un dossier financier pour cette année.'
            ], 422);
        }

        $montantApresBourse = $frais->montant;
        $bourseEtudiantId = null;

        if ($request->filled('bourse_etudiant_id')) {
            $bourseEtudiant = BourseEtudiant::with('bourse')
                ->where('bourse_id', $request->bourse_etudiant_id)
                ->where('annee_scolaire_id', $anneeScolaireId)
                ->where('etudiant_id', $request->etudiant_id)
                ->first();

            if ($bourseEtudiant) {
                $bourse = $bourseEtudiant->bourse;
                $bourseEtudiantId = $bourseEtudiant->id;
                if ($bourse->type === 'pourcentage') {
                    $montantApresBourse = $frais->montant * (1 - $bourse->valeur / 100);
                } else {
                    $montantApresBourse = max(0, $frais->montant - $bourse->valeur);
                }
            }
        }

        DB::beginTransaction();
        try {
            $fraisEtudiant = FraisEtudiant::create([
                'etudiant_id' => $request->etudiant_id,
                'frais_scolarite_id' => $request->frais_scolarite_id,
                'annee_scolaire_id' => $anneeScolaireId,
                'montant_initial' => $frais->montant,
                'montant_apres_bourse' => $montantApresBourse,
                'bourse_etudiant_id' => $bourseEtudiantId,
                'type_paiement' => $request->type_paiement,
                'frequence_paiement' => $request->frequence_paiement ?? 'annuel',
                'statut' => 'en_cours'
            ]);

            if ($request->type_paiement === 'negociation' && $request->has('echeances')) {
                // Alignement logique : la somme des échéances négociées devient le montant final dû
                $sommeEcheances = collect($request->echeances)->sum('montant');
                if ($sommeEcheances != $montantApresBourse) {
                    $fraisEtudiant->update(['montant_apres_bourse' => $sommeEcheances]);
                }

                $echeancier = Echeancier::create([
                    'frais_etudiant_id' => $fraisEtudiant->id,
                    'created_by' => Auth::id(),
                    'commentaire' => $request->commentaire
                ]);

                foreach ($request->echeances as $index => $eData) {
                    Echeance::create([
                        'echeancier_id' => $echeancier->id,
                        'frais_etudiant_id' => $fraisEtudiant->id,
                        'libelle' => $eData['libelle'],
                        'montant' => $eData['montant'],
                        'montant_paye' => 0,
                        'date_limite' => $eData['date_limite'],
                        'ordre' => $index + 1,
                        'statut' => 'en_attente'
                    ]);
                }
            } else {
                $fraisEtudiant->creerEcheancesDepuisTranchesGlobales();
            }

            DB::commit();
            return response()->json(['success' => true, 'data' => $fraisEtudiant]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $fraisEtudiant = FraisEtudiant::with([
            'etudiant',
            'fraisScolarite.niveau',
            'anneeScolaire',
            'bourseEtudiant.bourse',
            'echeances' => fn($q) => $q->orderBy('ordre'),
            'echeances.paiements'
        ])->findOrFail($id);

        $tousLesPaiements = $fraisEtudiant->echeances->flatMap->paiements->sortByDesc('date_paiement')->values();
        $response = $fraisEtudiant->toArray();
        $response['paiements'] = $tousLesPaiements;

        return response()->json($response);
    }

    public function update(Request $request, $slug)
    {
        $fraisEtudiant = FraisEtudiant::with(['echeances', 'echeancier'])->where('slug', $slug)->firstOrFail();

        $request->validate([
            'type_paiement' => 'required|in:tranches_globales,negociation',
            'echeances' => 'required_if:type_paiement,negociation|array',
            'echeances.*.libelle' => 'required_if:type_paiement,negociation|string',
            'echeances.*.montant' => 'required_if:type_paiement,negociation|numeric|min:0',
            'echeances.*.date_limite' => 'required_if:type_paiement,negociation|date',
        ]);

        DB::beginTransaction();
        try {
            // Recalcul du montant après bourse si bourse ou frais fournis
            if ($request->has('frais_scolarite_id')) {
                $frais = FraisScolarite::findOrFail($request->frais_scolarite_id);
                $fraisEtudiant->frais_scolarite_id = $frais->id;
                $fraisEtudiant->montant_initial = $frais->montant;
            }

            $montantInitial = $fraisEtudiant->montant_initial;
            $bourseEtudiantId = $fraisEtudiant->bourse_etudiant_id;

            if ($request->has('bourse_etudiant_id')) {
                $bourseEtudiant = BourseEtudiant::with('bourse')
                    ->where('bourse_id', $request->bourse_etudiant_id)
                    ->where('annee_scolaire_id', $fraisEtudiant->annee_scolaire_id)
                    ->where('etudiant_id', $fraisEtudiant->etudiant_id)
                    ->first();
                
                if ($bourseEtudiant) {
                    $bourseEtudiantId = $bourseEtudiant->id;
                    $bourse = $bourseEtudiant->bourse;
                    if ($bourse->type === 'pourcentage') {
                        $fraisEtudiant->montant_apres_bourse = $montantInitial * (1 - $bourse->valeur / 100);
                    } else {
                        $fraisEtudiant->montant_apres_bourse = max(0, $montantInitial - $bourse->valeur);
                    }
                } else {
                    $bourseEtudiantId = null;
                    $fraisEtudiant->montant_apres_bourse = $montantInitial;
                }
                $fraisEtudiant->bourse_etudiant_id = $bourseEtudiantId;
            } else {
                // Si on a changé le frais mais pas envoyé de bourse, il faut recalculer la bourse existante ou mettre le nouveau montant
                if ($bourseEtudiantId) {
                    $bourseEtudiant = BourseEtudiant::with('bourse')->find($bourseEtudiantId);
                    if ($bourseEtudiant) {
                        $bourse = $bourseEtudiant->bourse;
                        if ($bourse->type === 'pourcentage') {
                            $fraisEtudiant->montant_apres_bourse = $montantInitial * (1 - $bourse->valeur / 100);
                        } else {
                            $fraisEtudiant->montant_apres_bourse = max(0, $montantInitial - $bourse->valeur);
                        }
                    } else {
                        $fraisEtudiant->montant_apres_bourse = $montantInitial;
                    }
                } else {
                    $fraisEtudiant->montant_apres_bourse = $montantInitial;
                }
            }

            $fraisEtudiant->type_paiement = $request->type_paiement;
            if ($request->has('frequence_paiement')) {
                $fraisEtudiant->frequence_paiement = $request->frequence_paiement;
            }

            // AJUSTEMENT IMPORTANT : Si c'est une négociation, le nouveau montant final DEVIENT la somme des échéances négociées.
            if ($request->type_paiement === 'negociation' && $request->has('echeances')) {
                $sommeEcheances = collect($request->echeances)->sum('montant');
                $fraisEtudiant->montant_apres_bourse = $sommeEcheances;
            }

            $fraisEtudiant->save();

            $idsRecus = [];
            $totalPayeGlobal = $fraisEtudiant->paiements()->where('status', 'valide')->sum('montant');
            $resteGlobal = $totalPayeGlobal;

            if ($request->type_paiement === 'negociation' && $request->has('echeances')) {
                foreach ($request->echeances as $index => $eData) {
                    $echeance = null;
                    if (isset($eData['id'])) {
                        $echeance = Echeance::find($eData['id']);
                    }

                    $payeDirect = $echeance ? $echeance->paiements()->where('status', 'valide')->sum('montant') : 0;
                    $besoin = ($eData['montant'] ?? 0) - $payeDirect;
                    $creditVirtuel = ($resteGlobal > 0) ? min($besoin, $resteGlobal) : 0;
                    $resteGlobal -= $creditVirtuel;

                    if ($echeance) {
                        $nouveauMontant = $eData['montant'];
                        // Sécurité : on ne peut pas descendre en dessous de ce qui est déjà payé DIRECTEMENT
                        if ($payeDirect > 0) {
                            $nouveauMontant = max($nouveauMontant, $payeDirect);
                        }

                        $echeance->update([
                            'libelle' => $eData['libelle'],
                            'montant' => $nouveauMontant,
                            'date_limite' => $eData['date_limite'],
                            'ordre' => $index + 1
                        ]);
                        $echeance->updateMontantPaye(); // Force le recalcul du statut
                        $idsRecus[] = $echeance->id;
                    } else {
                        $nouvelle = Echeance::create([
                            'echeancier_id' => $fraisEtudiant->echeancier->id ?? null,
                            'frais_etudiant_id' => $fraisEtudiant->id,
                            'libelle' => $eData['libelle'],
                            'montant' => $eData['montant'],
                            'montant_paye' => 0,
                            'date_limite' => $eData['date_limite'],
                            'ordre' => $index + 1,
                            'statut' => 'en_attente'
                        ]);
                        $nouvelle->updateMontantPaye(); // Force le recalcul du statut
                        $idsRecus[] = $nouvelle->id;
                    }
                }
            } elseif ($request->type_paiement === 'tranches_globales') {
                // Si on bascule sur tranches globales, on peut soit recréer tout, 
                // soit laisser le modèle le faire s'il n'y en a pas.
                // Pour un update, on va supprimer les tranches non payées et appeler la méthode de création
                $fraisEtudiant->creerEcheancesDepuisTranchesGlobales();
                // On récupère les IDs créés pour éviter la suppression à l'étape suivante
                $idsRecus = Echeance::where('frais_etudiant_id', $fraisEtudiant->id)->pluck('id')->toArray();
            }

            // Sécurité : ne pas supprimer ce qui est payé
            $echeancesExistantes = Echeance::where('frais_etudiant_id', $fraisEtudiant->id)->get();
            foreach ($echeancesExistantes as $ee) {
                if (!in_array($ee->id, $idsRecus)) {
                    $payeDirect = (float)$ee->paiements()->where('status', 'valide')->sum('montant');
                    if ($payeDirect > 0 || (float)($ee->montant_paye ?? 0) > 0) {
                         // On ne supprime JAMAIS une tranche qui a un paiement réel
                        $idsRecus[] = $ee->id; 
                    } else {
                        $ee->delete();
                    }
                }
            }

            if ($fraisEtudiant->echeancier) {
                $fraisEtudiant->echeancier->update(['commentaire' => $request->commentaire]);
            }

            $fraisEtudiant->updateStatut();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Mis à jour']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getByEtudiant($etudiantId, Request $request)
    {
        $anneeId = $request->annee_scolaire_id ?? \App\Models\AnneeScolaire::where('active', true)->first()?->id;

        $fraisEtudiant = FraisEtudiant::with([
            'etudiant',
            'fraisScolarite.tranchepaiement',
            'anneeScolaire',
            'echeances' => fn($q) => $q->orderBy('ordre'),
            'echeances.paiements',
            'paiements'
        ])
        ->where('etudiant_id', $etudiantId)
        ->where('annee_scolaire_id', $anneeId)
        ->first();

        if (!$fraisEtudiant) return response()->json(['error' => 'Frais non trouvés'], 404);

        $totalPayeGlobal = (float)$fraisEtudiant->paiements()->where('status', 'valide')->sum('montant');
        $resteGlobal = $totalPayeGlobal;

        $dbEcheances = $fraisEtudiant->echeances;

        if ($dbEcheances->isEmpty()) {
            $tranches = $fraisEtudiant->fraisScolarite->tranchepaiement ?? collect();
            $virtuelles = $tranches->map(function($t, $index) use (&$resteGlobal) {
                $montant = (float)$t->montant;
                $credit = min($montant, $resteGlobal);
                $resteGlobal -= $credit;
                return [
                    'libelle' => $t->libelle,
                    'montant' => $montant,
                    'montant_paye' => $credit,
                    'date_limite' => $t->date_limite,
                    'ordre' => $t->ordre ?? ($index + 1),
                    'statut' => $credit >= $montant ? 'paye' : ($credit > 0 ? 'partiel' : 'en_attente'),
                    'est_virtuel' => true
                ];
            });
        } else {
            $virtuelles = $dbEcheances->map(function($e) use (&$resteGlobal) {
                $payeDirect = (float)($e->paiements->where('status', 'valide')->sum('montant') ?? 0);
                $besoin = max(0, (float)$e->montant - $payeDirect);
                $credit = min($besoin, $resteGlobal);
                $resteGlobal -= $credit;
                $total = $payeDirect + $credit;
                return [
                    'id' => $e->id,
                    'libelle' => $e->libelle,
                    'montant' => (float)$e->montant,
                    'montant_paye' => $total,
                    'date_limite' => $e->date_limite,
                    'ordre' => $e->ordre,
                    'statut' => $total >= (float)$e->montant ? 'paye' : ($total > 0 ? 'partiel' : 'en_attente')
                ];
            });
        }

        // On injecte les tranches calculées (avec crédit virtuel) dans l'objet principal
        $fraisEtudiant->setRelation('echeances', $virtuelles);

        return response()->json([
            'success' => true, 
            'data' => $fraisEtudiant,
            'montant_apres_bourse' => (float)$fraisEtudiant->montant_apres_bourse
        ]);
    }
    public function dashboard(Request $request)
    {
        $anneeId = $request->annee_scolaire_id ?? \App\Models\AnneeScolaire::where('active', true)->first()?->id;

        $stats = [
            'total_negocie' => (float)FraisEtudiant::where('annee_scolaire_id', $anneeId)->sum('montant_apres_bourse'),
            'total_paye' => (float)Paiement::whereHasMorph('payable', [\App\Models\Echeance::class], function($q) use ($anneeId) {
                $q->whereHas('fraisEtudiant', function($sq) use ($anneeId) {
                    $sq->where('annee_scolaire_id', $anneeId);
                });
            })->where('status', 'valide')->sum('montant'),
            'nb_negociations' => FraisEtudiant::where('annee_scolaire_id', $anneeId)->count(),
            'nb_etudiants_en_retard' => FraisEtudiant::where('annee_scolaire_id', $anneeId)->where('statut', 'en_retard')->count(),
        ];

        $stats['taux_recouvrement'] = $stats['total_negocie'] > 0 
            ? round(($stats['total_paye'] / $stats['total_negocie']) * 100, 2) 
            : 0;

        return response()->json($stats);
    }

    public function ajouterPaiement(Request $request, $id)
    {
        $fraisEtudiant = FraisEtudiant::findOrFail($id);
        
        $request->validate([
            'montant' => 'required|numeric|min:1',
            'date_paiement' => 'required|date',
            'mode_paiement' => 'required|string',
            'reference' => 'nullable|string'
        ]);

        // On cherche l'échéance la plus ancienne non soldée
        $echeance = Echeance::where('frais_etudiant_id', $fraisEtudiant->id)
            ->where('statut', '!=', 'paye')
            ->orderBy('ordre')
            ->first();

        if (!$echeance) {
            return response()->json(['error' => 'Toutes les échéances sont déjà soldées.'], 422);
        }

        DB::beginTransaction();
        try {
            Paiement::create([
                'etudiant_id' => $fraisEtudiant->etudiant_id,
                'payable_id' => $echeance->id,
                'payable_type' => \App\Models\Echeance::class,
                'montant' => $request->montant,
                'date_paiement' => $request->date_paiement,
                'mode_paiement' => $request->mode_paiement,
                'reference' => $request->reference,
                'status' => 'valide',
                'provenance' => 'manuel'
            ]);

            $echeance->updateMontantPaye();
            $fraisEtudiant->updateStatut();
            DB::commit();
            
            return response()->json(['success' => true, 'message' => 'Paiement ajouté']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
