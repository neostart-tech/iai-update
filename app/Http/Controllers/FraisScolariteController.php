<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\FraisScolarite;
use App\Models\Niveau;
use App\Models\AnneeScolaire;
use App\Enums\GenreEnum;
use App\Http\Resources\AnneeScolaireResource;
use App\Http\Resources\FraisScolariteResource;
use App\Http\Resources\NiveauResource;
use Illuminate\Http\Request;
use App\Models\TranchePaiement;
use Carbon\Carbon;


class FraisScolariteController extends Controller
{
    public function index()
    {
        $frais = FraisScolarite::with(['anneeScolaire', 'niveau', 'filiere','tranchepaiement'])->get();
        // $annees = AnneeScolaire::all();
        // $niveaux = Niveau::all();

        // return response()->json(
        //     [
        //         "frais" => FraisScolariteResource::collection($frais),
        //         "annees" => AnneeScolaireResource::collection($annees),
        //         "niveaux" => NiveauResource::collection($niveaux),
        //     ]
        // );

        return FraisScolariteResource::collection($frais);

        // return view('comptabilite.frais.index', compact('frais', 'annees', 'niveaux'));
    }


    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'niveau_id' => 'required|exists:niveaux,id',
    //         'filiere_id'=>'nullable',
    //         'montant' => 'required|integer|min:0',
    //         'genre' => 'nullable',
    //         'description' => 'nullable|string|max:255',
    //     ]);

    //     $anne = AnneeScolaire::where('active', true)->first()->getAttribute('id');

    //     // Vérifier qu'il n'existe pas déjà des frais pour ce niveau et ce genre
    //     $existingFrais = FraisScolarite::where('annee_scolaire_id', $anne)
    //         ->where('niveau_id', $request->niveau_id)
    //         ->where('genre', $request->genre)
    //         ->first();

    //     if ($existingFrais) {
    //         return redirect()->back()->withErrors(['genre' => 'Des frais existent déjà pour ce niveau et ce genre.']);
    //     }

    //     $frais = FraisScolarite::create([
    //         "annee_scolaire_id" => $anne,
    //         ...$request->all()
    //     ]);

    //     // return new FraisScolariteResource($frais);
    //     return redirect()->route('comptable.frais.index')->with('success', 'Frais enregistré avec succès');
    // }
    /**
     * Dupliquer les frais d'une année vers une autre
     */
    public function duplicate(Request $request)
    {
        $request->validate([
            'source_year_id' => 'required|exists:annee_scolaires,id',
            'target_year_id' => 'required|exists:annee_scolaires,id'
        ]);

        $sourceFrais = FraisScolarite::where('annee_scolaire_id', $request->source_year_id)->get();
        $count = 0;

        foreach ($sourceFrais as $item) {
            // Créer le nouveau frais
            $newFrais = $item->replicate();
            $newFrais->annee_scolaire_id = $request->target_year_id;
            $newFrais->save();

            // Dupliquer aussi les tranches si elles existent
            foreach ($item->tranchepaiement as $tranche) {
                $newTranche = $tranche->replicate();
                $newTranche->frais_scolarite_id = $newFrais->id;
                $newTranche->save();
            }
            $count++;
        }

        return response()->json(['message' => "$count frais dupliqués avec succès"]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'niveau_id' => 'required',
            'filiere_id' => "nullable",
            'montant' => 'required|numeric|min:1000',
            'genre' => 'nullable',
            "description" => 'nullable',
            "frequence" => "nullable|in:annuel,trimestriel,bimestriel" // Nouvelle option
        ]);

        $frais = FraisScolarite::create([
            ...$request->all(),
            "annee_scolaire_id" => AnneeScolaire::courante()->id
        ]);

        // Génération automatique des tranches si demandé
        if ($request->frequence) {
            $this->generateAutoTranches($frais, $request->frequence);
        }

        return new FraisScolariteResource($frais->load('tranchepaiement'));
    }

    public function update(Request $request, $frais)
    {
        $frais = FraisScolarite::findOrFail($frais);
        $frais->update($request->except(['existingTranches']));

        if ($request->frequence) {
            // Supprimer les anciennes tranches avant de régénérer
            $frais->tranchepaiement()->delete();
            $this->generateAutoTranches($frais, $request->frequence);
        }

        return new FraisScolariteResource($frais->load('tranchepaiement'));
    }

    /**
     * Génère les tranches de paiement automatiquement
     */
    private function generateAutoTranches($frais, $frequency)
    {
        $anneeActive = AnneeScolaire::where('active', true)->first();
        $dateDebut = $anneeActive && $anneeActive->date_debut ? Carbon::parse($anneeActive->date_debut) : Carbon::now();
        
        $nbTranches = match($frequency) {
            'annuel' => 1,
            'trimestriel' => 3,
            'bimestriel' => 6,
            default => 1
        };

        $intervalle = match($frequency) {
            'annuel' => 12,
            'trimestriel' => 3,
            'bimestriel' => 2,
            default => 3
        };

        $totalMontant = $frais->montant;
        $montantTranche = floor($totalMontant / $nbTranches);
        $reliquat = $totalMontant % $nbTranches;

        for ($i = 0; $i < $nbTranches; $i++) {
            $echeance = (clone $dateDebut)->addMonths($i * $intervalle);
            
            \App\Models\TranchePaiement::create([
                'frais_scolarite_id' => $frais->id,
                'annee_scolaire_id' => $frais->annee_scolaire_id,
                'libelle' => 'Tranche ' . ($i + 1),
                'montant' => ($i === 0) ? ($montantTranche + $reliquat) : $montantTranche,
                'date_limite' => $echeance->format('Y-m-d'),
                'status' => true
            ]);
        }
    }

    public function destroy($id)
    {
        $frais = FraisScolarite::findOrFail($id);
        $frais->delete();
        return new FraisScolariteResource($frais);
    }

    public function show($id)
    {
        $frais = FraisScolarite::with('tranchepaiement')->find($id);
        return new FraisScolariteResource($frais);
    }
}
