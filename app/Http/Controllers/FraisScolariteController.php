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
    public function store(Request $request)
    {
        $data = $request->validate([
            'niveau_id' => 'required',
            'filiere_id' => "nullable",
            'montant' => 'required|numeric|min:1000',
            'genre' => 'nullable',
            "description" => 'nullable'
        ]);


        $data = FraisScolarite::create([
            ...$request->all(),
            "annee_scolaire_id" => AnneeScolaire::courante()->id
        ]);

        return new FraisScolariteResource($data);
    }

    // public function update(Request $request, $id)
    // {
    //     $request->validate([
    //         'niveau_id' => 'required|exists:niveaux,id',
    //         'montant' => 'required|integer|min:0',
    //         'genre' => 'required|in:Masculin,Féminin,Tous',
    //         'description' => 'nullable|string|max:255',
    //     ]);

    //     $frais = FraisScolarite::findOrFail($id);

    //     $frais->update($request->all());
    //     // return new FraisScolariteResource($frais);

    //     return redirect()->route('comptable.frais.index')->with('success', 'Frais modifié avec succès');
    // }
    public function update(Request $request,$frais)
    {

    $frais=FraisScolarite::find($frais);

        $frais->update($request->all());

        return new FraisScolariteResource($frais);
    }


    public function destroy($id)
    {
        $frais = FraisScolarite::findOrFail($id);
        $frais->delete();

        return new FraisScolariteResource($frais);


        // return redirect()->route('comptable.frais.index')->with('success', 'Frais supprimé avec succès');
    }

    public function show($id)
    {
        $frais = FraisScolarite::find($id);
        // $tranches = TranchePaiement::where('frais_scolarite_id', $id)->latest()->get();
        return new FraisScolariteResource($frais);

        // return view('comptabilite.Tranche._index', compact('tranches', 'frais'));
    }
}
