<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\FraisScolarite;
use App\Models\Niveau;
use App\Models\AnneeScolaire;
use App\Enums\GenreEnum;
use Illuminate\Http\Request;
use App\Models\TranchePaiement;


class FraisScolariteController extends Controller
{
    public function index()
    {
        $frais = FraisScolarite::with(['anneeScolaire', 'niveau'])->get();
        $annees = AnneeScolaire::all();
        $niveaux =Niveau::all();

        return view('comptabilite.frais.index', compact('frais', 'annees', 'niveaux'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'niveau_id' => 'required|exists:niveaux,id',
            'montant' => 'required|integer|min:0',
            'genre' => 'required|in:Masculin,Féminin,Tous',
            'description' => 'nullable|string|max:255',
        ]);
        
        $anne = AnneeScolaire::where('active', true)->first()->getAttribute('id');
        
        // Vérifier qu'il n'existe pas déjà des frais pour ce niveau et ce genre
        $existingFrais = FraisScolarite::where('annee_scolaire_id', $anne)
            ->where('niveau_id', $request->niveau_id)
            ->where('genre', $request->genre)
            ->first();
            
        if ($existingFrais) {
            return redirect()->back()->withErrors(['genre' => 'Des frais existent déjà pour ce niveau et ce genre.']);
        }
        
        FraisScolarite::create([
            "annee_scolaire_id" => $anne,
            ...$request->all()
        ]);
        
        return redirect()->route('comptable.frais.index')->with('success', 'Frais enregistré avec succès');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'niveau_id' => 'required|exists:niveaux,id',
            'montant' => 'required|integer|min:0',
            'genre' => 'required|in:Masculin,Féminin,Tous',
            'description' => 'nullable|string|max:255',
        ]);
        
        $frais = FraisScolarite::findOrFail($id);
        
        $frais->update($request->all());
        
        return redirect()->route('comptable.frais.index')->with('success', 'Frais modifié avec succès');
    }

    public function destroy($id)
    {
        $frais = FraisScolarite::findOrFail($id);
        $frais->delete();
        
        return redirect()->route('comptable.frais.index')->with('success', 'Frais supprimé avec succès');
    }

    public function show($id){
        $frais=FraisScolarite::find($id);
        $tranches=TranchePaiement::where('frais_scolarite_id',$id)->latest()->get();
       
        return view('comptabilite.Tranche._index',compact('tranches','frais'));
    }
}