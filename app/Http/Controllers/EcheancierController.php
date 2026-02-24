<?php

namespace App\Http\Controllers;

use App\Models\Etudiant;
use App\Models\FraisScolarite;
use App\Models\PlanPaiement;
use App\Services\GenererEcheancierService;
use Illuminate\Http\Request;

class EcheancierController extends Controller
{
      public function generer(Request $request)
    {
        $data = $request->validate([
            'etudiant_id' => 'required|exists:etudiants,id',
            'frais_id' => 'required|exists:frais_scolarites,id',
            'plan_id' => 'required|exists:plan_paiements,id'
        ]);

        $etudiant = Etudiant::with('bourses')->findOrFail($data['etudiant_id']);
        $frais = FraisScolarite::findOrFail($data['frais_id']);
        $plan = PlanPaiement::findOrFail($data['plan_id']);

        return app(GenererEcheancierService::class)
            ->generer($etudiant, $frais, $plan);
    }

    public function show(Etudiant $etudiant)
    {
        return $etudiant->echeancier()
            ->with('echeances.paiements')
            ->first();
    }
}
