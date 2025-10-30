<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Etudiant;
use App\Models\Gratification;
use App\Models\UniteEnseignement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GratificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $gratifications = Gratification::with(['etudiant', 'uniteEnseignement', 'approuvateurUser'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.gratifications.index', compact('gratifications'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $etudiants = Etudiant::orderBy('nom')->get();
        $unites_enseignement = UniteEnseignement::orderBy('nom')->get();

        return view('admin.gratifications.create', compact('etudiants', 'unites_enseignement'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'etudiant_id' => 'required|exists:etudiants,id',
            'unite_enseignement_id' => 'required|exists:unite_enseignements,id',
            'motif' => 'required|string|max:1000',
            'type' => 'required|in:excellence,participation,engagement,amelioration,autre',
            'observation' => 'nullable|string|max:1000',
        ]);

        Gratification::create([
            ...$request->only(['etudiant_id', 'unite_enseignement_id', 'motif', 'type', 'observation']),
            'validee' => true, // Approuvée automatiquement par l'admin
            'approuvee_par' => auth()->id(),
            'date_approbation' => now(),
            'annee_scolaire_id' => injectAnneeScolaireId()['annee_scolaire_id'],
        ]);

        return redirect()
            ->route('admin.gratifications.index')
            ->with('success', 'Gratification créée avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Gratification $gratification): View
    {
        $gratification->load(['etudiant', 'uniteEnseignement', 'approuvateurUser']);
        return view('admin.gratifications.show', compact('gratification'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Gratification $gratification): View
    {
        $etudiants = Etudiant::orderBy('nom')->get();
        $unites_enseignement = UniteEnseignement::orderBy('nom')->get();

        return view('admin.gratifications.edit', compact('gratification', 'etudiants', 'unites_enseignement'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Gratification $gratification): RedirectResponse
    {
        $request->validate([
            'motif' => 'required|string|max:1000',
            'type' => 'required|in:excellence,participation,engagement,amelioration,autre',
            'observation' => 'nullable|string|max:1000',
            'validee' => 'boolean'
        ]);

        $gratification->update([
            ...$request->only(['motif', 'type', 'observation', 'validee']),
            'approuvee_par' => $request->validee ? auth()->id() : null,
            'date_approbation' => $request->validee ? now() : null,
        ]);

        return redirect()
            ->route('admin.gratifications.index')
            ->with('success', 'Gratification mise à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Gratification $gratification): RedirectResponse
    {
        $gratification->delete();

        return redirect()
            ->route('admin.gratifications.index')
            ->with('success', 'Gratification supprimée avec succès.');
    }
}
