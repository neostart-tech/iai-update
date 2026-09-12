<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Matiere;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MatiereController extends Controller
{
    public function index()
    {
        // Retourne toutes les matières avec leurs affectations (programmations / UVs)
        $matieres = Matiere::with([
            'uniteValeurs.filiere',
            'uniteValeurs.niveau',
            'uniteValeurs.periode',
            'uniteValeurs.enseignants'
        ])->get();
        
        return response()->json(['data' => $matieres]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'code' => 'nullable|string|max:255',
        ]);

        $matiere = Matiere::create([
            'nom' => $request->nom,
            'code' => $request->code,
        ]);

        return response()->json(['data' => $matiere]);
    }

    public function update(Request $request, Matiere $matiere)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'code' => 'nullable|string|max:255',
        ]);

        $matiere->update([
            'nom' => $request->nom,
            'code' => $request->code,
        ]);

        return response()->json(['data' => $matiere]);
    }

    public function destroy(Matiere $matiere)
    {
        // Ne peut pas supprimer s'il y a des affectations
        if ($matiere->uniteValeurs()->count() > 0) {
            return response()->json(['message' => 'Impossible de supprimer cette matière car elle est affectée à des classes.'], 422);
        }

        $matiere->delete();
        return response()->json(['message' => 'Matière supprimée avec succès.']);
    }
}
