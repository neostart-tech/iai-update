<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use App\Models\Etudiant;
use App\Models\EtudiantGroup;
use App\Models\Filiere;
use App\Models\Group;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class CarteEtudiantController extends Controller
{
    public function genererCarteEtudiant(Etudiant $etudiant_id)
    {

        $fileName = 'carte' . $etudiant_id->nom . $etudiant_id->prenom . '_' . time() . '.pdf';
        $groupetu = EtudiantGroup::where('etudiant_id', $etudiant_id->id)->first()->getAttribute('group_id');
        $filiereid = Group::where('id', $groupetu)->first()->getAttribute('filiere_id');
        $filiere = Filiere::find($filiereid)->getAttribute('code');
        $annee = AnneeScolaire::first()->getAttribute('nom');




        $pdf = Pdf::loadView('carte-etudiant.index', [
            'etudiant' => $etudiant_id,
            'photo' => $etudiant_id->albums[0]->photo,
            'filiere' => $filiere,
            'annee' => $annee,
        ])->setPaper('A4');
        return $pdf->download($fileName);



        //    return view('carte-etudiant.index', [
        //         'etudiant' => $etudiant_id,
        //         'photo' => $etudiant_id->albums[0]->photo,
        //         'filiere' =>$filiere,

        //         'annee' => $annee,
        //     ]);

    }
}
