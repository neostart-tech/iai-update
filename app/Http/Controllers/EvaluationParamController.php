<?php

namespace App\Http\Controllers\Professeur;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Evaluation;
use App\Models\EvaluationQuestion;
use App\Models\EvaluationQuestionOption;
use Illuminate\Support\Facades\DB;

class EvaluationParamController extends Controller
{
    // Affiche la page de paramétrage d'une évaluation
    public function edit(Evaluation $evaluation)
    {
        // return view('professeurs.evaluations.param', compact('evaluation'));
    }

    // Met à jour les paramètres de l'évaluation
    public function update(Request $request, Evaluation $evaluation)
    {
        $validated = $request->validate([
            'is_online' => 'boolean',
            'duration_minutes' => 'nullable|integer',
            'security_level' => 'in:none,medium,strict',
            'autosave_enabled' => 'boolean',
            'disable_copy_paste' => 'boolean',
            'disable_right_click' => 'boolean',
            'disable_printscreen' => 'boolean',
            'forbid_tab_switch' => 'boolean',
            'max_focus_lost' => 'nullable|integer',
            'auto_submit_on_time_end' => 'boolean',
        ]);

        $evaluation->update($validated);

        return redirect()->back()->with('success', 'Paramètres mis à jour avec succès !');
    }

    // Création dynamique des questions
    public function addQuestion(Request $request, Evaluation $evaluation)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'statement' => 'required|string',
            'type' => 'required|in:text,textarea,choice_single,choice_multiple',
            'points' => 'nullable|numeric',
            'options' => 'array', // uniquement pour choice_single / choice_multiple
            'options.*.label' => 'required_with:options|string|max:255',
            'options.*.is_correct' => 'boolean',
        ]);

        DB::transaction(function () use ($validated, $evaluation) {
            $question = $evaluation->questions()->create([
                'title' => $validated['title'],
                'statement' => $validated['statement'],
                'type' => $validated['type'],
                // 'points' => $validated['points'] ?? null,
            ]);

            if (in_array($question->type, ['choice_single', 'choice_multiple']) && !empty($validated['options'])) {
                foreach ($validated['options'] as $opt) {
                    $question->options()->create([
                        'label' => $opt['label'],
                        'is_correct' => $opt['is_correct'] ?? false,
                    ]);
                }
            }
        });

        return response()->json(['message' => 'Question ajoutée avec succès !']);
    }
}
