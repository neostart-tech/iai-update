<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\JourFerieResource;
use App\Models\JourFerie;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class JourFerieController extends Controller
{
    /**
     * API: Liste des jours fériés
     */
    public function index()
    {
        return JourFerieResource::collection(
            JourFerie::query()
                ->with('anneeScolaire')
                ->orderBy('date', 'desc')
                ->get()
        );
    }

    /**
     * Enregistrer un nouveau jour férié
     */
    public function store(Request $request)
    {
        $request->validate([
            'titre' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'est_recurrent' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
            'annee_scolaire_id' => ['nullable', 'exists:annee_scolaires,id'],
        ]);

        $data = $request->all();
        $data['slug'] = Str::slug($request->titre . '-' . $request->date . '-' . Str::random(4));
        
        // Si annee_scolaire_id n'est pas fourni, essayer de l'injecter via le helper si existant
        if (empty($data['annee_scolaire_id']) && function_exists('injectAnneeScolaireId')) {
            $data = array_merge($data, injectAnneeScolaireId());
        }

        $jourFerie = JourFerie::create($data);

        return new JourFerieResource($jourFerie);
    }

    /**
     * Afficher un jour férié spécifique
     */
    public function show(JourFerie $jourFerie)
    {
        return new JourFerieResource($jourFerie);
    }

    /**
     * Mettre à jour un jour férié
     */
    public function update(Request $request, JourFerie $jourFerie)
    {
        $request->validate([
            'titre' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'est_recurrent' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
            'annee_scolaire_id' => ['nullable', 'exists:annee_scolaires,id'],
        ]);

        $jourFerie->update($request->all());

        return new JourFerieResource($jourFerie);
    }

    /**
     * Supprimer un jour férié
     */
    public function destroy(JourFerie $jourFerie)
    {
        $jourFerie->delete();

        return response()->json([
            'success' => true,
            'message' => 'Jour férié supprimé avec succès'
        ]);
    }
}
