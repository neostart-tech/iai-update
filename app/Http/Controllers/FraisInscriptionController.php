<?php

namespace App\Http\Controllers;

use App\Http\Resources\FraisInscriptionResource;
use App\Models\FraisInscription;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FraisInscriptionController extends Controller
{
    public function index()
    {
        $frais = FraisInscription::with(['anneeScolaire', 'niveau', 'filiere'])->latest()->get();
        return FraisInscriptionResource::collection($frais);
    }

    public function store(Request $request)
    {
        if (!request()->user()->canManageFraisInscription()) {
            return response()->json(['message' => 'Action non autorisée'], 403);
        }

        $request->validate([
            'montant' => 'required|integer|min:0',
            'niveau_id' => 'nullable|exists:niveaux,id',
            'filiere_id' => 'nullable|exists:filieres,id',
        ]);

        $frais = FraisInscription::create([
            'montant' => $request->montant,
            'niveau_id' => $request->niveau_id,
            'filiere_id' => $request->filiere_id,
            'slug' => Str::uuid(),
            ...injectAnneeScolaireId()
        ]);

        return new FraisInscriptionResource($frais);
    }

    public function show($id)
    {
        $frais = FraisInscription::findOrFail($id);
        return new FraisInscriptionResource($frais);
    }

    public function update(Request $request, $id)
    {
        if (!request()->user()->canManageFraisInscription()) {
            return response()->json(['message' => 'Action non autorisée'], 403);
        }

        $request->validate([
            'montant' => 'required|integer|min:0',
            'niveau_id' => 'nullable|exists:niveaux,id',
            'filiere_id' => 'nullable|exists:filieres,id',
        ]);

        $frais = FraisInscription::findOrFail($id);

        if ($frais->has_payments) {
            return response()->json(['message' => 'Impossible de modifier ce tarif car il est déjà lié à des paiements.'], 422);
        }

        $frais->update($request->only('montant', 'niveau_id', 'filiere_id'));

        return new FraisInscriptionResource($frais);
    }

    public function destroy($id)
    {
        if (!request()->user()->canManageFraisInscription()) {
            return response()->json(['message' => 'Action non autorisée'], 403);
        }

        $frais = FraisInscription::findOrFail($id);

        if ($frais->has_payments) {
            return response()->json(['message' => 'Impossible de supprimer ce tarif car il est déjà lié à des paiements.'], 422);
        }

        $frais->delete();
        return response()->json(['message' => 'Frais supprimé avec succès']);
    }

    public function activate($id)
    {
        if (!request()->user()->canManageFraisInscription()) {
            return response()->json(['message' => 'Action non autorisée'], 403);
        }

        $frais = FraisInscription::findOrFail($id);
        
        // On bascule simplement l'état actuel (sans impacter les autres)
        $frais->update(['active' => !$frais->active]);

        return new FraisInscriptionResource($frais);
    }
}
