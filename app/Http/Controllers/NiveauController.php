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

    public function getPeriodes($id)
    {
        $niveau = Niveau::findOrFail($id);
        return response()->json($niveau->periodes);
    }

    public function assignPeriodes(Request $request, $id)
    {
        $request->validate([
            'periode_ids' => 'required|array',
            'periode_ids.*' => 'exists:periodes,id',
        ]);

        $niveau = Niveau::findOrFail($id);
        $niveau->periodes()->sync($request->periode_ids);

        return response()->json(['message' => 'Périodes associées avec succès']);
    }

    public function getDocumentRequirements(Request $request, $id)
    {
        $filiereId = $request->query('filiere_id');
        $requirements = \App\Models\DocumentRequirement::where('niveau_id', $id)
            ->where(function($q) use ($filiereId) {
                $q->whereNull('filiere_id');
                if ($filiereId) {
                    $q->orWhere('filiere_id', $filiereId);
                }
            })->get();

        return response()->json($requirements);
    }

    public function getDocumentRequirementsAdmin($id)
    {
        $requirements = \App\Models\DocumentRequirement::where('niveau_id', $id)->with('filiere')->get();
        return response()->json($requirements);
    }

    public function storeDocumentRequirement(Request $request, $id)
    {
        $request->validate([
            'nom_affichage' => 'required|string',
            'document_key' => 'required|string',
            'is_obligatoire' => 'boolean',
            'is_multiple' => 'boolean',
            'filiere_id' => 'nullable|exists:filieres,id'
        ]);

        $req = \App\Models\DocumentRequirement::create([
            'niveau_id' => $id,
            'nom_affichage' => $request->nom_affichage,
            'document_key' => \Illuminate\Support\Str::slug($request->document_key, '_'),
            'is_obligatoire' => $request->is_obligatoire ?? true,
            'is_multiple' => $request->is_multiple ?? false,
            'filiere_id' => $request->filiere_id
        ]);

        return response()->json($req);
    }

    public function updateDocumentRequirement(Request $request, $docId)
    {
        $request->validate([
            'nom_affichage' => 'required|string',
            'document_key' => 'required|string',
            'is_obligatoire' => 'boolean',
            'is_multiple' => 'boolean',
            'filiere_id' => 'nullable|exists:filieres,id'
        ]);

        $req = \App\Models\DocumentRequirement::findOrFail($docId);
        $req->update([
            'nom_affichage' => $request->nom_affichage,
            'document_key' => \Illuminate\Support\Str::slug($request->document_key, '_'),
            'is_obligatoire' => $request->is_obligatoire ?? true,
            'is_multiple' => $request->is_multiple ?? false,
            'filiere_id' => $request->filiere_id
        ]);

        return response()->json($req);
    }

    public function destroyDocumentRequirement($id)
    {
        \App\Models\DocumentRequirement::destroy($id);
        return response()->json(['message' => 'Document requirement deleted']);
    }
}
