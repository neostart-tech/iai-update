<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ExamQuestion;
use App\Models\ExamPart;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ExamQuestionController extends Controller
{
    /**
     * GET /api/exam-parts/{partId}/questions
     */
    public function index(int $partId): JsonResponse
    {
        try {
            $questions = ExamQuestion::where('part_id', $partId)
                                     ->where('is_active', true)
                                     ->orderBy('order')
                                     ->with([
                                         'options', 
                                         'complexData',      // Pour tableaux dynamiques
                                         'structuredData',   // Pour données structurées
                                         'multiParts',       // Pour multi-parties
                                         'guidedWriting'     // Pour rédaction guidée
                                     ])
                                     ->get();
            
            // Transformer les données pour inclure les cell_data
            $questions->transform(function($question) {
                if ($question->type === 'complex_data' && $question->complexData) {
                    // Formater les données pour le frontend
                    $complexData = $question->complexData;
                    
                    // Structure complète des données
                    $formattedData = [
                        'data_type' => $complexData->data_type,
                        'configuration' => $complexData->configuration,
                        'cell_configuration' => $complexData->cell_configuration,
                        'data' => $complexData->data ?? [],
                        'cell_data' => $complexData->cell_data ?? []
                    ];
                    
                    $question->complex_data = $formattedData;
                }
                return $question;
            });
            
            return response()->json([
                'success' => true,
                'message' => 'Questions récupérées avec succès',
                'data' => $questions
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des questions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/exam-questions
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'part_id' => 'required|exists:exam_parts,id',
            'content' => 'required|string',
            'type' => 'required|in:qcm_unique,qcm_multiple,texte_court,texte_long,vrai_faux,appariement,ordre,fichier,complex_data,structured_data,multi_parts,guided_writing',
            'config' => 'nullable|array',
            'points' => 'numeric|min:0|max:20',
            'order' => 'nullable|integer',
            'metadata' => 'nullable|array',
            'options' => 'nullable|array',
            
            // Données complexes
            'complex_data' => 'nullable|array',
            'complex_data.data_type' => 'nullable|string',
            'complex_data.configuration' => 'nullable|array',
            'complex_data.cell_configuration' => 'nullable|array',
            'complex_data.data' => 'nullable|array',
            'complex_data.cell_data' => 'nullable|array',
            
            // Autres types
            'structured_data' => 'nullable|array',
            'multi_parts_data' => 'nullable|array',
            'guided_writing_data' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Vérifier que les points ne dépassent pas 20
            if (!$this->validatePoints($request->points)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Les points ne peuvent pas dépasser 20'
                ], 400);
            }

            // Déterminer l'ordre si non fourni
            if (!$request->has('order')) {
                $maxOrder = ExamQuestion::where('part_id', $request->part_id)->max('order') ?? 0;
                $request->merge(['order' => $maxOrder + 1]);
            }

            // Créer la question
            $question = ExamQuestion::create([
                'part_id' => $request->part_id,
                'content' => $request->content,
                'type' => $request->type,
                'config' => $request->config ?? null,
                'points' => $request->points ?? 0,
                'order' => $request->order ?? 0,
                'is_active' => true,
                'metadata' => $request->metadata ?? null
            ]);

            // Gérer les options (QCM, Vrai/Faux)
            if (in_array($question->type, ['qcm_unique', 'qcm_multiple', 'vrai_faux']) && $request->has('options')) {
                foreach ($request->options as $index => $optionData) {
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

            // Gérer les données complexes (TABLEAUX DYNAMIQUES)
            if ($question->type === 'complex_data' && $request->has('complex_data')) {
                $complexData = $request->complex_data;
                
                // Préparer les données à sauvegarder
                $dataToSave = [
                    'question_id' => $question->id,
                    'data_type' => $complexData['data_type'] ?? 'tableau_analyse',
                    'configuration' => $complexData['configuration'] ?? null,
                ];
                
                // Sauvegarder les données du tableau (les valeurs des cellules)
                if (isset($complexData['data'])) {
                    $dataToSave['data'] = $complexData['data'];
                }
                
                // Sauvegarder la configuration cellule par cellule (type: data ou question)
                if (isset($complexData['cell_configuration'])) {
                    $dataToSave['cell_configuration'] = $complexData['cell_configuration'];
                }
                
                // Sauvegarder les données cellule par cellule (pour les cellules de type data)
                if (isset($complexData['cell_data'])) {
                    $dataToSave['cell_data'] = $complexData['cell_data'];
                }
                
                // Ajouter les métadonnées si présentes
                if (isset($complexData['metadata'])) {
                    $dataToSave['metadata'] = $complexData['metadata'];
                }
                
                $question->complexData()->create($dataToSave);
            }

            // Gérer les données structurées
            if ($question->type === 'structured_data' && $request->has('structured_data')) {
                $question->structuredData()->create([
                    'structure_type' => $request->structured_data['structure_type'] ?? 'strategies_4t',
                    'structure' => $request->structured_data['structure'] ?? [],
                    'items' => $request->structured_data['items'] ?? [],
                    'bareme' => $request->structured_data['bareme'] ?? null
                ]);
            }

            // Gérer les questions à plusieurs parties
            if ($question->type === 'multi_parts' && $request->has('multi_parts_data')) {
                $question->multiParts()->create([
                    'configuration' => $request->multi_parts_data['configuration'] ?? null,
                    'parts' => $request->multi_parts_data['parts'] ?? []
                ]);
            }

            // Gérer la rédaction guidée
            if ($question->type === 'guided_writing' && $request->has('guided_writing_data')) {
                $question->guidedWriting()->create([
                    'instructions' => $request->guided_writing_data['instructions'] ?? [],
                    'criteria' => $request->guided_writing_data['criteria'] ?? null,
                    'min_words' => $request->guided_writing_data['min_words'] ?? 50,
                    'max_words' => $request->guided_writing_data['max_words'] ?? 500
                ]);
            }

            DB::commit();

            // Recharger la question avec toutes ses relations
            $question = ExamQuestion::with([
                'options', 
                'complexData', 
                'structuredData', 
                'multiParts', 
                'guidedWriting'
            ])->find($question->id);

            return response()->json([
                'success' => true,
                'message' => 'Question créée avec succès',
                'data' => $this->formatQuestion($question)
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la question',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/exam-questions/{id}
     */
    public function show(ExamQuestion $examQuestion): JsonResponse
    {
        try {
            $examQuestion->load([
                'options', 
                'complexData', 
                'structuredData', 
                'multiParts', 
                'guidedWriting',
                'part'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Question récupérée avec succès',
                'data' => $this->formatQuestion($examQuestion)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de la question',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * PUT /api/exam-questions/{id}
     */
    public function update(Request $request, ExamQuestion $examQuestion): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'content' => 'sometimes|string',
            'type' => 'sometimes|in:qcm_unique,qcm_multiple,texte_court,texte_long,vrai_faux,appariement,ordre,fichier,complex_data,structured_data,multi_parts,guided_writing',
            'config' => 'nullable|array',
            'points' => 'sometimes|numeric|min:0|max:20',
            'order' => 'sometimes|integer',
            'is_active' => 'sometimes|boolean',
            'metadata' => 'nullable|array',
            'options' => 'nullable|array',
            
            // Données complexes
            'complex_data' => 'nullable|array',
            'complex_data.data_type' => 'nullable|string',
            'complex_data.configuration' => 'nullable|array',
            'complex_data.cell_configuration' => 'nullable|array',
            'complex_data.data' => 'nullable|array',
            'complex_data.cell_data' => 'nullable|array',
            
            // Autres types
            'structured_data' => 'nullable|array',
            'multi_parts_data' => 'nullable|array',
            'guided_writing_data' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Vérifier les points
            if ($request->has('points') && !$this->validatePoints($request->points)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Les points ne peuvent pas dépasser 20'
                ], 400);
            }

            // Mettre à jour la question
            $examQuestion->update($request->only([
                'content', 'type', 'config', 'points', 'order', 'is_active', 'metadata'
            ]));

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

            // Mettre à jour les données complexes (TABLEAUX DYNAMIQUES)
            if ($examQuestion->type === 'complex_data' && $request->has('complex_data')) {
                $complexData = $request->complex_data;
                
                $dataToUpdate = [
                    'data_type' => $complexData['data_type'] ?? 'tableau_analyse',
                ];
                
                // Mettre à jour la configuration
                if (isset($complexData['configuration'])) {
                    $dataToUpdate['configuration'] = $complexData['configuration'];
                }
                
                // Mettre à jour les données du tableau
                if (isset($complexData['data'])) {
                    $dataToUpdate['data'] = $complexData['data'];
                }
                
                // Mettre à jour la configuration cellule par cellule
                if (isset($complexData['cell_configuration'])) {
                    $dataToUpdate['cell_configuration'] = $complexData['cell_configuration'];
                }
                
                // Mettre à jour les données cellule par cellule
                if (isset($complexData['cell_data'])) {
                    $dataToUpdate['cell_data'] = $complexData['cell_data'];
                }
                
                if (isset($complexData['metadata'])) {
                    $dataToUpdate['metadata'] = $complexData['metadata'];
                }
                
                $examQuestion->complexData()->updateOrCreate(
                    ['question_id' => $examQuestion->id],
                    $dataToUpdate
                );
            }

            // Mettre à jour les données structurées
            if ($examQuestion->type === 'structured_data' && $request->has('structured_data')) {
                $examQuestion->structuredData()->updateOrCreate(
                    ['question_id' => $examQuestion->id],
                    [
                        'structure_type' => $request->structured_data['structure_type'] ?? 'strategies_4t',
                        'structure' => $request->structured_data['structure'] ?? [],
                        'items' => $request->structured_data['items'] ?? [],
                        'bareme' => $request->structured_data['bareme'] ?? null
                    ]
                );
            }

            // Mettre à jour les questions à plusieurs parties
            if ($examQuestion->type === 'multi_parts' && $request->has('multi_parts_data')) {
                $examQuestion->multiParts()->updateOrCreate(
                    ['question_id' => $examQuestion->id],
                    [
                        'configuration' => $request->multi_parts_data['configuration'] ?? null,
                        'parts' => $request->multi_parts_data['parts'] ?? []
                    ]
                );
            }

            // Mettre à jour la rédaction guidée
            if ($examQuestion->type === 'guided_writing' && $request->has('guided_writing_data')) {
                $examQuestion->guidedWriting()->updateOrCreate(
                    ['question_id' => $examQuestion->id],
                    [
                        'instructions' => $request->guided_writing_data['instructions'] ?? [],
                        'criteria' => $request->guided_writing_data['criteria'] ?? null,
                        'min_words' => $request->guided_writing_data['min_words'] ?? 50,
                        'max_words' => $request->guided_writing_data['max_words'] ?? 500
                    ]
                );
            }

            DB::commit();

            // Recharger la question avec toutes ses relations
            $examQuestion = ExamQuestion::with([
                'options', 
                'complexData', 
                'structuredData', 
                'multiParts', 
                'guidedWriting'
            ])->find($examQuestion->id);

            return response()->json([
                'success' => true,
                'message' => 'Question mise à jour avec succès',
                'data' => $this->formatQuestion($examQuestion)
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la question',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/exam-questions/{id}
     */
    public function destroy(ExamQuestion $examQuestion): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Supprimer les relations
            $examQuestion->options()->delete();
            $examQuestion->complexData()->delete();
            $examQuestion->structuredData()->delete();
            $examQuestion->multiParts()->delete();
            $examQuestion->guidedWriting()->delete();
            
            // Supprimer la question
            $examQuestion->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Question supprimée avec succès'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la question',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/exam-questions/reorder
     */
    public function reorder(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'questions' => 'required|array',
            'questions.*.id' => 'required|exists:exam_questions,id',
            'questions.*.order' => 'required|integer|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            foreach ($request->questions as $qData) {
                ExamQuestion::where('id', $qData['id'])
                           ->update(['order' => $qData['order']]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ordre des questions mis à jour avec succès'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du réordonnancement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/exam-questions/part/{partId}/stats
     */
    public function partStats(int $partId): JsonResponse
    {
        try {
            $questions = ExamQuestion::where('part_id', $partId)->get();

            $stats = [
                'total' => $questions->count(),
                'total_points' => $questions->sum('points'),
                'par_type' => $questions->groupBy('type')->map->count(),
                'moyenne_points' => $questions->avg('points'),
                'questions_actives' => $questions->where('is_active', true)->count()
            ];

            return response()->json([
                'success' => true,
                'message' => 'Statistiques récupérées avec succès',
                'data' => $stats
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du calcul des statistiques',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Valider que les points ne dépassent pas 20
     */
    private function validatePoints($points): bool
    {
        return $points >= 0 && $points <= 20;
    }

    /**
     * Formater la question pour la réponse JSON
     */
    private function formatQuestion(ExamQuestion $question): array
    {
        $data = $question->toArray();

        // Formater les données complexes si présentes
        if ($question->complexData) {
            $data['complex_data'] = [
                'id' => $question->complexData->id,
                'question_id' => $question->complexData->question_id,
                'data_type' => $question->complexData->data_type,
                'configuration' => $question->complexData->configuration,
                'cell_configuration' => $question->complexData->cell_configuration,
                'data' => $question->complexData->data,
                'cell_data' => $question->complexData->cell_data,
                'metadata' => $question->complexData->metadata,
                'created_at' => $question->complexData->created_at,
                'updated_at' => $question->complexData->updated_at
            ];
            // Ne pas supprimer complex_data du tableau, on veut qu'il apparaisse
        }

        // Formater les données structurées
        if ($question->structuredData) {
            $data['structured_data'] = $question->structuredData;
        }

        // Formater les multi-parts
        if ($question->multiParts) {
            $data['multi_parts_data'] = [
                'id' => $question->multiParts->id,
                'question_id' => $question->multiParts->question_id,
                'configuration' => $question->multiParts->configuration,
                'parts' => $question->multiParts->parts,
                'created_at' => $question->multiParts->created_at,
                'updated_at' => $question->multiParts->updated_at
            ];
        }

        // Formater la rédaction guidée
        if ($question->guidedWriting) {
            $data['guided_writing_data'] = [
                'id' => $question->guidedWriting->id,
                'question_id' => $question->guidedWriting->question_id,
                'instructions' => $question->guidedWriting->instructions,
                'criteria' => $question->guidedWriting->criteria,
                'min_words' => $question->guidedWriting->min_words,
                'max_words' => $question->guidedWriting->max_words,
                'created_at' => $question->guidedWriting->created_at,
                'updated_at' => $question->guidedWriting->updated_at
            ];
        }

        return $data;
    }

    /**
     * Dupliquer une question
     * POST /api/exam-questions/{id}/duplicate
     */
    public function duplicate(int $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $originalQuestion = ExamQuestion::with([
                'options', 
                'complexData', 
                'structuredData', 
                'multiParts', 
                'guidedWriting'
            ])->findOrFail($id);

            // Créer la nouvelle question
            $newQuestion = $originalQuestion->replicate();
            $newQuestion->order = ExamQuestion::where('part_id', $originalQuestion->part_id)->max('order') + 1;
            $newQuestion->is_active = true;
            $newQuestion->save();

            // Dupliquer les options
            foreach ($originalQuestion->options as $option) {
                $newOption = $option->replicate();
                $newOption->question_id = $newQuestion->id;
                $newOption->save();
            }

            // Dupliquer les données complexes
            if ($originalQuestion->complexData) {
                $newComplexData = $originalQuestion->complexData->replicate();
                $newComplexData->question_id = $newQuestion->id;
                $newComplexData->save();
            }

            // Dupliquer les données structurées
            if ($originalQuestion->structuredData) {
                $newStructuredData = $originalQuestion->structuredData->replicate();
                $newStructuredData->question_id = $newQuestion->id;
                $newStructuredData->save();
            }

            // Dupliquer les multi-parts
            if ($originalQuestion->multiParts) {
                $newMultiParts = $originalQuestion->multiParts->replicate();
                $newMultiParts->question_id = $newQuestion->id;
                $newMultiParts->save();
            }

            // Dupliquer la rédaction guidée
            if ($originalQuestion->guidedWriting) {
                $newGuidedWriting = $originalQuestion->guidedWriting->replicate();
                $newGuidedWriting->question_id = $newQuestion->id;
                $newGuidedWriting->save();
            }

            DB::commit();

            // Recharger la nouvelle question avec ses relations
            $newQuestion = ExamQuestion::with([
                'options', 
                'complexData', 
                'structuredData', 
                'multiParts', 
                'guidedWriting'
            ])->find($newQuestion->id);

            return response()->json([
                'success' => true,
                'message' => 'Question dupliquée avec succès',
                'data' => $this->formatQuestion($newQuestion)
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la duplication',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}