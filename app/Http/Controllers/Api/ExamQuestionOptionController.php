<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ExamQuestionOption;
use App\Models\ExamQuestion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ExamQuestionOptionController extends Controller
{
    /**
     * GET /api/exam-questions/{questionId}/options
     */
    public function index(int $questionId): JsonResponse
    {
        try {
            $options = ExamQuestionOption::where('question_id', $questionId)
                                         ->orderBy('order')
                                         ->get();

            return response()->json([
                'success' => true,
                'message' => 'Options récupérées avec succès',
                'data' => $options,
                'total' => $options->count()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des options',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ➕ CRÉER une nouvelle option
     * POST /api/exam-question-options
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'question_id' => 'required|exists:exam_questions,id',
            'text' => 'required|string',
            'is_correct' => 'boolean',
            'metadata' => 'nullable|array',
            'order' => 'sometimes|integer|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Vérifier le type de question
            $question = ExamQuestion::find($request->question_id);
            
            // Pour vrai/faux, limiter à 2 options
            if ($question->type === 'vrai_faux') {
                $existingCount = ExamQuestionOption::where('question_id', $request->question_id)->count();
                if ($existingCount >= 2) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Une question Vrai/Faux ne peut avoir que 2 options'
                    ], 400);
                }
            }

            // Si order non fourni, mettre à la fin
            if (!$request->has('order')) {
                $maxOrder = ExamQuestionOption::where('question_id', $request->question_id)
                                              ->max('order') ?? 0;
                $request->merge(['order' => $maxOrder + 1]);
            }

            $option = ExamQuestionOption::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Option créée avec succès',
                'data' => $option
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/exam-question-options/{id}
     */
    public function show(ExamQuestionOption $examQuestionOption): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'Option récupérée avec succès',
                'data' => $examQuestionOption->load('question')
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * PUT /api/exam-question-options/{id}
     */
    public function update(Request $request, ExamQuestionOption $examQuestionOption): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'text' => 'sometimes|string',
            'is_correct' => 'sometimes|boolean',
            'metadata' => 'nullable|array',
            'order' => 'sometimes|integer|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $examQuestionOption->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Option mise à jour avec succès',
                'data' => $examQuestionOption->fresh()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/exam-question-options/{id}
     */
    public function destroy(ExamQuestionOption $examQuestionOption): JsonResponse
    {
        try {
            $examQuestionOption->delete();

            return response()->json([
                'success' => true,
                'message' => 'Option supprimée avec succès'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * PATCH /api/exam-question-options/{id}/mark-correct
     */
    public function markCorrect(ExamQuestionOption $examQuestionOption): JsonResponse
    {
        try {
            $question = $examQuestionOption->question;
            
            // Pour QCM unique, démarker les autres
            if ($question->type === 'qcm_unique') {
                ExamQuestionOption::where('question_id', $question->id)
                                 ->update(['is_correct' => false]);
            }
            
            $examQuestionOption->update(['is_correct' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Option marquée comme correcte',
                'data' => $examQuestionOption
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/exam-question-options/reorder
     */
    public function reorder(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'options' => 'required|array',
            'options.*.id' => 'required|exists:exam_question_options,id',
            'options.*.order' => 'required|integer|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            foreach ($request->options as $optData) {
                ExamQuestionOption::where('id', $optData['id'])
                                 ->update(['order' => $optData['order']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Ordre des options mis à jour avec succès'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du réordonnancement',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}