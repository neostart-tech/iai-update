<?php

namespace App\Http\Controllers\Enseignant;

use App\Http\Controllers\Controller;
use App\Models\Evaluation;
use App\Models\Etudiant;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class NoteEntryController extends Controller
{
    private function ensureAuthorized(Evaluation $evaluation): void
    {
        $user = Auth::guard('enseignants')->user();
        abort_if(!$user, 403);
        $evaluation->loadMissing('uniteValeur.enseignants');
        $isAssigned = $evaluation->uniteValeur->enseignants->contains('id', $user->id);
        abort_unless($isAssigned, 403, "Vous n'êtes pas autorisé à saisir les notes pour cette matière.");
    }

    public function index(Evaluation $evaluation): View
    {
        $this->ensureAuthorized($evaluation);

        $notes = $evaluation->notes()->with(['etudiant:id,slug,matricule,nom,prenom'])->get();
        $correctionEnable = !$evaluation->getAttribute('correction_submission_date') &&
            (is_null($evaluation->getAttribute('correction_end_date')) || today()->isBefore($evaluation->getAttribute('correction_end_date')));

        return view('enseignants.notes.index', compact('evaluation', 'notes', 'correctionEnable'));
    }

    public function store(Request $request, Evaluation $evaluation): RedirectResponse
    {
        $this->ensureAuthorized($evaluation);

        $etudiantsSlugs = $request->collect('etudiants');
        $etudiants = Etudiant::query()->whereIn('slug', $etudiantsSlugs)->get();
        $notes = $request->collect('notes');

        $etudiants->each(function (Etudiant $etudiant, $key) use ($evaluation, $notes) {
            $note = $evaluation->notes()->firstWhere('etudiant_id', $etudiant->getAttribute('id'));
            $raw = $notes->get($key);
            if (!$note) return;
            if (is_string($raw) && strtoupper(trim($raw)) === 'R') {
                $note->update(['notation' => 'R', 'note' => null]);
                return;
            }
            $value = is_numeric($raw) ? (float) $raw : null;
            if ($value !== null && $value >= 0 && $value <= 20) {
                $note->update(['notation' => null, 'note' => $value]);
            }
        });

        successMsg('Notes enregistrées avec succès');
        return back();
    }
}
