<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ExamQuestion;
use App\Models\ExamPart;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ExamQuestionController extends Controller
{
    /**
     * GET /api/exam-parts/{partId}/questions
     */
    public function index(int $partId): JsonResponse
    {
        $questions = ExamQuestion::where('part_id', $partId)
                                 ->where('is_active', true)
                                 ->orderBy('order')
                                 ->with('options')
                                 ->get();
        
        return response()->json([
            'success' => true,
            'data' => $questions
        ]);
    }

    /**
     * POST /api/exam-questions
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'part_id' => 'required|exists:exam_parts,id',
            'content' => 'required|string',
            'type' => 'required|in:qcm_unique,qcm_multiple,texte_court,texte_long,vrai_faux,appariement,ordre,fichier',
            'config' => 'nullable|array',
            'points' => 'min:0',
            'order' => 'integer',
            'metadata' => 'nullable|array',
            'options' => 'nullable|array' // Ajouter la validation des options
        ]);

        // Créer la question
        $question = ExamQuestion::create([
            'part_id' => $validated['part_id'],
            'content' => $validated['content'],
            'type' => $validated['type'],
            'config' => $validated['config'] ?? null,
            'points' => $validated['points'] ?? 0,
            'order' => $validated['order'] ?? 0,
            'metadata' => $validated['metadata'] ?? null
        ]);

        // Si c'est un QCM et qu'il y a des options, les créer
        if (in_array($question->type, ['qcm_unique', 'qcm_multiple', 'vrai_faux']) && $request->has('options')) {
            foreach ($request->options as $index => $optionData) {
                // S'assurer que l'option a un texte
                if (!empty($optionData['text'])) {
                    $question->options()->create([
                        'text' => $optionData['text'],
                        'is_correct' => $optionData['is_correct'] ?? false,
                        'order' => $optionData['order'] ?? $index + 1,
                        'metadata' => $optionData['metadata'] ?? null
                    ]);
                }
            }
        }

        // Recharger la question avec ses options
        $question->load('options');

        return response()->json([
            'success' => true,
            'message' => 'Question créée avec succès',
            'data' => $question
        ], 201);
    }

    /**
     * GET /api/exam-questions/{id}
     */
    public function show(ExamQuestion $examQuestion): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $examQuestion->load('options')
        ]);
    }

    /**
     * PUT /api/exam-questions/{id}
     */
    public function update(Request $request, ExamQuestion $examQuestion): JsonResponse
    {
        $validated = $request->validate([
            'content' => 'string',
            'type' => 'in:qcm_unique,qcm_multiple,texte_court,texte_long,vrai_faux,appariement,ordre,fichier',
            'config' => 'nullable|array',
            'points' => 'integer|min:0',
            'order' => 'integer',
            'is_active' => 'boolean',
            'metadata' => 'nullable|array',
            'options' => 'nullable|array'
        ]);

        // Mettre à jour la question
        $examQuestion->update($validated);

        // Mettre à jour les options si nécessaire
        if (in_array($examQuestion->type, ['qcm_unique', 'qcm_multiple', 'vrai_faux']) && $request->has('options')) {
            // Supprimer les anciennes options
            $examQuestion->options()->delete();
            
            // Créer les nouvelles options
            foreach ($request->options as $index => $optionData) {
                if (!empty($optionData['text'])) {
                    $examQuestion->options()->create([
                        'text' => $optionData['text'],
                        'is_correct' => $optionData['is_correct'] ?? false,
                        'order' => $optionData['order'] ?? $index + 1,
                        'metadata' => $optionData['metadata'] ?? null
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Question mise à jour avec succès',
            'data' => $examQuestion->load('options')
        ]);
    }

    /**
     * DELETE /api/exam-questions/{id}
     */
    public function destroy(ExamQuestion $examQuestion): JsonResponse
    {
        $examQuestion->delete();

        return response()->json([
            'success' => true,
            'message' => 'Question supprimée avec succès'
        ]);
    }
}