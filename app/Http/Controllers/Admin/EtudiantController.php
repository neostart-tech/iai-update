<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Etudiant;
use App\Models\Filiere;
use App\Models\Group;
use App\Models\Niveau;
use App\Imports\EtudiantsImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;


class EtudiantController extends Controller
{
    public function index(Request $request)
    {
        $anneeActiveId = injectAnneeScolaireId();

        $etudiants = Etudiant::whereHas('etudiantGroups', function ($q) use ($anneeActiveId, $request) {

            $q->where('annee_scolaire_id', $anneeActiveId);

            if ($request->filled('group_id')) {
                $q->where('group_id', $request->group_id);
            }

            if ($request->filled('filiere_id')) {
                $q->where('filiere_id', $request->filiere_id);
            }

            if ($request->filled('niveau_id')) {
                $q->where('niveau_id', $request->niveau_id);
            }
        })->with([
            'etudiantGroups' => function ($q) use ($anneeActiveId) {
                $q->where('annee_scolaire_id', $anneeActiveId)
                    ->latest('id');
            },
            'etudiantGroups.group',
            'etudiantGroups.filiere',
            'etudiantGroups.niveau',
        ])->orderBy('nom')->orderBy('prenom')->get();

        return view('admin.etudiants._index', [
            'etudiants' => $etudiants,
            'groupes'   => Group::with('niveau')->get(),
            'groups'   => Group::with('niveau')->get(),
            'filieres'  => Filiere::all(),
            'niveaux'   => Niveau::all(),
        ]);
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
