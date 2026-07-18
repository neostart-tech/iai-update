<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\TypeDiplome;
use App\Models\TypeDiplomeChamp;
use Illuminate\Http\Request;

class TypeDiplomeController extends Controller
{
    public function index()
    {
        return response()->json(TypeDiplome::with('champs')->orderBy('ordre')->get());
    }

    public function champsDisponibles()
    {
        return response()->json(TypeDiplomeChamp::CHAMPS_DISPONIBLES);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255', 'unique:type_diplomes,nom'],
            'actif' => ['boolean'],
            'ordre' => ['nullable', 'integer', 'unique:type_diplomes,ordre'],
            'champs' => ['nullable', 'array'],
            'champs.*.champ_key' => ['required', 'string', 'in:' . implode(',', array_keys(TypeDiplomeChamp::CHAMPS_DISPONIBLES))],
            'champs.*.obligatoire' => ['required', 'boolean'],
        ], [
            'ordre.unique' => "Cet ordre d'affichage est déjà utilisé par un autre type de diplôme.",
        ]);

        $type = TypeDiplome::create([
            'nom' => $validated['nom'],
            'actif' => $validated['actif'] ?? true,
            'ordre' => $validated['ordre'] ?? (TypeDiplome::max('ordre') + 1),
        ]);

        foreach ($validated['champs'] ?? [] as $champ) {
            $type->champs()->create($champ);
        }

        return response()->json($type->load('champs'), 201);
    }

    public function update(Request $request, $id)
    {
        $type = TypeDiplome::findOrFail($id);

        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255', 'unique:type_diplomes,nom,' . $id],
            'actif' => ['boolean'],
            'ordre' => ['nullable', 'integer', 'unique:type_diplomes,ordre,' . $id],
            'champs' => ['nullable', 'array'],
            'champs.*.champ_key' => ['required', 'string', 'in:' . implode(',', array_keys(TypeDiplomeChamp::CHAMPS_DISPONIBLES))],
            'champs.*.obligatoire' => ['required', 'boolean'],
        ], [
            'ordre.unique' => "Cet ordre d'affichage est déjà utilisé par un autre type de diplôme.",
        ]);

        $type->update([
            'nom' => $validated['nom'],
            'actif' => $validated['actif'] ?? $type->actif,
            'ordre' => $validated['ordre'] ?? $type->ordre,
        ]);

        // Remplace entièrement le jeu de champs affichés pour ce type — l'absence
        // d'une clé dans le payload signifie "champ caché" pour ce diplôme.
        if ($request->has('champs')) {
            $type->champs()->delete();
            foreach ($validated['champs'] as $champ) {
                $type->champs()->create($champ);
            }
        }

        return response()->json($type->load('champs'));
    }

    public function destroy($id)
    {
        $type = TypeDiplome::withCount('candidatures')->findOrFail($id);

        if ($type->candidatures_count > 0) {
            return response()->json(['message' => 'Impossible de supprimer ce type de diplôme : des candidatures y sont déjà rattachées. Désactivez-le plutôt.'], 422);
        }

        $type->delete();
        return response()->json(['message' => 'Type de diplôme supprimé avec succès']);
    }
}
