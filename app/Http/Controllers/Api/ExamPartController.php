<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ExamPart;
use App\Models\Evaluation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ExamPartController extends Controller
{
    /**
     * GET /api/evaluations/{evaluationId}/parts
     */
    public function index(string $evaluationId): JsonResponse
    {
        $evalaution=Evaluation::where('slug',$evaluationId)->first()->getAttribute('id');
        $parts = ExamPart::where('evaluation_id', $evalaution)
                         ->orderBy('order')
                         ->get();
        
        return response()->json([
            'success' => true,
            'data' => $parts
        ]);
    }

    /**
     * POST /api/exam-parts
     */
 public function store(Request $request): JsonResponse
{
    $validated = $request->validate([
        'evaluation_id' => 'required|string', // On change la validation
        'titre' => 'required|string|max:255',
        'description' => 'nullable|string',
        'contexte' => 'nullable|string',
        'order' => 'integer',
        'metadata' => 'nullable|array'
    ]);

    // Trouver l'évaluation par son slug
    $evaluation = Evaluation::where('slug', $validated['evaluation_id'])->first();
    
    if (!$evaluation) {
        return response()->json([
            'success' => false,
            'message' => 'Évaluation non trouvée'
        ], 404);
    }

    // Créer la partie avec le vrai ID
    $part = ExamPart::create([
        'evaluation_id' => $evaluation->id, // Utiliser l'ID trouvé
        'titre' => $validated['titre'],
        'description' => $validated['description'] ?? null,
        'contexte' => $validated['contexte'] ?? null,
        'order' => $validated['order'] ?? 0,
        'metadata' => $validated['metadata'] ?? null
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Partie créée avec succès',
        'data' => $part
    ], 201);
}
    /**
     * GET /api/exam-parts/{id}
     */
    public function show(ExamPart $examPart): JsonResponse
    {
        // Charger les questions associées
        $examPart->load('questions');
        
        return response()->json([
            'success' => true,
            'data' => $examPart
        ]);
    }

    /**
     * PUT /api/exam-parts/{id}
     */
    public function update(Request $request, ExamPart $examPart): JsonResponse
    {
        $validated = $request->validate([
            'titre' => 'string|max:255',
            'description' => 'nullable|string',
            'contexte' => 'nullable|string',
            'order' => 'integer',
            'metadata' => 'nullable|array'
        ]);

        $examPart->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Partie mise à jour avec succès',
            'data' => $examPart
        ]);
    }

    /**
     * DELETE /api/exam-parts/{id}
     */
    public function destroy(ExamPart $examPart): JsonResponse
    {
        $examPart->delete();

        return response()->json([
            'success' => true,
            'message' => 'Partie supprimée avec succès'
        ]);
    }

    /**

     * POST /api/exam-parts/reorder
     */
    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'parts' => 'required|array',
            'parts.*.id' => 'required|exists:exam_parts,id',
            'parts.*.order' => 'required|integer'
        ]);

        foreach ($request->parts as $partData) {
            ExamPart::where('id', $partData['id'])
                    ->update(['order' => $partData['order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Ordre mis à jour avec succès'
        ]);
    }
}