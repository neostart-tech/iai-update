<?php

namespace App\Http\Controllers;

use App\Models\FraisInscription;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FraisInscriptionController extends Controller
{

    public function index()
    {
        $frais = FraisInscription::latest()->get();


        // return response()->json(
        //     [
        //         "frais" => FraisScolariteResource::collection($frais),
        //         "annees" => AnneeScolaireResource::collection($annees),
        //         "niveaux" => NiveauResource::collection($niveaux),
        //     ]
        // );

        return view('comptabilite.frais_inscription.index', compact('frais'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'montant' => 'required|integer|min:100',
        ]);


        $frais = FraisInscription::create([
            'montant' => $request->montant,
            'slug' => Str::uuid(),
        ]);

        // return new FraisScolariteResource($frais);
        return redirect()->route('comptable.frais-inscription.index')->with('success', 'Frais enregistré avec succès');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'montant' => 'required|integer|min:0',
        ]);

        $frais = FraisInscription::findOrFail($id);

        $frais->update($request->all());
        // return new FraisScolariteResource($frais);

        return redirect()->route('comptable.frais-inscription.index')->with('success', 'Frais modifié avec succès');
    }

    public function destroy($id)
    {
        $frais = FraisInscription::findOrFail($id);
        $frais->delete();

        // return new FraisScolariteResource($frais);


        return redirect()->route('comptable.frais-inscription.index')->with('success', 'Frais supprimé avec succès');
    }
}
