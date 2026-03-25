<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Etudiant;
use App\Models\ExamSubmission;
use App\Models\ExamQuestion;
use App\Models\ExamSession;
use App\Models\Evaluation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ExamSubmissionController extends Controller
{
    /**
     * GET /api/exam/{evaluationSlug}/student/{etudiantId}/submissions
     * Le paramètre s'appelle $evaluationId mais c'est en réalité le slug
     */
    public function index(string $evaluationId, int $etudiantId): JsonResponse
    {
        try {
            // Trouver l'évaluation par son slug
            $evaluation = Evaluation::where('slug', $evaluationId)->firstOrFail();
            
            $submissions = ExamSubmission::where('evaluation_id', $evaluation->id)
                                         ->where('etudiant_id', $etudiantId)
                                         ->with(['question' => function($q) {
                                             $q->with('part');
                                         }])
                                         ->orderBy('created_at')
                                         ->get();

            // Grouper par partie
            $grouped = $submissions->groupBy(function($item) {
                return $item->question->part->titre ?? 'Sans partie';
            });

            return response()->json([
                'success' => true,
                'message' => 'Soumissions récupérées avec succès',
                'data' => [
                    'all' => $submissions,
                    'grouped_by_part' => $grouped,
                    'total' => $submissions->count(),
                    'total_points' => $submissions->sum('points_obtenus')
                ]
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
     * POST /api/exam/{evaluationSlug}/save
     */
    public function save(Request $request, string $evaluationId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'question_id' => 'required|exists:exam_questions,id',
            'reponse' => 'required',
            'etudiant_id' => 'required|exists:etudiants,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Trouver l'évaluation par son slug
            $evaluation = Evaluation::where('slug', $evaluationId)->firstOrFail();
            
            $etudiantId = $request->etudiant_id;
            $questionId = $request->question_id;

            // Vérifier que la question appartient bien à l'examen
            $question = ExamQuestion::find($questionId);
            $part = $question->part;
            if ($part->evaluation_id != $evaluation->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette question ne fait pas partie de cet examen'
                ], 400);
            }

            // Mettre à jour ou créer la soumission
            $submission = ExamSubmission::updateOrCreate(
                [
                    'evaluation_id' => $evaluation->id,
                    'etudiant_id' => $etudiantId,
                    'question_id' => $questionId
                ],
                [
                    'reponse' => $request->reponse,
                    'auto_saved_at' => now(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]
            );

            // Mettre à jour la session
            ExamSession::where('evaluation_id', $evaluation->id)
                       ->where('etudiant_id', $etudiantId)
                       ->where('status', 'en_cours')
                       ->update(['last_activity_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'Réponse sauvegardée automatiquement',
                'data' => [
                    'submission' => $submission,
                    'saved_at' => now()->toDateTimeString()
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la sauvegarde',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/exam/{evaluationSlug}/submit-question
     */
    public function submitQuestion(Request $request, string $evaluationId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'question_id' => 'required|exists:exam_questions,id',
            'reponse' => 'required',
            'etudiant_id' => 'required|exists:etudiants,id'
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

            // Trouver l'évaluation par son slug
            $evaluation = Evaluation::where('slug', $evaluationId)->firstOrFail();
            
            $etudiantId = $request->etudiant_id;
            $questionId = $request->question_id;
            $question = ExamQuestion::with('options')->find($questionId);

            // Vérifier que la question appartient à l'évaluation
            $part = $question->part;
            if ($part->evaluation_id != $evaluation->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette question ne fait pas partie de cet examen'
                ], 400);
            }

            // Correction automatique selon le type
            $correction = $this->autoCorrect($question, $request->reponse);

            // Créer ou mettre à jour la soumission
            $submission = ExamSubmission::updateOrCreate(
                [
                    'evaluation_id' => $evaluation->id,
                    'etudiant_id' => $etudiantId,
                    'question_id' => $questionId
                ],
                [
                    'reponse' => $request->reponse,
                    'is_correct' => $correction['is_correct'],
                    'points_obtenus' => $correction['points_obtenus'],
                    'submitted_at' => now(),
                    'auto_saved_at' => now(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Réponse soumise avec succès',
                'data' => [
                    'submission' => $submission,
                    'correction' => $correction
                ]
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la soumission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/exam/{evaluationSlug}/submit-all
     */
    public function submitAll(Request $request, string $evaluationId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'etudiant_id' => 'required|exists:etudiants,id'
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

            // Trouver l'évaluation par son slug
            $evaluation = Evaluation::where('slug', $evaluationId)->firstOrFail();
            
            $etudiantId = $request->etudiant_id;

            // Marquer toutes les soumissions non soumises
            ExamSubmission::where('evaluation_id', $evaluation->id)
                         ->where('etudiant_id', $etudiantId)
                         ->whereNull('submitted_at')
                         ->update(['submitted_at' => now()]);

            // Marquer la session comme terminée
            ExamSession::where('evaluation_id', $evaluation->id)
                      ->where('etudiant_id', $etudiantId)
                      ->where('status', 'en_cours')
                      ->update([
                          'status' => 'termine',
                          'submitted_at' => now()
                      ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Examen soumis avec succès',
                'data' => [
                    'submitted_at' => now()->toDateTimeString()
                ]
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la soumission finale',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/exam-submissions/{id}
     */
    public function show(ExamSubmission $examSubmission): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'Soumission récupérée avec succès',
                'data' => $examSubmission->load(['question', 'etudiant', 'evaluation'])
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
     * POST /api/exam-submissions/{id}/grade
     */
    public function grade(Request $request, ExamSubmission $examSubmission): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'points_obtenus' => 'required|numeric|min:0',
            'commentaire' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $examSubmission->update([
                'points_obtenus' => $request->points_obtenus,
                'metadata' => array_merge($examSubmission->metadata ?? [], [
                    'commentaire_correction' => $request->commentaire,
                    'corrige_par' => auth()->id(),
                    'corrige_le' => now()->toDateTimeString()
                ])
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Note attribuée avec succès',
                'data' => $examSubmission->fresh()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la notation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/exam/{evaluationSlug}/statistics
     */
    public function statistics(string $evaluationId): JsonResponse
    {
        try {
            // Trouver l'évaluation par son slug
            $evaluation = Evaluation::where('slug', $evaluationId)->firstOrFail();
            
            $submissions = ExamSubmission::where('evaluation_id', $evaluation->id)
                                        ->with('question')
                                        ->get();

            $stats = [
                'total_etudiants' => $submissions->groupBy('etudiant_id')->count(),
                'total_soumissions' => $submissions->count(),
                'moyenne_generale' => $submissions->avg('points_obtenus'),
                'par_question' => []
            ];

            // Statistiques par question
            $groupedByQuestion = $submissions->groupBy('question_id');
            foreach ($groupedByQuestion as $questionId => $qSubmissions) {
                $question = ExamQuestion::find($questionId);
                $stats['par_question'][] = [
                    'question_id' => $questionId,
                    'question_content' => substr($question->content, 0, 100) . '...',
                    'total_reponses' => $qSubmissions->count(),
                    'moyenne_points' => $qSubmissions->avg('points_obtenus'),
                    'taux_reussite' => $qSubmissions->where('is_correct', true)->count() / max($qSubmissions->count(), 1) * 100
                ];
            }

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
     * Correction automatique selon le type de question
     */
    private function autoCorrect(ExamQuestion $question, $reponse): array
    {
        $points = 0;
        $isCorrect = false;

        switch ($question->type) {
            case 'qcm_unique':
                // reponse = option_id
                $correctOption = $question->options->where('is_correct', true)->first();
                $isCorrect = ($correctOption && $reponse['option_id'] == $correctOption->id);
                $points = $isCorrect ? $question->points : 0;
                break;

            case 'qcm_multiple':
                // reponse = [option_ids]
                $correctOptions = $question->options->where('is_correct', true)->pluck('id')->toArray();
                sort($correctOptions);
                $userOptions = $reponse['option_ids'] ?? [];
                sort($userOptions);
                $isCorrect = ($correctOptions == $userOptions);
                $points = $isCorrect ? $question->points : 0;
                break;

            case 'vrai_faux':
                $correctOption = $question->options->where('is_correct', true)->first();
                $isCorrect = ($correctOption && $reponse['option_id'] == $correctOption->id);
                $points = $isCorrect ? $question->points : 0;
                break;

            case 'texte_court':
                // Comparaison avec mots-clés
                $expected = strtolower($question->config['expected_answer'] ?? '');
                $userAnswer = strtolower($reponse['text'] ?? '');
                $isCorrect = ($expected == $userAnswer);
                $points = $isCorrect ? $question->points : 0;
                break;

            default:
                $points = null; // Correction manuelle
                break;
        }

        return [
            'is_correct' => $isCorrect,
            'points_obtenus' => $points,
            'points_max' => $question->points
        ];
    }

public function allSubmissions(string $evaluationId): JsonResponse
{
    try {
        // Trouver l'évaluation par son slug
        $evaluation = Evaluation::where('slug', $evaluationId)->firstOrFail();
        
        // 🔴 CORRECTION - Utiliser etudiantGroups pour récupérer les étudiants du groupe
        $etudiants = Etudiant::whereHas('etudiantGroups', function($q) use ($evaluation) {
            $q->where('group_id', $evaluation->group_id)
              ->where('annee_scolaire_id', injectAnneeScolaireId()); // Filtrer par année active
        })->get();
        
        // Alternative si vous voulez charger les relations
        $etudiants = Etudiant::with(['etudiantGroups' => function($q) use ($evaluation) {
            $q->where('group_id', $evaluation->group_id)
              ->where('annee_scolaire_id', injectAnneeScolaireId());
        }])->whereHas('etudiantGroups', function($q) use ($evaluation) {
            $q->where('group_id', $evaluation->group_id)
              ->where('annee_scolaire_id', injectAnneeScolaireId());
        })->get();
        
        // Récupérer toutes les questions avec leurs parties
        $questions = ExamQuestion::whereHas('part', function($q) use ($evaluation) {
            $q->where('evaluation_id', $evaluation->id);
        })->with(['part', 'options'])->orderBy('order')->get();
        
        // Récupérer toutes les soumissions
        $submissions = ExamSubmission::where('evaluation_id', $evaluation->id)
                                    ->get()
                                    ->groupBy('etudiant_id');
        
        $resultats = [];
        
        foreach ($etudiants as $etudiant) {
            $etudiantSubmissions = $submissions[$etudiant->id] ?? collect();
            
            $totalPoints = 0;
            $questionsRepondues = 0;
            $questionsCorrigees = 0;
            
            foreach ($questions as $question) {
                $submission = $etudiantSubmissions->firstWhere('question_id', $question->id);
                if ($submission) {
                    $questionsRepondues++;
                    if ($submission->points_obtenus !== null) {
                        $questionsCorrigees++;
                        $totalPoints += $submission->points_obtenus;
                    }
                }
            }
            
            $statut = 'Non corrigé';
            if ($questionsCorrigees > 0) {
                $statut = $questionsCorrigees === $questionsRepondues ? 'Corrigé' : 'Partiellement corrigé';
            }
            
            // Récupérer le groupe actuel de l'étudiant (pour information)
            $groupeActuel = $etudiant->etudiantGroups()
                ->where('annee_scolaire_id', injectAnneeScolaireId())
                ->first();
            
            $resultats[] = [
                'id' => $etudiant->id,
                'nom' => $etudiant->nom,
                'prenom' => $etudiant->prenom,
                'email' => $etudiant->email,
                'matricule' => $etudiant->matricule,
                'groupe_actuel' => $groupeActuel ? $groupeActuel->group->nom : null,
                'progression' => [
                    'total' => $questions->count(),
                    'repondues' => $questionsRepondues,
                    'pourcentage' => $questions->count() > 0 ? round(($questionsRepondues / $questions->count()) * 100) : 0
                ],
                'note' => $totalPoints,
                'noteMax' => $questions->sum('points'),
                'statutCorrection' => $statut,
                'derniereActivite' => $etudiantSubmissions->max('submitted_at') ?? $etudiantSubmissions->max('auto_saved_at'),
            ];
        }
        
        // Calculer les statistiques
        $totalEtudiants = count($resultats);
        $etudiantsAvecReponses = collect($resultats)->filter(fn($e) => $e['progression']['repondues'] > 0)->count();
        $sommeNotes = collect($resultats)->sum('note');
        
        $stats = [
            'total_etudiants' => $totalEtudiants,
            'participation' => $totalEtudiants > 0 ? round(($etudiantsAvecReponses / $totalEtudiants) * 100) : 0,
            'moyenne' => $totalEtudiants > 0 ? round($sommeNotes / $totalEtudiants, 1) : 0,
            'a_corriger' => ExamSubmission::where('evaluation_id', $evaluation->id)
                                         ->whereNull('points_obtenus')
                                         ->count()
        ];
        
        return response()->json([
            'success' => true,
            'message' => 'Soumissions récupérées avec succès',
            'data' => [
                'etudiants' => $resultats,
                'questions' => $questions,
                'stats' => $stats,
                'total_points' => $questions->sum('points')
            ]
        ], 200);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la récupération',
            'error' => $e->getMessage()
        ], 500);
    }
}


 public function submitComplex(Request $request, string $evaluationId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'question_id' => 'required|exists:exam_questions,id',
            'etudiant_id' => 'required|exists:etudiants,id',
            'reponse' => 'required|array' // La réponse structurée
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

            // Trouver l'évaluation par son slug
            $evaluation = Evaluation::where('slug', $evaluationId)->firstOrFail();
            
            // Vérifier que la question existe
            $question = ExamQuestion::find($request->question_id);

            // Créer ou mettre à jour la soumission
            $submission = ExamSubmission::updateOrCreate(
                [
                    'evaluation_id' => $evaluation->id,
                    'etudiant_id' => $request->etudiant_id,
                    'question_id' => $request->question_id
                ],
                [
                    'reponse' => $request->reponse,
                    'submitted_at' => now(),
                    'auto_saved_at' => now(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Réponse soumise avec succès',
                'data' => $submission
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la soumission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

     public function details(ExamSubmission $examSubmission): JsonResponse
    {
        try {
            $examSubmission->load(['question', 'etudiant', 'evaluation']);
            
            // Charger les données supplémentaires selon le type
            if ($examSubmission->question->type === 'complex_data') {
                $examSubmission->question->load('complexData');
            } elseif ($examSubmission->question->type === 'structured_data') {
                $examSubmission->question->load('structuredData');
            } elseif ($examSubmission->question->type === 'multi_parts') {
                $examSubmission->question->load('multiParts');
            } elseif ($examSubmission->question->type === 'guided_writing') {
                $examSubmission->question->load('guidedWriting');
            }

            return response()->json([
                'success' => true,
                'message' => 'Détails récupérés',
                'data' => $examSubmission
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}