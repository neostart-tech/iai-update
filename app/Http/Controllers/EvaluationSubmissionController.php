<?php
namespace App\Http\Controllers;

use App\Models\Evaluation;
use App\Models\EvaluationSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EvaluationSubmissionController extends Controller
{
    public function show(Evaluation $evaluation)
    {
        $evaluation->load('questions.options');

        $submission = EvaluationSubmission::firstOrCreate([
            'evaluation_id' => $evaluation->id,
            'etudiant_id' => Auth::id(),
        ], [
            'started_at' => now(),
            'status' => 'in_progress'
        ]);

        // return view('etudiants.evaluations.show', compact('evaluation', 'submission'));
    }

    public function saveProgress(Request $request, Evaluation $evaluation)
    {
        $submission = EvaluationSubmission::firstOrCreate([
            'evaluation_id' => $evaluation->id,
            'etudiant_id' => Auth::id(),
        ]);

        foreach ($request->input('answers', []) as $questionId => $answerData) {
            $submission->answers()->updateOrCreate(
                ['question_id' => $questionId],
                [
                    'answer_text' => $answerData['text'] ?? null,
                    'answer_options' => $answerData['options'] ?? null
                ]
            );
        }

        return response()->json(['status' => 'ok', 'message' => 'Progression sauvegardée']);
    }

    public function submit(Request $request, Evaluation $evaluation)
    {
        $submission = EvaluationSubmission::firstOrCreate([
            'evaluation_id' => $evaluation->id,
            'etudiant_id' => Auth::id(),
        ]);

        // On sauvegarde une dernière fois
        $this->saveProgress($request, $evaluation);

        $submission->update([
            'submitted_at' => now(),
            'status' => 'submitted'
        ]);

        return redirect()->route('etudiants.dashboard')->with('success', 'Évaluation soumise !');
    }
}
