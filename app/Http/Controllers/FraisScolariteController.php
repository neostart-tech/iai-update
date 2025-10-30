<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\FraisScolarite;
use App\Models\Niveau;
use App\Models\AnneeScolaire;
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
        ]);
        $anne=AnneeScolaire::where('active',true)->first()->getAttribute('id');
        FraisScolarite::create([
         "annee_scolaire_id"=> $anne,
         ...$request->all()
        ]);
        return redirect()->route('comptable.frais.index')->with('success', 'Frais enregistré');
    }

    public function show($id){
        $frais=FraisScolarite::find($id);
        $tranches=TranchePaiement::where('frais_scolarite_id',$id)->latest()->get();
       
        return view('comptabilite.Tranche._index',compact('tranches','frais'));
    }
}