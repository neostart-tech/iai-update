<?php

namespace App\Http\Controllers;

use App\Http\Resources\NiveauResource;
use App\Models\Niveau;
use Illuminate\Http\Request;

class NiveauController extends Controller
{
    public function index()
    {
        return NiveauResource::collection(Niveau::withoutGlobalScope('active')->orderBy('ordre')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'libelle' => 'required|string|max:255',
            'ordre' => 'nullable|integer',
            'code' => 'nullable|string|max:50',
        ]);

        $data = $request->all();
        $maxOrdre = Niveau::withoutGlobalScope('active')->max('ordre') ?? 0;

        // Si l'ordre est explicitement 0, on le garde.
        // Sinon, si l'ordre est vide ou nul, on incrémente.
        if (!isset($data['ordre']) || $data['ordre'] === null || $data['ordre'] === '') {
            $data['ordre'] = $maxOrdre + 1;
        } else if ($data['ordre'] != 0) {
            // Si c'est différent de 0, on vérifie qu'il n'est pas inférieur au max
            if ($data['ordre'] <= $maxOrdre) {
                 // Optionnel: On peut soit bloquer, soit simplement ajuster.
                 // Ici on retourne une erreur comme précédemment demandé.
                 return response()->json([
                    'message' => "L'ordre doit être soit 0, soit supérieur au dernier ordre enregistré ($maxOrdre)."
                ], 422);
            }
        }

        $niveau = Niveau::create($data);

        return new NiveauResource($niveau);
    }

    public function show($id)
    {
        $niveau = Niveau::withoutGlobalScope('active')->findOrFail($id);
        return new NiveauResource($niveau);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'libelle' => 'required|string|max:255',
            'ordre' => 'nullable|integer',
            'code' => 'nullable|string|max:50',
        ]);

        $niveau = Niveau::withoutGlobalScope('active')->findOrFail($id);
        $niveau->update($request->all());

        return new NiveauResource($niveau);
    }

    public function toggleStatus($id)
    {
        $niveau = Niveau::withoutGlobalScope('active')->findOrFail($id);
        $niveau->update(['active' => !$niveau->active]);

        return new NiveauResource($niveau);
    }
}
