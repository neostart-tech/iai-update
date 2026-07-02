<?php

namespace App\Http\Controllers;

use App\Enums\TypeEvaluationEnum;
use App\Http\Controllers\Admin\NoteController;
use App\Http\Requests\EvaluationRequest;
use App\Http\Resources\EvaluationResource;
use App\Http\Resources\EvaluationStudentResource;
use App\Http\Resources\UserResource;
use App\Jobs\NotifyStudentsAboutEvaluation;
use App\Models\AnneeScolaire;
use App\Models\EmploiDuTemp;
use App\Models\Etudiant;
use App\Models\EtudiantGroup;
use App\Models\Evaluation;
use App\Models\EvaluationAnswer;
use App\Models\EvaluationCaseStudyContext;
use App\Models\EvaluationQuestion;
use App\Models\EvaluationQuestionOption;
use App\Models\EvaluationSubmission;
use App\Models\Group;
use App\Models\Part;
use App\Models\Salle;
use App\Models\User;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;
use App\Services\GeminiService;
use Throwable;

class EvaluationController extends Controller
{
    public function index()
    {
        // dd( Evaluation::query()
        //         ->with([
        //             'salle',
        //             'group',
        //             'group.niveau',
        //             'matiere:id,nom,code',
        //             'fiche.surveillants',
        //         ])
        //         ->orderByDesc('debut')
        //         ->get()
        //         ->map(function (Evaluation $evaluation) {
        //             $evaluation->setAttribute('dateFormatted', $evaluation->getAttribute('debut')->translatedFormat('d F Y'));
        //             $evaluation->setAttribute('debutFormatted', $evaluation->getAttribute('debut')->translatedFormat('H:i'));
        //             $evaluation->setAttribute('finFormatted', $evaluation->getAttribute('fin')->translatedFormat('H:i'));

        //             return $evaluation;
        //         }));
        // return view('admin.evaluations.index')->with([
        //     'evaluations' => Evaluation::query()
        //         ->with([
        //             'salle:id,nom',
        //             'group:id,nom',
        //             'group.niveau',
        //             'matiere:id,nom,code',
        //             'fiche.surveillants',
        //         ])
        //         ->orderByDesc('debut')
        //         ->get()
        //         ->map(function (Evaluation $evaluation) {
        //             $evaluation->setAttribute('dateFormatted', $evaluation->getAttribute('debut')->translatedFormat('d F Y'));
        //             $evaluation->setAttribute('debutFormatted', $evaluation->getAttribute('debut')->translatedFormat('H:i'));
        //             $evaluation->setAttribute('finFormatted', $evaluation->getAttribute('fin')->translatedFormat('H:i'));

        //             return $evaluation;
        //         }),
        //     'enseignants' => User::all(),
        // ]);
        $evaluations = Evaluation::query()
            ->with([
                'salle:id,nom',
                'group:id,nom',
                'group.niveau',
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
            });
        return EvaluationResource::collection($evaluations);
    }

    public function create()
    {
        //   $evaluations = Evaluation::query()
        //     ->with([
        //         'salle:id,nom',
        //         'group.filiere',
        //         'matiere',
        //         'fiche.surveillants',
        //     ])
        //     ->orderByDesc('debut')
        //     ->get();

        //     return EvaluationResource::collection($evaluations);
        return view('admin.evaluations.create')->with([
            'evaluation' => new Evaluation([
                'debut' => '12:00',
                'fin' => '14:00',
                'correction_end_date' => now()->addWeeks(2),

            ]),
            'groups' => Group::with('niveau')->get(),
            'salles' => Salle::all(),
            'types' => TypeEvaluationEnum::cases(),
            'niveaux' => \App\Models\Niveau::all(),
        ]);
    }

    public function store(EvaluationRequest $request)
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
                'is_online',
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

        return new EvaluationResource($evaluation);
        // return to_route('admin.evaluations.index')->with(successMsg('Évaluation enregistrée avec succès'));
    }

    public function show(Evaluation $evaluation)
    {
        return new EvaluationResource($evaluation->load([
            'salle:id,nom',
            'group:id,nom',
            'group.niveau',
            'matiere:id,nom,code',
            'fiche.surveillants',
        ]));
    }

    public function edit(Evaluation $evaluation)
    {
        if ($evaluation->getAttribute('published') or $evaluation->getAttribute('debut')->isBefore(now())) {
            // warningMsg("L'évènement ne peut plus être modifier");

            // return back();
            return __404("L'évaluation ne peut plus être modifiée.");
        }

        return new EvaluationResource($evaluation);

        // return view('admin.evaluations.edit', compact('evaluation'))->with([
        //     'groups' => Group::all(),
        //     'salles' => Salle::all(),
        //     'types' => TypeEvaluationEnum::cases(),
        //     'niveaux' => \App\Models\Niveau::all(),
        //     'enseignants' => User::all(),
        // ]);
    }

    public function update(EvaluationRequest $request, Evaluation $evaluation)
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
                'is_online',
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

        // successMsg('Évaluation mise à jour avec succès');

        // return to_route('admin.evaluations.index');

        return new EvaluationResource($evaluation);
    }

    public function destroy(Evaluation $evaluation)
    {
        if ($evaluation->getAttribute('published') or $evaluation->getAttribute('debut')->isBefore(now())) {
            return __404("Impossible de supprimer cette évaluation.Soit elle a déja été publiée soit la date de debut est supérieure a aujourd'hui");
        }

        $evaluation->delete();

        return new EvaluationResource($evaluation);
        // return to_route('admin.evaluations.index')->with(successMsg('Évaluation supprimée avec succès.'));
    }

    public function publish(string $slug)
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

        // return new EvaluationResource($evaluation);


        return response([
            'message' => 'Annonce d\'évaluation publiée avec succès.',
        ]);
    }

    public function getNoteFiche(Evaluation $evaluation)
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

    public function getListEvaluationForStudent()
    {
        /** @var \App\Models\Etudiant $user */
        $user = auth()->user();

        // Sécurité : Vérifier si l'utilisateur est bien un étudiant et s'il est autorisé à composer
        if (!$user || !($user instanceof \App\Models\Etudiant)) {
            return response()->json(['message' => 'Accès non autorisé'], 401);
        }

        if (!$user->peutComposer()) {
            return response()->json([
                'message' => 'Votre accès aux examens est restreint. Veuillez contacter l\'administration.',
                'blocked' => true,
                'data' => []
            ], 403);
        }

        $active = AnneeScolaire::where('active', true)->first()->getAttribute('id');

        $activeGroup = EtudiantGroup::where('etudiant_id', $user->id)
            ->where('annee_scolaire_id', $active)
            ->first();

        if (!$activeGroup) {
            return EvaluationResource::collection([]);
        }

        $groupIds = $activeGroup->getAttribute('group_id');
        $evaluations = Evaluation::where('group_id', $groupIds)
            ->with(['salle', 'group'])
            ->where('is_online', true)
            ->where('published', true)
            ->orderBy('date', 'asc')
            ->get();

        return EvaluationResource::collection($evaluations);
    }


    public function getListEvaluationForTeacher()
    {

        $evaluations = Evaluation::with(['salle', 'group', 'group.niveau'])
            ->where('is_online', true)
            ->orderBy('date', 'asc')
            ->get();

        return EvaluationResource::collection($evaluations);
    }

    public function getMyEvaluations()
    {
        $user = auth()->user();

        // Get evaluations where the connected teacher is assigned to the subject (uniteValeur)
        $evaluations = Evaluation::with(['salle', 'group', 'group.niveau', 'matiere'])
            ->whereHas('matiere.user', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })
            ->where('is_online', true)
            ->orderBy('date', 'asc')
            ->get();

        return EvaluationResource::collection($evaluations);
    }

    /**
     * Suggérer des questions via l'IA
     */
    public function aiSuggestQuestions(Request $request, GeminiService $geminiService)
    {
        $request->validate([
            'topic' => 'required|string',
            'part_id' => 'required'
        ]);

        try {
            $result = $geminiService->generateQuestions(
                $request->topic, 
                4, // On propose 4 questions pour commencer
                'intermédiaire'
            );

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Raffiner une question via l'IA
     */
    public function aiRefineQuestion(Request $request, GeminiService $geminiService)
    {
        $request->validate([
            'content' => 'required|string',
            'type' => 'required|string'
        ]);

        try {
            $result = $geminiService->getQuestionAssistance(
                $request->content,
                $request->type,
                $request->options ?? null
            );

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'optimisation : ' . $e->getMessage()
            ], 500);
        }
    }
}
