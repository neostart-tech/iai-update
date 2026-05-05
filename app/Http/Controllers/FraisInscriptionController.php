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
        $frais = FraisInscription::with('anneeScolaire')->latest()->get();
        return FraisInscriptionResource::collection($frais);
    }

    public function store(Request $request)
    {
        if (!request()->user()->canManageFraisInscription()) {
            return response()->json(['message' => 'Action non autorisée'], 403);
        }

        $request->validate([
            'montant' => 'required|integer|min:0',
        ]);

        $frais = FraisInscription::create([
            'montant' => $request->montant,
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
        ]);

        $frais = FraisInscription::findOrFail($id);
        $frais->update($request->only('montant'));

        return new FraisInscriptionResource($frais);
    }

    public function destroy($id)
    {
        if (!request()->user()->canManageFraisInscription()) {
            return response()->json(['message' => 'Action non autorisée'], 403);
        }

        $frais = FraisInscription::findOrFail($id);
        $frais->delete();
        return response()->json(['message' => 'Frais supprimé avec succès']);
    }

    public function activate($id)
    {
        if (!request()->user()->canManageFraisInscription()) {
            return response()->json(['message' => 'Action non autorisée'], 403);
        }

        // On désactive tout
        FraisInscription::query()->update(['active' => false]);
        
        // On active celui-ci
        $frais = FraisInscription::findOrFail($id);
        $frais->update(['active' => true]);

        return new FraisInscriptionResource($frais);
    }
}
