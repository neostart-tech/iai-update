<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\MoyenConnaissance;
use Illuminate\Http\Request;

class MoyenConnaissanceController extends Controller
{
    public function index()
    {
        return response()->json(MoyenConnaissance::orderBy('ordre')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'libelle' => ['required', 'string', 'max:255', 'unique:moyens_connaissances,libelle'],
            'actif' => ['boolean'],
            'ordre' => ['nullable', 'integer', 'unique:moyens_connaissances,ordre'],
        ], [
            'ordre.unique' => "Cet ordre d'affichage est déjà utilisé par un autre moyen de connaissance.",
        ]);

        $moyen = MoyenConnaissance::create([
            'libelle' => $validated['libelle'],
            'actif' => $validated['actif'] ?? true,
            'ordre' => $validated['ordre'] ?? (MoyenConnaissance::max('ordre') + 1),
        ]);

        return response()->json($moyen, 201);
    }

    public function update(Request $request, $id)
    {
        $moyen = MoyenConnaissance::findOrFail($id);

        $validated = $request->validate([
            'libelle' => ['required', 'string', 'max:255', 'unique:moyens_connaissances,libelle,' . $id],
            'actif' => ['boolean'],
            'ordre' => ['nullable', 'integer', 'unique:moyens_connaissances,ordre,' . $id],
        ], [
            'ordre.unique' => "Cet ordre d'affichage est déjà utilisé par un autre moyen de connaissance.",
        ]);

        $moyen->update([
            'libelle' => $validated['libelle'],
            'actif' => $validated['actif'] ?? $moyen->actif,
            'ordre' => $validated['ordre'] ?? $moyen->ordre,
        ]);

        return response()->json($moyen);
    }

    public function destroy($id)
    {
        $moyen = MoyenConnaissance::withCount('candidatures')->findOrFail($id);

        if ($moyen->candidatures_count > 0) {
            return response()->json(['message' => 'Impossible de supprimer ce moyen de connaissance : des candidatures y sont déjà rattachées. Désactivez-le plutôt.'], 422);
        }

        $moyen->delete();
        return response()->json(['message' => 'Moyen de connaissance supprimé avec succès']);
    }
}
