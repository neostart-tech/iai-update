<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ExamSession;
use App\Models\Evaluation;
use App\Models\ExamQuestion;
use App\Models\ExamSubmission;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ExamSessionController extends Controller
{
    /**
     * POST /api/exam/{evaluationId}/start
     */
    public function start(Request $request, string $evaluationId): JsonResponse
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
            $etudiantId = $request->etudiant_id;
            
            // Rechercher par slug
            $evaluation = Evaluation::where('slug', $evaluationId)->firstOrFail();

            // 🔴 UTILISER UNE TRANSACTION POUR ÉVITER LES DOUBLONS
            DB::beginTransaction();

            // 1️⃣ TERMINER TOUTES LES ANCIENNES SESSIONS (peu importe leur statut)
            $terminatedCount = ExamSession::where('evaluation_id', $evaluation->id)
                       ->where('etudiant_id', $etudiantId)
                       ->where('status', '!=', 'termine') // Terminer celles qui ne sont pas déjà terminées
                       ->update([
                           'status' => 'termine',
                           'submitted_at' => now()
                       ]);

            // 2️⃣ VÉRIFIER S'IL Y A DÉJÀ UNE SESSION (par sécurité)
            $existingSession = ExamSession::where('evaluation_id', $evaluation->id)
                                          ->where('etudiant_id', $etudiantId)
                                          ->where('status', 'en_cours')
                                          ->first();

            if ($existingSession) {
                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Session déjà existante',
                    'data' => [
                        'session' => $existingSession,
                        'is_new' => false,
                        'anciennes_sessions_terminees' => $terminatedCount
                    ]
                ], 200);
            }

            // 3️⃣ CRÉER UNE NOUVELLE SESSION
            $session = ExamSession::create([
                'evaluation_id' => $evaluation->id,
                'etudiant_id' => $etudiantId,
                'started_at' => now(),
                'last_activity_at' => now(),
                'status' => 'en_cours',
                'session_token' => Str::random(60),
                'progress' => [
                    'questions_repondues' => 0,
                    'total_questions' => $this->countTotalQuestions($evaluation->id)
                ]
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Session démarrée avec succès',
                'data' => [
                    'session' => $session,
                    'is_new' => true,
                    'anciennes_sessions_terminees' => $terminatedCount,
                    'temps_restant' => $this->calculateTimeRemaining($evaluation),
                    'token' => $session->session_token
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du démarrage',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/exam/{evaluationId}/progress
     */
    public function progress(Request $request, string $evaluationId): JsonResponse
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
            $etudiantId = $request->etudiant_id;
            
            // Trouver l'évaluation par slug
            $evaluation = Evaluation::where('slug', $evaluationId)->firstOrFail();

            // Récupérer la session la PLUS RÉCENTE
            $session = ExamSession::where('evaluation_id', $evaluation->id)
                                  ->where('etudiant_id', $etudiantId)
                                  ->orderBy('created_at', 'desc')
                                  ->first();

            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune session trouvée'
                ], 404);
            }

            $submissions = ExamSubmission::where('evaluation_id', $evaluation->id)
                                        ->where('etudiant_id', $etudiantId)
                                        ->get();

            $totalQuestions = $this->countTotalQuestions($evaluation->id);
            $repondues = $submissions->whereNotNull('submitted_at')->count();
            $enCours = $submissions->whereNull('submitted_at')->count();
            $nonVues = $totalQuestions - $repondues - $enCours;

            $progress = [
                'total_questions' => $totalQuestions,
                'repondues' => $repondues,
                'en_cours' => $enCours,
                'non_vues' => $nonVues,
                'pourcentage' => $totalQuestions > 0 ? round(($repondues / $totalQuestions) * 100) : 0,
                'temps_restant' => $this->calculateTimeRemaining($evaluation),
                'derniere_activite' => $session->last_activity_at,
                'session_id' => $session->id,
                'session_status' => $session->status
            ];

            return response()->json([
                'success' => true,
                'message' => 'Progression récupérée',
                'data' => $progress
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
     * GET /api/exam-sessions/{id}
     */
    public function show(ExamSession $examSession): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'Session récupérée',
                'data' => $examSession->load(['evaluation', 'etudiant'])
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
     * PUT /api/exam-sessions/{id}
     */
    public function update(Request $request, ExamSession $examSession): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|in:en_cours,termine,interrompu',
            'progress' => 'sometimes|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $examSession->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Session mise à jour',
                'data' => $examSession->fresh()
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
     * DELETE /api/exam-sessions/{id}
     */
    public function destroy(ExamSession $examSession): JsonResponse
    {
        try {
            $examSession->delete();

            return response()->json([
                'success' => true,
                'message' => 'Session supprimée'
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
     * POST /api/exam-sessions/{id}/ping
     */
    public function ping(ExamSession $examSession): JsonResponse
    {
        try {
            $examSession->update([
                'last_activity_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Activité mise à jour'
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
     * GET /api/evaluations/{evaluationId}/sessions
     */
    public function examSessions(string $evaluationId): JsonResponse
    {
        try {
            // Trouver l'évaluation par slug
            $evaluation = Evaluation::where('slug', $evaluationId)->firstOrFail();
            
            $sessions = ExamSession::where('evaluation_id', $evaluation->id)
                                   ->with('etudiant')
                                   ->orderBy('created_at', 'desc')
                                   ->get();

            $stats = [
                'total' => $sessions->count(),
                'en_cours' => $sessions->where('status', 'en_cours')->count(),
                'termines' => $sessions->where('status', 'termine')->count(),
                'interrompus' => $sessions->where('status', 'interrompu')->count()
            ];

            return response()->json([
                'success' => true,
                'message' => 'Sessions récupérées',
                'data' => [
                    'sessions' => $sessions,
                    'stats' => $stats,
                    'evaluation' => $evaluation
                ]
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
     * ENDPOINT POUR NETTOYER LES DOUBLONS (à appeler une seule fois)
     * POST /api/exam-sessions/clean-duplicates
     */
    public function cleanDuplicates(): JsonResponse
    {
        try {
            DB::beginTransaction();
            
            // Récupérer tous les groupes (evaluation_id, etudiant_id) qui ont plusieurs sessions
            $duplicates = DB::table('exam_sessions')
                ->select('evaluation_id', 'etudiant_id', DB::raw('COUNT(*) as count'))
                ->groupBy('evaluation_id', 'etudiant_id')
                ->having('count', '>', 1)
                ->get();

            $cleaned = 0;
            
            foreach ($duplicates as $dup) {
                // Garder la session la plus récente
                $latestSession = ExamSession::where('evaluation_id', $dup->evaluation_id)
                                           ->where('etudiant_id', $dup->etudiant_id)
                                           ->orderBy('created_at', 'desc')
                                           ->first();
                
                if ($latestSession) {
                    // Supprimer toutes les autres sessions
                    $deleted = ExamSession::where('evaluation_id', $dup->evaluation_id)
                                         ->where('etudiant_id', $dup->etudiant_id)
                                         ->where('id', '!=', $latestSession->id)
                                         ->delete();
                    
                    $cleaned += $deleted;
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Doublons nettoyés avec succès',
                'data' => [
                    'groupes_doublons' => $duplicates->count(),
                    'sessions_supprimees' => $cleaned
                ]
            ], 200);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du nettoyage',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Compte le nombre total de questions pour une évaluation
     */
    private function countTotalQuestions(int $evaluationId): int
    {
        return ExamQuestion::whereHas('part', function($q) use ($evaluationId) {
            $q->where('evaluation_id', $evaluationId);
        })->count();
    }

    /**
     * Calcule le temps restant pour une évaluation
     */
    private function calculateTimeRemaining(Evaluation $evaluation): array
    {
        $now = now();
        $fin = $evaluation->fin;
        
        if ($now > $fin) {
            return [
                'minutes' => 0,
                'secondes' => 0,
                'termine' => true
            ];
        }

        $diff = $now->diffInSeconds($fin);
        
        return [
            'minutes' => floor($diff / 60),
            'secondes' => $diff % 60,
            'termine' => false
        ];
    }
}