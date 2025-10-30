<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnneeScolaire;
use Illuminate\Http\Request;

class AnneeScolaireController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Liste toutes les années scolaires
        $annees = AnneeScolaire::all();
        return view('admin.anneescolaire.index', compact('annees'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Affiche le formulaire de création
        return view('admin.anneescolaire.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validation
        $request->validate([
            'libelle' => 'required|string|unique:annee_scolaires,libelle',
        ]);

        // Crée la nouvelle année scolaire et l'active
        $annee = new AnneeScolaire();
        $annee->libelle = $request->libelle;
        $annee->active = true;
        $annee->save();

        // Désactive toutes les autres années scolaires actives sauf la nouvelle
        AnneeScolaire::where('id', '!=', $annee->id)
            ->where('active', true)
            ->update(['active' => false]);

        return redirect()->route('anneescolaires.index')->with('success', 'Année scolaire créée et activée.');
    }

    /**
     * Display the specified resource.
     */
    public function show(AnneeScolaire $anneeScolaire)
    {
        // Affiche les détails d'une année scolaire
        return view('admin.anneescolaire.show', compact('anneeScolaire'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AnneeScolaire $anneeScolaire)
    {
        // Affiche le formulaire d'édition
        return view('admin.anneescolaire.edit', compact('anneeScolaire'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AnneeScolaire $anneeScolaire)
    {
        // Validation
        $request->validate([
            'libelle' => 'required|string|unique:annee_scolaires,libelle,' . $anneeScolaire->id,
            'active' => 'sometimes|boolean',
        ]);

        $anneeScolaire->libelle = $request->libelle;

        // Si on veut activer cette année scolaire
        if ($request->has('active') && $request->active) {
            // Désactive toutes les autres
            AnneeScolaire::where('id', '!=', $anneeScolaire->id)->update(['active' => false]);
            $anneeScolaire->active = true;
        } else {
            $anneeScolaire->active = false;
        }

        $anneeScolaire->save();

        return redirect()->route('anneescolaires.index')->with('success', 'Année scolaire mise à jour.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AnneeScolaire $anneeScolaire)
    {
        $anneeScolaire->delete();
        return redirect()->route('anneescolaires.index')->with('success', 'Année scolaire supprimée.');
    }
}
