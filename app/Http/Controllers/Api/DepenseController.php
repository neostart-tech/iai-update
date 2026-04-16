<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Depense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class DepenseController extends Controller
{
    /**
     * Liste des dépenses pour l'année scolaire courante
     */
    public function index(Request $request)
    {
        try {
            $anneeId = $request->get('annee_id') ?? getAnneeScolaireId();
            $query = Depense::with('user')->where('annee_scolaire_id', $anneeId);

            if ($request->has('categorie')) {
                $query->where('categorie', $request->categorie);
            }

            if ($request->has('start_date') && $request->has('end_date')) {
                $query->whereBetween('date_depense', [$request->start_date, $request->end_date]);
            }

            $depenses = $query->orderBy('date_depense', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $depenses,
                'total' => $depenses->sum('montant')
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enregistrer une nouvelle dépense
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'titre' => 'required|string|max:255',
            'montant' => 'required|numeric|min:0',
            'date_depense' => 'required|date',
            'categorie' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'reference' => 'nullable|string|max:255',
            'mode_paiement' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $anneeId = getAnneeScolaireId();
            
            $depense = Depense::create([
                'titre' => $request->titre,
                'montant' => $request->montant,
                'date_depense' => $request->date_depense,
                'categorie' => $request->categorie,
                'description' => $request->description,
                'reference' => $request->reference,
                'mode_paiement' => $request->mode_paiement,
                'user_id' => auth()->id(),
                'annee_scolaire_id' => $anneeId
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Dépense enregistrée avec succès',
                'data' => $depense
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer une dépense
     */
    public function destroy($id)
    {
        try {
            $depense = Depense::findOrFail($id);
            $depense->delete();

            return response()->json([
                'success' => true,
                'message' => 'Dépense supprimée avec succès'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Stats des dépenses par catégorie
     */
    public function stats(Request $request)
    {
        try {
            $anneeId = $request->get('annee_id') ?? getAnneeScolaireId();
            
            $stats = Depense::where('annee_scolaire_id', $anneeId)
                ->selectRaw('categorie, SUM(montant) as total')
                ->groupBy('categorie')
                ->get();
                
            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
