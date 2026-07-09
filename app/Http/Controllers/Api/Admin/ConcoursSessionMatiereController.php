<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConcoursSession;
use App\Models\ConcoursSessionMatiere;
use App\Traits\ActionsTraits\AuthorizesConcoursManagementTrait;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ConcoursSessionMatiereController extends Controller
{
    use AuthorizesConcoursManagementTrait;

    private function assertBelongsToSession(ConcoursSession $session, ConcoursSessionMatiere $sessionMatiere): void
    {
        if ($sessionMatiere->concours_session_id !== $session->id) {
            throw new HttpException(404, 'Cette matière ne fait pas partie de la session indiquée.');
        }
    }

    /**
     * Empêche : (a) le doublon exact d'une même configuration (même matière/niveau/filière),
     * et (b) le conflit entre une configuration "toutes les filières" (filiere_id = null) et
     * une configuration filière-spécifique pour la même matière/niveau — un tel conflit
     * ferait compter deux fois la même matière dans le calcul de la moyenne du candidat.
     */
    private function assertPasDeConflit(ConcoursSession $session, int $matiereId, int $niveauId, ?int $filiereId, ?int $excludeId = null): void
    {
        $query = ConcoursSessionMatiere::where('concours_session_id', $session->id)
            ->where('concours_matiere_id', $matiereId)
            ->where('niveau_id', $niveauId);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        if ($filiereId === null) {
            // "Toutes les filières" entre en conflit avec n'importe quelle configuration déjà
            // existante pour cette matière/niveau (globale ou filière spécifique).
            $conflit = $query->exists();
        } else {
            $conflit = (clone $query)->where(function ($q) use ($filiereId) {
                $q->whereNull('filiere_id')->orWhere('filiere_id', $filiereId);
            })->exists();
        }

        if ($conflit) {
            throw new HttpException(422, 'Cette matière est déjà configurée pour ce niveau (globalement ou pour cette filière) dans cette session.');
        }
    }

    public function index(ConcoursSession $session)
    {
        $this->authorizeConcoursManagement();

        return $session->matieres()->with(['concoursMatiere', 'niveau', 'filiere'])->get();
    }

    public function store(Request $request, ConcoursSession $session)
    {
        $this->authorizeConcoursManagement();

        $request->validate([
            'concours_matiere_id' => 'required|exists:concours_matieres,id',
            'niveau_id' => 'required|exists:niveaux,id',
            'filiere_id' => 'nullable|exists:filieres,id',
            'coefficient' => 'required|numeric|min:0.1|max:100',
        ]);

        $this->assertPasDeConflit(
            $session,
            (int) $request->input('concours_matiere_id'),
            (int) $request->input('niveau_id'),
            $request->input('filiere_id') !== null ? (int) $request->input('filiere_id') : null,
        );

        $sessionMatiere = ConcoursSessionMatiere::create([
            'concours_session_id' => $session->id,
            'concours_matiere_id' => $request->input('concours_matiere_id'),
            'niveau_id' => $request->input('niveau_id'),
            'filiere_id' => $request->input('filiere_id'),
            'coefficient' => $request->input('coefficient'),
        ]);

        return response()->json($sessionMatiere->load(['concoursMatiere', 'niveau', 'filiere']), 201);
    }

    public function update(Request $request, ConcoursSession $session, ConcoursSessionMatiere $sessionMatiere)
    {
        $this->authorizeConcoursManagement();
        $this->assertBelongsToSession($session, $sessionMatiere);

        $request->validate([
            'concours_matiere_id' => 'required|exists:concours_matieres,id',
            'niveau_id' => 'required|exists:niveaux,id',
            'filiere_id' => 'nullable|exists:filieres,id',
            'coefficient' => 'required|numeric|min:0.1|max:100',
        ]);

        $this->assertPasDeConflit(
            $session,
            (int) $request->input('concours_matiere_id'),
            (int) $request->input('niveau_id'),
            $request->input('filiere_id') !== null ? (int) $request->input('filiere_id') : null,
            $sessionMatiere->id,
        );

        $sessionMatiere->update($request->only(['concours_matiere_id', 'niveau_id', 'filiere_id', 'coefficient']));

        return response()->json($sessionMatiere->load(['concoursMatiere', 'niveau', 'filiere']));
    }

    public function destroy(ConcoursSession $session, ConcoursSessionMatiere $sessionMatiere)
    {
        $this->authorizeConcoursManagement();
        $this->assertBelongsToSession($session, $sessionMatiere);

        $sessionMatiere->delete();

        return response()->json(['message' => 'Matière retirée de la session avec succès']);
    }
}
