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
        try {
            $fileName = 'carte_' . $etudiant_id->nom . '_' . $etudiant_id->prenom . '_' . time() . '.pdf';
            
            // Récupération sécurisée du groupe de l'étudiant
            $etudiantGroup = EtudiantGroup::where('etudiant_id', $etudiant_id->id)->first();
            if (!$etudiantGroup) {
                return abort(404, 'Aucun groupe trouvé pour cet étudiant.');
            }
            
            $groupetu = $etudiantGroup->getAttribute('group_id');
            
            // Récupération sécurisée du groupe et de la filière
            $group = Group::where('id', $groupetu)->first();
            if (!$group) {
                return abort(404, 'Groupe non trouvé.');
            }
            
            $filiereid = $group->getAttribute('filiere_id');
            
            // Récupération sécurisée de la filière
            $filiereModel = Filiere::find($filiereid);
            $filiere = $filiereModel ? $filiereModel->getAttribute('code') : 'Filière non définie';
            
            // Récupération sécurisée de l'année scolaire
            $anneeScolaire = AnneeScolaire::first();
            $annee = $anneeScolaire ? $anneeScolaire->getAttribute('nom') : date('Y') . '-' . (date('Y') + 1);
            
            // Récupération sécurisée de la photo de l'étudiant
            $photo = null;
            if ($etudiant_id->albums && $etudiant_id->albums->isNotEmpty() && isset($etudiant_id->albums[0]->photo)) {
                $photo = $etudiant_id->albums[0]->photo;
            }

            $pdf = Pdf::loadView('carte-etudiant.index', [
                'etudiant' => $etudiant_id,
                'photo' => $photo,
                'filiere' => $filiere,
                'annee' => $annee,
            ])->setPaper('A4');
            
            return $pdf->download($fileName);
            
        } catch (\Exception $e) {
            return abort(500, 'Erreur lors de la génération de la carte : ' . $e->getMessage());
        }



        //    return view('carte-etudiant.index', [
        //         'etudiant' => $etudiant_id,
        //         'photo' => $etudiant_id->albums[0]->photo,
        //         'filiere' =>$filiere,

        //         'annee' => $annee,
        //     ]);

    }
}
