<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\CandidatureResource;
use App\Models\Candidature;
use App\Models\ConcoursNote;
use App\Models\ConcoursSession;
use App\Traits\ActionsTraits\AuthorizesConcoursManagementTrait;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ConcoursNoteController extends Controller
{
    use AuthorizesConcoursManagementTrait;

    /**
     * Grille de saisie : candidats ayant participé à l'épreuve pour cette session,
     * avec les matières applicables à leur (niveau, filière) et leurs notes existantes.
     */
    public function index(ConcoursSession $session)
    {
        $this->authorizeConcoursManagement();

        $candidatures = Candidature::query()
            ->where('concours_session_id', $session->id)
            ->where('participation', true)
            ->with(['niveau', 'filiere'])
            ->orderBy('nom')
            ->orderBy('prenom')
            ->get();

        $sessionMatieres = $session->matieres()->with('concoursMatiere')->get();

        $notes = ConcoursNote::whereIn('candidature_id', $candidatures->pluck('id'))->get();

        $data = $candidatures->map(function (Candidature $candidature) use ($sessionMatieres, $notes) {
            $matieres = $sessionMatieres->filter(function ($sm) use ($candidature) {
                return $sm->niveau_id === $candidature->niveau_id
                    && ($sm->filiere_id === null || $sm->filiere_id === $candidature->filiere_id);
            })->map(function ($sm) use ($notes, $candidature) {
                $note = $notes->first(fn ($n) => $n->candidature_id === $candidature->id && $n->concours_session_matiere_id === $sm->id);

                return [
                    'concours_session_matiere_id' => $sm->id,
                    'matiere' => $sm->concoursMatiere->nom,
                    'coefficient' => $sm->coefficient,
                    'note' => $note?->note,
                ];
            })->values();

            return [
                'candidature' => new CandidatureResource($candidature),
                'matieres' => $matieres,
                'moyenne' => $candidature->moyenneConcours(),
            ];
        });

        return response()->json(['data' => $data]);
    }

    /**
     * Enregistrement en masse des notes saisies pour une session.
     * Payload attendu : { notes: [{ candidature_id, concours_session_matiere_id, note }, ...] }
     */
    public function storeBulk(Request $request, ConcoursSession $session)
    {
        $this->authorizeConcoursManagement();

        $request->validate([
            'notes' => 'required|array|max:500',
            'notes.*.candidature_id' => [
                'required',
                Rule::exists('candidatures', 'id')->where('concours_session_id', $session->id),
            ],
            'notes.*.concours_session_matiere_id' => [
                'required',
                Rule::exists('concours_session_matieres', 'id')->where('concours_session_id', $session->id),
            ],
            'notes.*.note' => 'nullable|numeric|min:0|max:20',
        ]);

        foreach ($request->input('notes') as $entry) {
            ConcoursNote::updateOrCreate(
                [
                    'candidature_id' => $entry['candidature_id'],
                    'concours_session_matiere_id' => $entry['concours_session_matiere_id'],
                ],
                [
                    'note' => $entry['note'],
                    'saisi_par' => auth()->id(),
                ]
            );
        }

        return response()->json(['message' => 'Notes enregistrées avec succès']);
    }
}
