<?php

namespace App\Http\Controllers;

use App\Models\Etudiant;
use App\Models\Paiement;
use App\Notifications\PaiementNotification;
use App\Services\PaiementEtudiantService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;

class PaiementController extends Controller
{
     protected $paiementService;
    
    public function __construct(PaiementEtudiantService $paiementService)
    {
        $this->paiementService = $paiementService;
    }
    
    /**
     * Récupérer les informations de paiement de l'étudiant
     */
    public function getInfos($identifier = null)
    {
        try {
            if ($identifier) {
                $etudiant = Etudiant::where('id', $identifier)->orWhere('slug', $identifier)->first();
                if (!$etudiant) throw new Exception("Étudiant introuvable");
                $etudiantId = $etudiant->id;
            } else {
                $user = request()->user();
                $etudiantId = $user->etudiant_id ?? $user->id;
            }
            
            $anneeScolaireId = request()->input('annee_id') ?? request()->header('X-Annee-Scolaire-Id');
            $infos = $this->paiementService->getInfosPaiement($etudiantId, $anneeScolaireId);
            
            return response()->json([
                'success' => true,
                'data' => $infos
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
    
    /**
     * Récupérer le récapitulatif de l'étudiant
     */
    public function getRecap($identifier = null)
    {
        try {
            if ($identifier) {
                $etudiant = Etudiant::where('id', $identifier)->orWhere('slug', $identifier)->first();
                if (!$etudiant) throw new Exception("Étudiant introuvable");
                $etudiantId = $etudiant->id;
            } else {
                $user = request()->user();
                $etudiantId = $user->etudiant_id ?? $user->id;
            }

            $anneeScolaireId = request()->input('annee_id') ?? request()->header('X-Annee-Scolaire-Id');
            $recap = $this->paiementService->getRecap($etudiantId, $anneeScolaireId);
            
            return response()->json([
                'success' => true,
                'data' => $recap
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
    
    /**
     * Récupérer l'historique des paiements de l'étudiant
     */
    public function getHistorique($identifier = null)
    {
        try {
            if ($identifier) {
                $etudiant = Etudiant::where('id', $identifier)->orWhere('slug', $identifier)->first();
                if (!$etudiant) throw new Exception("Étudiant introuvable");
                $etudiantId = $etudiant->id;
            } else {
                $user = request()->user();
                $etudiantId = $user->etudiant_id ?? $user->id;
            }

            $anneeScolaireId = request()->input('annee_id') ?? request()->header('X-Annee-Scolaire-Id');
            $historique = $this->paiementService->getHistorique($etudiantId, $anneeScolaireId);
            
            return response()->json([
                'success' => true,
                'data' => $historique
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
    
    /**
     * Effectuer un paiement
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'etudiant_id' => 'required|exists:etudiants,id',
            'montant' => 'required|numeric|min:1',
            'mode_paiement' => 'required|string|in:especes,banque,semoa,caisse,carte,virement,cheque,mobile_money,autre',
            'nature_paiement' => 'nullable|string|in:scolarite,inscription',
            'frais_retrait_mm' => 'nullable|numeric|min:0',
            'commentaire' => 'nullable|string|max:1000',
            'reference' => 'nullable|string|max:255',
            'payable_id' => 'nullable|integer',
            'payable_type' => 'nullable|string|in:echeance,tranche,frais_inscription',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $justificatifPath = null;
        if ($request->hasFile('justificatif')) {
            $justificatifPath = $request->file('justificatif')->store('paiements/justificatifs', 'public');
        }

        try {
            $anneeScolaireId = $request->input('annee_id') ?? $request->header('X-Annee-Scolaire-Id');
            $result = $this->paiementService->traiterPaiement(
                $request->etudiant_id,
                $request->montant,
                $request->mode_paiement,
                $request->reference,
                $request->payable_id,
                $request->payable_type,
                $request->get('nature_paiement', 'scolarite'),
                $request->get('frais_retrait_mm', 0),
                $request->commentaire,
                $justificatifPath,
                $anneeScolaireId
            );
            
            return response()->json($result);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
    
    /**
     * Rechercher des étudiants (optionnel)
     */
    public function rechercherEtudiants(Request $request)
    {
        $request->validate([
            'search' => 'required|string|min:2'
        ]);
        
        try {
            $etudiants = Etudiant::with(['dernierGroupe.niveau', 'dernierGroupe.filiere'])
                ->where(function($query) use ($request) {
                    $query->where('nom', 'like', '%' . $request->search . '%')
                          ->orWhere('prenom', 'like', '%' . $request->search . '%')
                          ->orWhere('matricule', 'like', '%' . $request->search . '%');
                })
                ->limit(20)
                ->get()
                ->map(function($e) {
                    return [
                        'id' => $e->id,
                        'slug' => $e->slug,
                        'nom' => $e->nom,
                        'prenom' => $e->prenom,
                        'nom_complet' => $e->nom . ' ' . $e->prenom,
                        'matricule' => $e->matricule,
                        'niveau' => $e->dernierGroupe->niveau->libelle ?? null,
                        'filiere' => $e->dernierGroupe->filiere->nom ?? null,
                        'telephone' => $e->tel,
                    ];
                });
            
            return response()->json([
                'success' => true,
                'data' => $etudiants
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Modifier un paiement
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'montant' => 'nullable|numeric|min:1',
            'mode_paiement' => 'nullable|string|in:especes,banque,semoa,caisse,carte,virement,cheque,mobile_money,autre',
            'reference' => 'nullable|string|max:255',
            'commentaire' => 'nullable|string|max:1000',
            'frais_retrait_mm' => 'nullable|numeric|min:0',
            'justificatif' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $justificatifPath = null;
        if ($request->hasFile('justificatif')) {
            $justificatifPath = $request->file('justificatif')->store('paiements/justificatifs', 'public');
        }

        try {
            $result = $this->paiementService->modifierPaiement($id, $request->only([
                'montant', 'mode_paiement', 'reference', 'commentaire', 'frais_retrait_mm'
            ]), $justificatifPath);

            $paiement = Paiement::find($id);
            if ($paiement) {
                // Renvoyer les infos à jour pour le frontend
                $result['infos'] = $this->paiementService->getInfosPaiement($paiement->etudiant_id);
                $result['recap'] = $this->paiementService->getRecap($paiement->etudiant_id);
                $result['historique'] = $this->paiementService->getHistorique($paiement->etudiant_id);
            }

            return response()->json($result);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}