<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use App\Models\Etudiant;
use App\Models\EtudiantGroup;
use App\Models\Filiere;
use App\Models\Group;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\TemplateProcessor;

class CarteEtudiantController extends Controller
{
    public function genererCarteEtudiant(Etudiant $etudiant)
    {
       $etudiant->load(['tuteur','etudiantGroups']);   
     

        $group_id = EtudiantGroup::where('etudiant_id', $etudiant->id)->max('group_id');
        $group = Group::find($group_id);      
        $group->load('niveau'); 
        $filiere=$etudiant->etudiantGroups->first()->filiere->nom;
         $niveau=$etudiant->etudiantGroups->first()->niveau->libelle;

// dd($filiere);
                       
        $template = new TemplateProcessor(storage_path('app/templates/carte.docx'));
        $template->setValue('i', $etudiant->id);
        $template->setValue('nom', $etudiant->nom);             
        $template->setValue('prenom', $etudiant->prenom);         
        $template->setValue('datenaissance', date_format(date_create($etudiant->date_naissance), 'd/m/Y'));
        $template->setValue('lieunaissance', $etudiant->lieu_naissance);
        $template->setValue('matricule', $etudiant->matricule);
        $template->setValue('tel', $etudiant->tel);
        $template->setValue('genre', $etudiant->genre->value);
        $template->setValue('nationalite', $etudiant->nationalite);
        $template->setValue('tuteur', $etudiant->tuteur?->nom.' '.$etudiant->tuteur?->prenom);
        $template->setValue('niveau', $group->niveau->libelle ?? 'Niveau non défini');
        $template->setValue('filiere',$filiere);
         $template->setValue('niveau',$niveau);
        $template->setValue('anneescolaire', AnneeScolaire::where('active', true)->first()->nom);



        $file = storage_path('app/carte_' . $etudiant->getNomCompletAttribute() . '.docx');
        $template->saveAs($file);

        // return response()->download($file);
        return response()->file($file, [
    'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
]);
    }
}
