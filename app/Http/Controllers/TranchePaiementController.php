<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FraisScolarite;
use App\Models\TranchePaiement;


class TranchePaiementController extends Controller
{
    public function index(){
        return view('comptabilite.Tranche._index');
    }

   public function store(Request $request)
{
    $request->validate([
        'frais_id' => 'required|exists:frais_scolarites,id',
        'tranches' => 'required|array|min:1',
        'tranches.*.libelle' => 'required|string',
        'tranches.*.montant' => 'required|numeric|min:1',
        'tranches.*.date_limite' => 'required|date',
    ],
    [
        'frais_id.required' => 'Le frais est requis',
        'tranches.required' => 'Veuillez saisir le montant de la tranche',
    ]
);

    $frais = FraisScolarite::findOrFail($request->frais_id);
    $anneeId = $frais->annee_scolaire_id;

    //Montant déjà enregistré dans la base pour ce frais
    $sommeExistante = TranchePaiement::where('frais_scolarite_id', $frais->id)->sum('montant');

    // Montant des nouvelles tranches à ajouter
    $sommeNouvelle = collect($request->tranches)->sum('montant');

    //Total cumulé
    $total = $sommeExistante + $sommeNouvelle;

    if ($total > $frais->montant) {
        $reste = $frais->montant - $sommeExistante;

        return back()->with('error', "La somme des tranches dépasse le montant du frais. Il vous reste seulement $reste F disponible.");
    }

   
    foreach ($request->tranches as $tranche) {
        TranchePaiement::create([
            'frais_scolarite_id' => $frais->id,
            'annee_scolaire_id' => $anneeId,
            'libelle' => $tranche['libelle'],
            'montant' => $tranche['montant'],
            'date_limite' => $tranche['date_limite'],
        ]);
    }

    return back()->with('success', 'Tranches enregistrées avec succès.');
}


public function update(Request $request, $id)
{
    $request->validate([
        'libelle' => 'required|string',
        'montant' => 'required|numeric|min:1',
        'date_limite' => 'required|date',
    ]);

    $tranche = TranchePaiement::findOrFail($id);
    $fraisId = $tranche->frais_scolarite_id;

    // vérif du montant total
    $autresTranches = TranchePaiement::where('frais_scolarite_id', $fraisId)
        ->where('id', '!=', $tranche->id)
        ->sum('montant');

    $nouveauTotal = $autresTranches + $request->montant;
    if ($nouveauTotal > $tranche->fraisScolarite->montant) {
        return response()->json([
            "error"=>"La somme des tranches dépasse le montant total autorisé."
        ]);
    }

    //Vérification des dates
    $dateModifiee = $request->date_limite;

    // Tranche précédente (avec une date inférieure)
    $tranchePrecedente = TranchePaiement::where('frais_scolarite_id', $fraisId)
        ->where('id', '!=', $tranche->id)
        ->where('date_limite', '<', $tranche->date_limite)
        ->orderBy('date_limite', 'desc')
        ->first();

    // Tranche suivante (avec une date supérieure)
    $trancheSuivante = TranchePaiement::where('frais_scolarite_id', $fraisId)
        ->where('id', '!=', $tranche->id)
        ->where('date_limite', '>', $tranche->date_limite)
        ->orderBy('date_limite', 'asc')
        ->first();

    if ($tranchePrecedente && $dateModifiee <= $tranchePrecedente->date_limite) {
        return response()->json([
            "error"=>'La date doit être postérieure à celle de la tranche précédente (' . \Carbon\Carbon::parse($tranchePrecedente->date_limite)->format('d/m/Y') . ').'
        ]);
    }

    if ($trancheSuivante && $dateModifiee >= $trancheSuivante->date_limite) {
         return response()->json([
            "error"=>'La date doit être antérieure à celle de la tranche suivante (' . \Carbon\Carbon::parse($trancheSuivante->date_limite)->format('d/m/Y') . ').'
        ]);
    }

    $tranche->update([
        'libelle' => $request->libelle,
        'montant' => $request->montant,
        'date_limite' => $dateModifiee,
    ]);

       return response()->json([
            "success"=>'Tranche modifiée avec succès.'
        ]);    
}

    /**
     * Supprimer une tranche
     */
    public function destroy($id)
    {
        $tranche = TranchePaiement::findOrFail($id);
        $tranche->delete();

          return response()->json([
            "success"=>'Tranche supprimée avec succès.'
        ]);  
    }
}
