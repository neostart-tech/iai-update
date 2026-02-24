<?php

namespace App\Http\Controllers;

use App\Http\Resources\PlanPaiementResource;
use App\Models\PlanPaiement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str; 

class PlanPaiementController extends Controller
{
    // Lister tous les plans
    public function index()
    {
        $plans = PlanPaiement::with('tranches')->get();
        return PlanPaiementResource::collection($plans);
    }

    // Créer un plan de paiement
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'type' => 'required|in:standard,tranches_fixes,negociation',
            'nombre_tranches' => 'nullable|integer|min:1',
            'est_personnalise' => 'sometimes|boolean',
            'tranches' => 'nullable|array',
            'tranches.*.ordre' => 'required_with:tranches|integer|min:1',
            'tranches.*.montant' => 'nullable|numeric|min:0',
            'tranches.*.pourcentage' => 'nullable|numeric|min:0|max:100',
            'tranches.*.mois_apres_debut' => 'nullable|integer|min:0',
            'actif' => 'sometimes|boolean'
        ]);

        return DB::transaction(function () use ($validated) {
            $plan = PlanPaiement::create([
                'nom' => $validated['nom'],
                'slug' => Str::slug($validated['nom']),
                'type' => $validated['type'],
                'nombre_tranches' => $validated['nombre_tranches'] ?? 1,
                'est_personnalise' => $validated['est_personnalise'] ?? false,
                'actif' => $validated['actif'] ?? true,
            ]);

            if (isset($validated['tranches']) && $plan->type === 'tranches_fixes') {
                foreach ($validated['tranches'] as $t) {
                    $plan->tranches()->create($t);
                }
            }

            return new PlanPaiementResource($plan->load('tranches'));
        });
    }

    // Voir un plan
    public function show($id) // Changer pour accepter l'ID directement
    {
        $plan = PlanPaiement::with('tranches')->findOrFail($id);
        return new PlanPaiementResource($plan);
    }

    // Mettre à jour
    public function update(Request $request, $id)
    {
        $plan = PlanPaiement::findOrFail($id);
        
        $validated = $request->validate([
            'nom' => 'sometimes|string|max:255',
            'actif' => 'sometimes|boolean',
            'nombre_tranches' => 'nullable|integer|min:1',
            'est_personnalise' => 'sometimes|boolean',
            'tranches' => 'nullable|array',
        ]);

        return DB::transaction(function () use ($plan, $validated) {
            if (isset($validated['nom'])) {
                $validated['slug'] = Str::slug($validated['nom']);
            }
            
            $plan->update($validated);

            if (isset($validated['tranches']) && $plan->type === 'tranches_fixes') {
                $plan->tranches()->delete();
                foreach ($validated['tranches'] as $t) {
                    $plan->tranches()->create($t);
                }
            }

            return new PlanPaiementResource($plan->load('tranches'));
        });
    }

    // Supprimer un plan
    public function destroy($id)
    {
        $plan = PlanPaiement::findOrFail($id);
        
        if (!$plan->peutSupprimer()) {
            return response()->json(['message' => 'Plan utilisé, suppression impossible'], 422);
        }

        $plan->delete();
        return response()->json(['message' => 'Plan supprimé avec succès']);
    }
}