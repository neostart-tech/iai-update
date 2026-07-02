<?php

namespace App\Http\Controllers;

use App\Models\DocumentType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DocumentTypeController extends Controller
{
    public function index()
    {
        return response()->json(DocumentType::orderBy('nom_affichage')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom_affichage' => 'required|string|max:255',
            'document_key' => 'required|string|unique:document_types,document_key',
            'is_multiple' => 'boolean',
            'is_photo' => 'boolean',
            'accepted_formats' => 'nullable|string|in:image,pdf,all',
        ]);

        $documentType = DocumentType::create([
            'nom_affichage' => $request->nom_affichage,
            'document_key' => Str::slug($request->document_key, '_'),
            'is_multiple' => $request->is_multiple ?? false,
            'is_photo' => $request->is_photo ?? false,
            'accepted_formats' => $request->accepted_formats ?? 'all',
        ]);

        return response()->json($documentType);
    }

    public function update(Request $request, $id)
    {
        $documentType = DocumentType::findOrFail($id);

        $request->validate([
            'nom_affichage' => 'required|string|max:255',
            'document_key' => 'required|string|unique:document_types,document_key,' . $id,
            'is_multiple' => 'boolean',
            'is_photo' => 'boolean',
            'accepted_formats' => 'nullable|string|in:image,pdf,all',
        ]);

        $documentType->update([
            'nom_affichage' => $request->nom_affichage,
            'document_key' => Str::slug($request->document_key, '_'),
            'is_multiple' => $request->is_multiple ?? false,
            'is_photo' => $request->is_photo ?? false,
            'accepted_formats' => $request->accepted_formats ?? 'all',
        ]);

        return response()->json($documentType);
    }

    public function destroy($id)
    {
        // On empêche la suppression si c'est utilisé
        $documentType = DocumentType::withCount('documentRequirements')->findOrFail($id);
        
        if ($documentType->document_requirements_count > 0) {
            return response()->json(['message' => 'Impossible de supprimer ce document car il est exigé dans certains niveaux.'], 422);
        }

        $documentType->delete();
        return response()->json(['message' => 'Document supprimé avec succès']);
    }
}
