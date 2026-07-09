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
        
        $requirements = \App\Models\DocumentRequirement::with('documentType')->where('niveau_id', $id)
            ->where(function($q) use ($filiereId) {
                $q->whereNull('filiere_id');
                if ($filiereId) {
                    $q->orWhere('filiere_id', $filiereId);
                }
            })->get();

        $formatted = $requirements->map(function($req) {
            return [
                'id' => $req->id,
                'niveau_id' => $req->niveau_id,
                'filiere_id' => $req->filiere_id,
                'document_type_id' => $req->document_type_id,
                'is_obligatoire' => $req->is_obligatoire,
                'nom_affichage' => $req->documentType->nom_affichage ?? '',
                'document_key' => $req->documentType->document_key ?? '',
                'is_multiple' => $req->documentType->is_multiple ?? false,
                'is_photo' => $req->documentType->is_photo ?? false,
                'accepted_formats' => $req->documentType->accepted_formats ?? 'all',
                'description' => $req->description,
            ];
        });

        return response()->json($formatted);
    }

    public function getDocumentRequirementsAdmin($id)
    {
        $requirements = \App\Models\DocumentRequirement::where('niveau_id', $id)->with(['filiere', 'documentType'])->get();
        
        $formatted = $requirements->map(function($req) {
            return [
                'id' => $req->id,
                'niveau_id' => $req->niveau_id,
                'filiere_id' => $req->filiere_id,
                'document_type_id' => $req->document_type_id,
                'is_obligatoire' => $req->is_obligatoire,
                'nom_affichage' => $req->documentType->nom_affichage ?? '',
                'document_key' => $req->documentType->document_key ?? '',
                'is_multiple' => $req->documentType->is_multiple ?? false,
                'is_photo' => $req->documentType->is_photo ?? false,
                'accepted_formats' => $req->documentType->accepted_formats ?? 'all',
                'description' => $req->description,
                'filiere' => $req->filiere,
            ];
        });

        return response()->json($formatted);
    }

    public function storeDocumentRequirement(Request $request, $id)
    {
        $request->validate([
            'document_type_id' => [
                'required',
                'exists:document_types,id',
                \Illuminate\Validation\Rule::unique('document_requirements')->where(function ($query) use ($id, $request) {
                    return $query->where('niveau_id', $id)
                                 ->where('filiere_id', $request->filiere_id);
                })
            ],
            'is_obligatoire' => 'boolean',
            'filiere_id' => 'nullable|exists:filieres,id',
            'description' => 'nullable|string|max:1000'
        ], [
            'document_type_id.unique' => 'Ce document est déjà exigé pour ce niveau/filière.'
        ]);

        $req = \App\Models\DocumentRequirement::create([
            'niveau_id' => $id,
            'document_type_id' => $request->document_type_id,
            'is_obligatoire' => $request->is_obligatoire ?? true,
            'filiere_id' => $request->filiere_id,
            'description' => $request->description
        ]);

        return response()->json($req);
    }

    public function updateDocumentRequirement(Request $request, $docId)
    {
        $request->validate([
            'document_type_id' => 'required|exists:document_types,id',
            'is_obligatoire' => 'boolean',
            'filiere_id' => 'nullable|exists:filieres,id',
            'description' => 'nullable|string|max:1000'
        ]);

        $req = \App\Models\DocumentRequirement::findOrFail($docId);
        $req->update([
            'document_type_id' => $request->document_type_id,
            'is_obligatoire' => $request->is_obligatoire ?? true,
            'filiere_id' => $request->filiere_id,
            'description' => $request->description
        ]);

        return response()->json($req);
    }

    public function destroyDocumentRequirement($id)
    {
        \App\Models\DocumentRequirement::destroy($id);
        return response()->json(['message' => 'Document requirement deleted']);
    }
}
