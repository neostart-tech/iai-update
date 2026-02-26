<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FraisEtudiant;
use App\Models\Echeancier;
use App\Models\Echeance;
use App\Models\Etudiant;
use App\Models\FraisScolarite;
use App\Models\BourseEtudiant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class NegociationController extends Controller
{
    /**
     * Afficher la liste des frais étudiants
     */
    public function index()
    {
        $query = FraisEtudiant::with(['etudiant', 'fraisScolarite.niveau', 'anneeScolaire', 'bourseEtudiant.bourse','echeances.paiements'])
            ->orderBy('created_at', 'desc')->get();
      return response()->json($query);

    }

    /**
     * Afficher le formulaire de création
     */
    // public function create(Request $request)
    // {
    //     $etudiant = null;
    //     $fraisDisponibles = collect();
    //     $bourses = collect();

    //     if ($request->filled('etudiant_id')) {
    //         $etudiant = Etudiant::with('dernierGroupe.niveau', 'dernierGroupe.filiere')->find($request->etudiant_id);
            
    //         if ($etudiant && $etudiant->dernierGroupe) {
    //             // Récupérer les frais correspondant au niveau de l'étudiant
    //             $fraisDisponibles = FraisScolarite::with('niveau', 'filiere')
    //                 ->where('niveau_id', $etudiant->dernierGroupe->niveau_id)
    //                 ->where(function($q) use ($etudiant) {
    //                     $q->whereNull('filiere_id')
    //                       ->orWhere('filiere_id', $etudiant->dernierGroupe->filiere_id);
    //                 })
    //                 ->get();

    //             // Récupérer les bourses actives de l'étudiant
    //             $bourses = BourseEtudiant::with('bourse')
    //                 ->where('etudiant_id', $etudiant->id)
    //                 ->whereHas('bourse', function($q) {
    //                     $q->where('statut', 'active');
    //                 })
    //                 ->get();
    //         }
    //     }

    //     $etudiants = Etudiant::orderBy('nom')->get();
    //     $anneesScolaires = \App\Models\AnneeScolaire::orderBy('nom', 'desc')->get();

    //  return __200('Négociation créer avec succes');
    // }

    /**
     * Enregistrer une nouvelle négociation
     */
    public function store(Request $request)
    {
        $request->validate([
            'etudiant_id' => 'required|exists:etudiants,id',
            'frais_scolarite_id' => 'required|exists:frais_scolarites,id',
            'annee_scolaire_id' => 'required|exists:annee_scolaires,id',
            'bourse_etudiant_id' => 'nullable|exists:bourse_etudiants,id',
            'type_paiement' => 'required|in:tranches_globales,negociation',
            'frequence_paiement' => 'required_if:type_paiement,tranches_globales|in:annuel,trimestriel,bimestriel,mensuel',
            'echeances' => 'required_if:type_paiement,negociation|array|min:1',
            'echeances.*.libelle' => 'required|string',
            'echeances.*.montant' => 'required|numeric|min:1',
            'echeances.*.date_limite' => 'required|date|after:today',
            'commentaire' => 'nullable|string'
        ]);

        $frais = FraisScolarite::findOrFail($request->frais_scolarite_id);
        
        // Calculer le montant après bourse
        $montantApresBourse = $frais->montant;
        $bourseEtudiant = null;

        if ($request->filled('bourse_etudiant_id')) {
            $bourseEtudiant = BourseEtudiant::with('bourse')->find($request->bourse_etudiant_id);
            
            if ($bourseEtudiant) {
                $bourse = $bourseEtudiant->bourse;
                
                if ($bourse->type === 'pourcentage') {
                    $montantApresBourse = $frais->montant * (1 - $bourse->valeur / 100);
                } else {
                    $montantApresBourse = max(0, $frais->montant - $bourse->valeur);
                }
            }
        }

        // Vérifier que l'étudiant n'a pas déjà un frais pour cette année
        $existant = FraisEtudiant::where('etudiant_id', $request->etudiant_id)
            ->where('annee_scolaire_id', $request->annee_scolaire_id)
            ->first();

        if ($existant) {
            return back()->withErrors(['error' => 'Cet étudiant a déjà des frais enregistrés pour cette année'])->withInput();
        }

        DB::beginTransaction();

        try {
            // Créer le frais étudiant
            $fraisEtudiant = FraisEtudiant::create([
                'etudiant_id' => $request->etudiant_id,
                'frais_scolarite_id' => $request->frais_scolarite_id,
                'annee_scolaire_id' => $request->annee_scolaire_id,
                'montant_initial' => $frais->montant,
                'montant_apres_bourse' => $montantApresBourse,
                'bourse_etudiant_id' => $request->bourse_etudiant_id,
                'type_paiement' => $request->type_paiement,
                'frequence_paiement' => $request->frequence_paiement ?? 'annuel',
                'statut' => 'en_cours'
            ]);

            // Gérer les échéances selon le type
            if ($request->type_paiement === 'negociation') {
                // Créer un échéancier pour la négociation
                $echeancier = Echeancier::create([
                    'frais_etudiant_id' => $fraisEtudiant->id,
                    'created_by' => Auth::id(),
                    'commentaire' => $request->commentaire
                ]);

                // Créer les échéances négociées
                foreach ($request->echeances as $index => $echeanceData) {
                    Echeance::create([
                        'echeancier_id' => $echeancier->id,
                        'frais_etudiant_id' => $fraisEtudiant->id,
                        'libelle' => $echeanceData['libelle'],
                        'montant' => $echeanceData['montant'],
                        'montant_paye' => 0,
                        'date_limite' => $echeanceData['date_limite'],
                        'ordre' => $index + 1,
                        'statut' => 'en_attente'
                    ]);
                }
            } else {
                // Créer les échéances basées sur les tranches globales ou la fréquence
                $fraisEtudiant->creerEcheancesDepuisTranchesGlobales();
            }

            DB::commit();

            return __200('Négociation créer avec succes');

            // return redirect()->route('admin.negociations.show', $fraisEtudiant->id)
            //     ->with('success', 'Négociation enregistrée avec succès');
        } catch (\Exception $e) {
            DB::rollBack();
            // return back()->withErrors(['error' => 'Erreur: ' . $e->getMessage()])->withInput();
            return response()->json(['error' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * Afficher les détails d'une négociation
     */
    public function show($id)
    {
        $fraisEtudiant = FraisEtudiant::with([
            'etudiant',
            'fraisScolarite.niveau',
            'fraisScolarite.filiere',
            'anneeScolaire',
            'bourseEtudiant.bourse',
            'echeances' => function($q) {
                $q->orderBy('ordre');
            },
            'paiements' => function($q) {
                $q->orderBy('date_paiement', 'desc');
            }
        ])->findOrFail($id);

        return response()->json($fraisEtudiant);

        // return view('admin.negociations.show', compact('fraisEtudiant'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    // public function edit($id)
    // {
    //     $fraisEtudiant = FraisEtudiant::with([
    //         'etudiant',
    //         'echeances',
    //         'bourseEtudiant.bourse'
    //     ])->findOrFail($id);

    //     // Ne permettre l'édition que si c'est une négociation et pas encore soldé
    //     if ($fraisEtudiant->type_paiement !== 'negociation' || $fraisEtudiant->statut === 'solde') {
    //         return redirect()->route('admin.negociations.show', $id)
    //             ->with('error', 'Cette négociation ne peut pas être modifiée');
    //     }

    //     // return view('admin.negociations.edit', compact('fraisEtudiant'));
    // }

    // /**
    //  * Mettre à jour une négociation
    //  */
    public function update(Request $request, $id)
    {
        $fraisEtudiant = FraisEtudiant::findOrFail($id);

        if ($fraisEtudiant->type_paiement !== 'negociation' || $fraisEtudiant->statut === 'solde') {
            return response()->json(['error' => 'Non modifiable'], 422);
        }

        $request->validate([
            'echeances' => 'required|array|min:1',
            'echeances.*.id' => 'nullable|exists:echeances,id',
            'echeances.*.libelle' => 'required|string',
            'echeances.*.montant' => 'required|numeric|min:1',
            'echeances.*.date_limite' => 'required|date|after:today',
            'commentaire' => 'nullable|string'
        ]);

        DB::beginTransaction();

        try {
            // Mettre à jour ou créer les échéances
            $idsRecus = [];
            
            foreach ($request->echeances as $index => $echeanceData) {
                if (isset($echeanceData['id'])) {
                    $echeance = Echeance::find($echeanceData['id']);
                    $echeance->update([
                        'libelle' => $echeanceData['libelle'],
                        'montant' => $echeanceData['montant'],
                        'date_limite' => $echeanceData['date_limite'],
                        'ordre' => $index + 1
                    ]);
                    $idsRecus[] = $echeance->id;
                } else {
                    $nouvelle = Echeance::create([
                        'echeancier_id' => $fraisEtudiant->echeancier->id,
                        'frais_etudiant_id' => $fraisEtudiant->id,
                        'libelle' => $echeanceData['libelle'],
                        'montant' => $echeanceData['montant'],
                        'montant_paye' => 0,
                        'date_limite' => $echeanceData['date_limite'],
                        'ordre' => $index + 1,
                        'statut' => 'en_attente'
                    ]);
                    $idsRecus[] = $nouvelle->id;
                }
            }

            // Supprimer les échéances qui ne sont plus là
            Echeance::where('echeancier_id', $fraisEtudiant->echeancier->id)
                ->whereNotIn('id', $idsRecus)
                ->delete();

            // Mettre à jour le commentaire
            if ($fraisEtudiant->echeancier) {
                $fraisEtudiant->echeancier->update([
                    'commentaire' => $request->commentaire
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Négociation mise à jour avec succès'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Ajouter un paiement à une échéance
     */
    public function ajouterPaiement(Request $request, $id)
    {
        $request->validate([
            'echeance_id' => 'required|exists:echeances,id',
            'montant' => 'required|numeric|min:1',
            'mode_paiement' => 'required|string',
            'reference' => 'nullable|string'
        ]);

        $echeance = Echeance::findOrFail($request->echeance_id);

        try {
            $paiement = $echeance->ajouterPaiement($request->montant, [
                'mode_paiement' => $request->mode_paiement,
                'reference' => $request->reference
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Paiement enregistré avec succès',
                'paiement' => $paiement,
                'echeance' => $echeance->fresh(),
                'frais_etudiant' => $echeance->fraisEtudiant->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtenir les données pour le tableau de bord
     */
    public function dashboard()
    {
        $stats = [
            'total_etudiants' => FraisEtudiant::distinct('etudiant_id')->count('etudiant_id'),
            'en_cours' => FraisEtudiant::where('statut', 'en_cours')->count(),
            'en_retard' => FraisEtudiant::where('statut', 'en_retard')->count(),
            'solde' => FraisEtudiant::where('statut', 'solde')->count(),
            'montant_total' => FraisEtudiant::sum('montant_apres_bourse'),
            'montant_paye' => \App\Models\Paiement::where('status', 'valide')->sum('montant')
        ];

        $echeancesAVenir = Echeance::with(['fraisEtudiant.etudiant'])
            ->where('date_limite', '>=', now())
            ->where('date_limite', '<=', now()->addDays(7))
            ->where('statut', '!=', 'paye')
            ->orderBy('date_limite')
            ->limit(10)
            ->get();

        $echeancesEnRetard = Echeance::with(['fraisEtudiant.etudiant'])
            ->where('date_limite', '<', now())
            ->where('statut', '!=', 'paye')
            ->orderBy('date_limite')
            ->limit(10)
            ->get();

        return response()->json([
            'stats' => $stats,
            'echeances_a_venir' => $echeancesAVenir,
            'echeances_en_retard' => $echeancesEnRetard
        ]);
    }
}