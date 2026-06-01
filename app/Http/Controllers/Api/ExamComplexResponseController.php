<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExamSubmission;
use Illuminate\Http\Request;

class ExamComplexResponseController extends Controller
{
      /**
     * 
     * @param Request $request
     * @param int $evaluationId
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveComplex(Request $request, $evaluationId)
    {
        $request->validate([
            'question_id' => 'required|exists:exam_questions,id',
            'reponse' => 'required|array',
            'etudiant_id' => 'required|exists:etudiants,id'
        ]);

        $reponse = $request->reponse;
        $type = $reponse['type'] ?? null;

        // Récupérer la soumission existante
        $existingSubmission = ExamSubmission::where([
            'evaluation_id' => $evaluationId,
            'etudiant_id' => $request->etudiant_id,
            'question_id' => $request->question_id
        ])->first();

        // Structure de réponse selon le type
        $formattedResponse = [];

        switch ($type) {
            case 'complex_data':
                // Pour une cellule de tableau
                if ($existingSubmission && isset($existingSubmission->reponse['type']) 
                    && $existingSubmission->reponse['type'] === 'complex_data') {
                    // Fusionner avec l'existant
                    $formattedResponse = $existingSubmission->reponse;
                    if (!isset($formattedResponse['data'])) {
                        $formattedResponse['data'] = [];
                    }
                    $key = $reponse['data']['cell_key'];
                    $formattedResponse['data'][$key] = $reponse['data']['value'];
                } else {
                    // Nouvelle réponse
                    $formattedResponse = [
                        'type' => 'complex_data',
                        'data' => [
                            $reponse['data']['cell_key'] => $reponse['data']['value']
                        ]
                    ];
                }
                break;

            case 'multi_parts':
                // Pour une partie d'une question multi-parties
                if ($existingSubmission && isset($existingSubmission->reponse['type'])
                    && $existingSubmission->reponse['type'] === 'multi_parts') {
                    // Fusionner avec l'existant
                    $formattedResponse = $existingSubmission->reponse;
                    if (!isset($formattedResponse['data'])) {
                        $formattedResponse['data'] = [];
                    }
                    $partKey = 'part_' . $reponse['data']['part'];
                    $formattedResponse['data'][$partKey] = $reponse['data']['value'];
                } else {
                    // Nouvelle réponse
                    $formattedResponse = [
                        'type' => 'multi_parts',
                        'data' => [
                            'part_' . $reponse['data']['part'] => $reponse['data']['value']
                        ]
                    ];
                }
                break;

            case 'structured_data':
                // Pour une question structurée
                if ($existingSubmission && isset($existingSubmission->reponse['type'])
                    && $existingSubmission->reponse['type'] === 'structured_data') {
                    // Fusionner avec l'existant
                    $formattedResponse = $existingSubmission->reponse;
                    if (!isset($formattedResponse['data'])) {
                        $formattedResponse['data'] = [];
                    }
                    $itemKey = 'item_' . $reponse['data']['item'];
                    $formattedResponse['data'][$itemKey] = $reponse['data']['value'];
                } else {
                    // Nouvelle réponse
                    $formattedResponse = [
                        'type' => 'structured_data',
                        'data' => [
                            'item_' . $reponse['data']['item'] => $reponse['data']['value']
                        ]
                    ];
                }
                break;

            case 'guided_writing':
                // Pour une rédaction guidée
                $formattedResponse = [
                    'type' => 'guided_writing',
                    'data' => $reponse['data']['text'] ?? '',
                    'word_count' => str_word_count($reponse['data']['text'] ?? '')
                ];
                break;

            default:
                // Réponse simple
                $formattedResponse = $reponse;
        }

        // Sauvegarder ou mettre à jour
        $submission = ExamSubmission::updateOrCreate(
            [
                'evaluation_id' => $evaluationId,
                'etudiant_id' => $request->etudiant_id,
                'question_id' => $request->question_id
            ],
            [
                'reponse' => $formattedResponse,
                'auto_saved_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Réponse sauvegardée avec succès',
            'data' => [
                'submission' => $submission
            ]
        ]);
    }

    /**
     * 🔴 IMPORTANT: Récupérer toutes les soumissions d'un étudiant
     * 
     * @param int $evaluationId
     * @param int $etudiantId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStudentSubmissions($evaluationId, $etudiantId)
    {
        $submissions = ExamSubmission::where('evaluation_id', $evaluationId)
            ->where('etudiant_id', $etudiantId)
            ->get()
            ->map(function ($submission) {
                $reponse = $submission->reponse;
                
                // Si c'est une réponse complexe, garder la structure
                if (isset($reponse['type'])) {
                    return [
                        'question_id' => $submission->question_id,
                        'reponse' => $reponse,
                        'submitted_at' => $submission->submitted_at,
                        'auto_saved_at' => $submission->auto_saved_at,
                        'points_obtenus' => $submission->points_obtenus,
                        'is_correct' => $submission->is_correct
                    ];
                }
                
                // Format standard
                return [
                    'question_id' => $submission->question_id,
                    'reponse' => $reponse,
                    'submitted_at' => $submission->submitted_at,
                    'auto_saved_at' => $submission->auto_saved_at,
                    'points_obtenus' => $submission->points_obtenus,
                    'is_correct' => $submission->is_correct
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'all' => $submissions
            ]
        ]);
    }
}
