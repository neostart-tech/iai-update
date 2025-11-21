<?php

namespace App\Http\Controllers;

use App\Enums\TypeEvaluationEnum;
use App\Http\Controllers\Admin\NoteController;
use App\Http\Requests\EvaluationRequest;
use App\Jobs\NotifyStudentsAboutEvaluation;
use App\Models\EmploiDuTemp;
use App\Models\Evaluation;
use App\Models\EvaluationAnswer;
use App\Models\EvaluationQuestion;
use App\Models\EvaluationQuestionOption;
use App\Models\EvaluationSubmission;
use App\Models\Group;
use App\Models\Salle;
use App\Models\User;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Throwable;

class EvaluationController extends Controller
{
    public function index(): View
    {
        return view('admin.evaluations.index')->with([
            'evaluations' => Evaluation::query()
                ->with([
                    'salle:id,nom',
                    'group:id,nom,filiere_id',
                    'group.filiere:id,code',
                    'matiere:id,nom,code',
                    'fiche.surveillants',
                ])
                ->orderByDesc('debut')
                ->get()
                ->map(function (Evaluation $evaluation) {
                    $evaluation->setAttribute('dateFormatted', $evaluation->getAttribute('debut')->translatedFormat('d F Y'));
                    $evaluation->setAttribute('debutFormatted', $evaluation->getAttribute('debut')->translatedFormat('H:i'));
                    $evaluation->setAttribute('finFormatted', $evaluation->getAttribute('fin')->translatedFormat('H:i'));

                    return $evaluation;
                }),
            'enseignants' => User::all(),
        ]);
    }

    public function create(): View
    {
        return view('admin.evaluations.create')->with([
            'evaluation' => new Evaluation([
                'debut' => '12:00',
                'fin' => '14:00',
                'correction_end_date' => now()->addWeeks(2),
            ]),
            'groups' => Group::all(),
            'salles' => Salle::all(),
            'types' => TypeEvaluationEnum::cases(),
            'niveaux' => \App\Models\Niveau::all(),
        ]);
    }

    public function store(EvaluationRequest $request): RedirectResponse
    {
        // dd('Ceci est un test');
        $evaluation = Evaluation::create([
            ...$request->only([
                'type',
                'group_id',
                'unite_valeur_id',
                'salle_id',
                'niveau_id',
                'semestre',
                'date',
                'debut',
                'fin',
                'duration_minutes',
                'published',
                'correction_end_date',
            ]),
            ...injectAnneeScolaireId(),
        ]);

        if ($evaluation->getAttribute('published')) {
            NotifyStudentsAboutEvaluation::dispatch($evaluation);
        }

        return to_route('admin.evaluations.index')->with(successMsg('Évaluation enregistrée avec succès'));
    }

    public function show(Evaluation $evaluation): View
    {
        return view('admin.evaluations.show', [
            'evaluation' => $evaluation,
            'salles' => Salle::all(),
            'enseignants' => User::all(),
        ]);
    }

    public function edit(Evaluation $evaluation): View|RedirectResponse
    {
        if ($evaluation->getAttribute('published') or $evaluation->getAttribute('debut')->isBefore(now())) {
            warningMsg("L'évènement ne peut plus être modifier");

            return back();
        }

        return view('admin.evaluations.edit', compact('evaluation'))->with([
            'groups' => Group::all(),
            'salles' => Salle::all(),
            'types' => TypeEvaluationEnum::cases(),
            'niveaux' => \App\Models\Niveau::all(),
            'enseignants' => User::all(),
        ]);
    }

    public function update(EvaluationRequest $request, Evaluation $evaluation): RedirectResponse
    {
        $evaluation->setAllWaysUpdate(false);
        $evaluation->update([
            ...$request->only([
                'type',
                'group_id',
                'unite_valeur_id',
                'salle_id',
                'niveau_id',
                'semestre',
                'date',
                'debut',
                'fin',
                'duration_minutes',
                'published',
                'correction_end_date',
            ]),
        ]);

        if ($evaluation->getAttribute('published')) {
            NotifyStudentsAboutEvaluation::dispatch($evaluation);
        }

        successMsg('Évaluation mise à jour avec succès');

        return to_route('admin.evaluations.index');
    }

    public function publish(string $slug): Application|Response|ResponseFactory
    {
        $evaluation = Evaluation::query()->firstWhere('slug', $slug);

        if (! $evaluation) {
            return __404();
        }

        /**
         * @var Evaluation $evaluation
         */
        if ($evaluation->getAttribute('published')) {
            return __200();
        }

        try {
            $evaluation->update(['published' => true]);
            NotifyStudentsAboutEvaluation::dispatch($evaluation);
        } catch (Throwable $exception) {
            return __500($exception->getMessage());
        }

        return response([
            'message' => 'Annonce d\'évaluation publiée avec succès.',
        ]);
    }

    public function getNoteFiche(Evaluation $evaluation): View
    {
        return (new NoteController)->evaluationNotesIndex($evaluation);
    }

    // Gestion de

    public function editEvaluation(Evaluation $evaluation)
    {
        // Seulement les examens (pas les cours)
        if ($evaluation->type === 'Cours') {
            abort(403);
        }

        // return view('enseignants.evaluations.config', compact('evaluation'));
    }

    public function updateEvaluation(Request $request, Evaluation $evaluation)
    {
        $request->validate([
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

        $evaluation->update($request->all());

        return redirect()->back()->with('success', 'Paramètres mis à jour !');
    }

    public function getListEvaluationForStudent()
    {
        $groupIds = auth()->user()->group->pluck('id');
        $evaluations = Evaluation::where('group_id', $groupIds)
            ->with(['salle', 'group'])
            ->where('is_online', true)
            ->orderBy('date', 'asc')
            ->get();

        return view('etudiants.my-space.evaluations.index', compact('evaluations'));

    }

    // public function startEvaluationview($id)
    // {
    //     $evaluation = Evaluation::find($id);

    //     return view('etudiants.my-space.evaluations.start-evaluation', compact('evaluation'));
    // }*
public function startEvaluationview($id)
{
    $etudiant = Auth::guard('etudiants')->user();
    $evaluation = Evaluation::with('questions.options')->find($id);

    $submission = EvaluationSubmission::where('evaluation_id', $id)
                    ->where('etudiant_id', $etudiant->id)
                    ->first();

    $isSubmitted = $submission && $submission->submitted_at !== null;

    // Remplir les réponses existantes pour chaque question
    foreach ($evaluation->questions as $question) {
        $answer = $submission ? $submission->answers()->where('question_id', $question->id)->first() : null;

        $question->user_answer = $answer ? $answer->answer_text : null;
        $question->user_answer_options = $answer ? $answer->answer_options : null;
    }

    return view('etudiants.my-space.evaluations.start-evaluation', compact('evaluation', 'isSubmitted'));
}


    // public function showQuestions($id)
    // {
    //     $evaluation = Evaluation::find($id);
    //     $evaluation->load(['questions.options']);

    //     return response()->json($evaluation);
    // }
public function showQuestions($id)
{
    $etudiant = auth()->user();

	// dd($etudiant);
    $evaluation = Evaluation::findOrFail($id);

    // Charger questions + options
    $evaluation->load(['questions.options']);

    // Charger la submission + answers
    $submission = EvaluationSubmission::where('evaluation_id', $evaluation->id)
                ->where('etudiant_id', $etudiant)
                ->with('answers')
                ->first();


    // Injecter les réponses dans chaque question
    foreach ($evaluation->questions as $question) {

        if ($submission) {
            $answer = $submission->answers
                     ->where('question_id', $question->id)
                     ->first();

            if ($answer) {
                // Selon type
                if ($question->type === 'text' || $question->type === 'textarea') {
                    $question->user_answer = $answer->answer_text;
                } 
                elseif ($question->type === 'choice_single') {
                    $question->user_answer = $answer->answer_options[0] ?? null;
                }
                elseif ($question->type === 'choice_multiple') {
                    $question->user_answer = $answer->answer_options ?? [];
                }
            } else {
                $question->user_answer = null;
            }
        } else {
            $question->user_answer = null;
        }
    }

    // Ajouter submission dans l'objet
    $evaluation->submission = $submission;

    return response()->json($evaluation);
}



   public function submitQuestion(Request $request, $id)
{
    $etudiant = Auth::guard('etudiants')->user();
    $evaluation = Evaluation::find($id);

    if (! $etudiant) {
        abort(403, 'Étudiant non connecté');
    }

    // 🔒 BLOCAGE SI TEMPS ÉCOULÉ
    if (now()->greaterThan($evaluation->fin)) {
        return redirect()->back()->withErrors([
            'error' => 'Le temps est écoulé, vous ne pouvez plus soumettre l\'évaluation.'
        ]);
    }

    // Récupérer ou créer la soumission
    $submission = EvaluationSubmission::firstOrCreate(
        [
            'evaluation_id' => $evaluation->id,
            'etudiant_id' => $etudiant->id,
        ],
        [
            'started_at' => now(),
        ]
    );

    // Marquer comme soumis
    $submission->update([
        'submitted_at' => now(),
        'status' => 'submitted',
    ]);

    // Sauvegarder les réponses
    foreach ($evaluation->questions as $question) {

        $field = 'question_'.$question->id;

        if (! $request->has($field)) {
            continue;
        }

        $value = $request->input($field);

        // Choix multiples
        if ($question->type == 'choice_multiple') {
            EvaluationAnswer::create([
                'submission_id' => $submission->id,
                'question_id' => $question->id,
                'answer_options' => $value,
            ]);
        }
        // Choix unique
        elseif ($question->type == 'choice_single') {
            EvaluationAnswer::create([
                'submission_id' => $submission->id,
                'question_id' => $question->id,
                'answer_options' => [$value],
            ]);
        }
        // Question texte
        else {
            EvaluationAnswer::create([
                'submission_id' => $submission->id,
                'question_id' => $question->id,
                'answer_text' => $value,
            ]);
        }
    }

    return redirect()->back()->with('succes', 'Votre évaluation a bien été envoyée.');
}



// public function autosave(Request $request, $id)
// {
//     $etudiant = Auth::guard('etudiants')->user();
//     $evaluation = Evaluation::find($id);

//     if (! $etudiant) {
//         return response()->json(['status' => 'error', 'message' => 'Non authentifié'], 401);
//     }

//     // 🔒 BLOCAGE SI TEMPS ÉCOULÉ
//     if (now()->greaterThan($evaluation->fin)) {
//         return response()->json([
//             'status' => 'error',
//             'message' => 'Temps écoulé — sauvegarde automatique désactivée.'
//         ], 403);
//     }

//     // Récupérer ou créer la soumission (brouillon)
//     $submission = EvaluationSubmission::firstOrCreate(
//         [
//             'evaluation_id' => $evaluation->id,
//             'etudiant_id' => $etudiant->id,
//         ],
//         [
//             'started_at' => now(),
//             'status' => 'in_progress',
//         ]
//     );

//     // Sauvegarder chaque réponse
//     foreach ($evaluation->questions as $question) {

//         $field = 'question_'.$question->id;
//         if (! $request->has($field)) continue;

//         $value = $request->input($field);

//         // Récupère ou crée la réponse
//         $answer = EvaluationAnswer::updateOrCreate(
//             [
//                 'submission_id' => $submission->id,
//                 'question_id' => $question->id,
//             ],
//             []
//         );

//         // Type texte
//         if ($question->type == 'text' || $question->type == 'textarea') {
//             $answer->answer_text = $value;
//         }
//         // Choix multiple
//         elseif ($question->type == 'choice_multiple') {
//             $answer->answer_options = $value;
//         }
//         // Choix unique
//         else {
//             $answer->answer_options = [$value];
//         }

//         $answer->save();
//     }

//     return response()->json(['status' => 'saved']);
// }
public function autosave(Request $request, $id)
{
    $etudiant = Auth::guard('etudiants')->user();
    $evaluation = Evaluation::find($id);

    if (! $etudiant) {
        return response()->json(['status' => 'error', 'message' => 'Non authentifié'], 401);
    }

    // 🔒 BLOCAGE SI TEMPS ÉCOULÉ
    if (now()->greaterThan($evaluation->fin)) {
        return response()->json([
            'status' => 'error',
            'message' => 'Temps écoulé — sauvegarde automatique désactivée.'
        ], 403);
    }

    // Récupérer ou créer la soumission (brouillon)
    $submission = EvaluationSubmission::firstOrCreate(
        [
            'evaluation_id' => $evaluation->id,
            'etudiant_id' => $etudiant->id,
        ],
        [
            'started_at' => now(),
            'status' => 'in_progress',
        ]
    );

    foreach ($evaluation->questions as $question) {

        $field = 'question_'.$question->id;

        if (! $request->has($field)) continue;

        $value = $request->input($field);

        // Récupérer la réponse existante
        $answer = EvaluationAnswer::where('submission_id', $submission->id)
                    ->where('question_id', $question->id)
                    ->first();

        // ===========================
        // CHOIX MULTIPLE VIDES → SUPPRIMER
        // ===========================
        if ($question->type == 'choice_multiple' && is_array($value) && count($value) === 0) {
            if ($answer) $answer->delete();
            continue;
        }

        // ===========================
        // AUTRES CHAMPS VIDES → SUPPRIMER
        // ===========================
        if (($question->type != 'choice_multiple') && ($value === null || $value === '')) {
            if ($answer) $answer->delete();
            continue;
        }

        // ===========================
        // CRÉER OU METTRE À JOUR
        // ===========================
        if (!$answer) {
            $answer = new EvaluationAnswer();
            $answer->submission_id = $submission->id;
            $answer->question_id = $question->id;
        }

        if ($question->type == 'text' || $question->type == 'textarea') {
            $answer->answer_text = $value;
            $answer->answer_options = null;
        } elseif ($question->type == 'choice_multiple') {
            $answer->answer_options = $value;
            $answer->answer_text = null;
        } else { // choice_single
            $answer->answer_options = [$value];
            $answer->answer_text = null;
        }

        $answer->save();
    }

    return response()->json(['status' => 'saved']);
}



    public function submissions(Evaluation $evaluation)
    {
        $evaluation->load(['submissions.etudiant', 'questions.options']);
        // return view('enseignants.evaluations.submissions', compact('evaluation'));
    }

    public function createQuestionEvaluation($id)
    {

        $emploiDuTemp = EmploiDuTemp::findOrFail($id);

        return view('professeurs.evaluations.evaluations-question-form', compact('emploiDuTemp'));
    }

    public function StoreEvaluationQuestion(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'questions' => 'required|array',
            'questions.*.title' => 'required|string|max:255',
            'questions.*.statement' => 'required|string',
            'questions.*.type' => 'required|in:text,textarea,choice_single,choice_multiple',
            'questions.*.options_text' => 'nullable|array', // options_text peuvent être null si non applicable
            'questions.*.options_text.*.label' => 'required|string|max:255', // Options pour les choix
            'questions.*.points' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        }

        try {
            $emploiDuTemp = EmploiDuTemp::findOrFail($id);

            \DB::beginTransaction();
            $evaluations = Evaluation::firstOrCreate([
                'type' => 'Examen',
                'group_id' => $emploiDuTemp->group_id,
                'unite_valeur_id' => $emploiDuTemp->uv_id,
                'salle_id' => $emploiDuTemp->salle_id,
                'niveau_id' => $emploiDuTemp->niveau_id ?? null,
                'semestre' => $emploiDuTemp->semestre ?? null,
                'date' => $emploiDuTemp->debut ? $emploiDuTemp->debut->toDateString() : now()->toDateString(),
                'debut' => $emploiDuTemp->debut ? $emploiDuTemp->debut->toTimeString() : '12:00:00',
                'fin' => $emploiDuTemp->fin ? $emploiDuTemp->fin->toTimeString() : '14:00:00',
                'duration_minutes' => $emploiDuTemp->debut && $emploiDuTemp->fin ? $emploiDuTemp->debut->diffInMinutes($emploiDuTemp->fin) : 120,
                'published' => true,
                'slug' => \Str::uuid(),
                'correction_end_date' => now()->addWeeks(2),
                'annee_scolaire_id' => $emploiDuTemp->annee_scolaire_id,
            ]);
            // Enregistrer les questions
            foreach ($request->questions as $questionData) {
                $question = new EvaluationQuestion;
                $question->evaluation_id = $evaluations->id;
                $question->title = $questionData['title'];
                $question->statement = $questionData['statement'];
                $question->type = $questionData['type'];
                $question->points = $questionData['points'] ?? 0;
                $question->save();

                // Si c'est une question avec des options (choix)
                if (in_array($questionData['type'], ['choice_single', 'choice_multiple'])) {
                    foreach ($questionData['options_text'] as $optionData) {
                        $option = new EvaluationQuestionOption;
                        $option->question_id = $question->id;
                        $option->label = $optionData['label'];
                        $option->is_correct = false;  // Si nécessaire, gérer la logique de réponse correcte
                        $option->save();
                    }
                }
            }

            \DB::commit();

            return response()->json(['status' => 'success', 'message' => 'Questions enregistrées avec succès.']);
        } catch (\Exception $e) {
            \DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue, veuillez réessayer.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
