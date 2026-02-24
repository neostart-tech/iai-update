<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\EtudiantRessource;
use App\Models\Etudiant;
use App\Models\Filiere;
use App\Models\Group;
use App\Models\Niveau;
use App\Imports\EtudiantsImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;


class EtudiantController extends Controller
{
    public function index(Request $request)
    {
        $anneeActiveId = injectAnneeScolaireId();

        $etudiants = Etudiant::whereHas('etudiantGroups', function ($q) use ($anneeActiveId, $request) {

            $q->where('annee_scolaire_id', $anneeActiveId);
        })->with([
            'etudiantGroups' => function ($q) use ($anneeActiveId) {
                $q->where('annee_scolaire_id', $anneeActiveId)
                    ->latest('id');
            },
            'etudiantGroups.group',
            'etudiantGroups.filiere',
            'etudiantGroups.niveau',
        ])->orderBy('nom')->orderBy('prenom')->get();

        return EtudiantRessource::collection($etudiants);

        // return view('admin.etudiants._index', [
        //     'etudiants' => $etudiants,
        //     'groupes'   => Group::with('niveau')->get(),
        //     'groups'   => Group::with('niveau')->get(),
        //     'filieres'  => Filiere::all(),
        //     'niveaux'   => Niveau::all(),
        // ]);
    }

    public function getNonBoursiers()
    {
        $anneeActiveId = injectAnneeScolaireId();

        // Récupérer les IDs des étudiants qui ont déjà une bourse pour l'année active
        $etudiantsAvecBourseIds = DB::table('bourse_etudiants')
            ->where('annee_scolaire_id', $anneeActiveId)
            ->pluck('etudiant_id')
            ->toArray();

        // Récupérer uniquement les étudiants SANS bourse pour l'année active
        $etudiants = Etudiant::whereHas('etudiantGroups', function ($q) use ($anneeActiveId) {
            $q->where('annee_scolaire_id', $anneeActiveId);
        })
            ->whereNotIn('id', $etudiantsAvecBourseIds)
            ->with([
                'etudiantGroups' => function ($q) use ($anneeActiveId) {
                    $q->where('annee_scolaire_id', $anneeActiveId)
                        ->latest('id');
                },
                'etudiantGroups.group',
                'etudiantGroups.filiere',
                'etudiantGroups.niveau',
            ])
            ->orderBy('nom')
            ->orderBy('prenom')
            ->get();

        return EtudiantRessource::collection($etudiants);
    }


    public function show(Etudiant $etudiant)
    {
        $anneeActiveId = injectAnneeScolaireId();

        // Récupérer l'étudiant avec ses groupes, filière et niveau
        $etudiant = Etudiant::where('id', $etudiant->id)
            ->with([
                'etudiantGroups' => function ($q) use ($anneeActiveId) {
                    $q->where('annee_scolaire_id', $anneeActiveId)
                        ->latest('id');
                },
                'etudiantGroups.group',
                'etudiantGroups.filiere',
                'etudiantGroups.niveau',
            ])
            ->firstOrFail(); // renvoie 404 si non trouvé

        return new EtudiantRessource($etudiant);
    }


    // public function importEtudiant(Request $request)
    // {
    //     $request->validate([
    //         'file' => 'required|file|mimes:xls,xlsx'
    //     ]);

    //     Excel::import(
    //         new EtudiantsImport,
    //         $request->file('file')
    //     );

    //     return back()->with(
    //         'success',
    //         'Importation réussie avec succès'
    //     );
    // }



    public function importEtudiant(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx'
        ]);

        (new EtudiantsImport)
            ->queue($request->file('file'))
            ->allOnQueue('imports'); // optionnel mais recommandé

        // return back()->with(
        //     'success',
        //     'Import lancé avec succès. Traitement en cours...'
        // );
        return response()->json([
            'status' => 'queued',
            'message' => 'Import lancé avec succès'
        ]);
    }
}
