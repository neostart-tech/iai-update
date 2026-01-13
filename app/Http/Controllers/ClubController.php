<?php

namespace App\Http\Controllers;

use App\Http\Resources\ClubResource;
use App\Http\Resources\UserResource;
use App\Models\Club;
use App\Models\Etudiant;
use App\Models\User;
use Illuminate\Http\Request;

class ClubController extends Controller
{
    public function index()
    {

        // return ClubResource::collection(
        //     Club::with('responsable')
        //         ->withCount('etudiants')
        //         ->get()
        // );

        $clubs = Club::with('responsable')
            ->withCount('etudiants')
            ->get();
        $responsables = User::whereHas('roles', function ($q) {
            $q->where('nom', 'Enseignant');
        })->get();

        return view('admin.clubs.index', compact('clubs', 'responsables'));
    }


    public function create()
    {
        // Users ayant le rôle enseignant ou personnel
        $responsables = User::whereHas('roles', function ($q) {
            $q->where('nom', 'Enseignant');
        })->get();

        return view('admin.clubs.create', compact('responsables'));
    }

    public function store(Request $request)
    {

        $request->validate([
            'nom' => 'required',
            'responsable_id' => 'required|exists:users,id',
        ]);

        $club = Club::create($request->all());

        // return new ClubResource($club);
        return redirect()->back()
            ->with('success', 'Club créé avec succès');
    }

    public function update(Request $request, Club $club)
    {

        $request->validate([
            'nom' => 'required',
            'responsable_id' => 'required|exists:users,id',
        ]);


        $club = $club->update($request->only($request->validate()));
        // return new ClubResource($club);

        return redirect()->back()
            ->with('success', 'Club mise a jour avec succès');
    }

    public function destroy(Club $club)
    {

        if ($club->etudiants()) {
            return response()->json(['success' => "Vous n'etes pas autorisé a supprimer ce club car des etudiants en font partie"]);
        }
        $club->delete();
        // return new ClubResource($club);
        return response()->json(['success' => "CLub supprimé avec succes"]);
    }



    public function getEtudiant(Club $club)
    {
        $etudiants = Etudiant::whereDoesntHave('clubs', function ($query) use ($club) {
            $query->where('clubs.id', $club->id);
        })->get();

        // return UserResource::collection($etudiants);
        return view('admin.clubs.etudiants.index', compact('club', 'etudiants'));
    }

    public function storeEtudiant(Request $request, Club $club)
    {
        $request->validate([
            'etudiant_ids' => 'required|array',
            'etudiant_ids.*' => 'exists:etudiants,id'
        ]);


        $data = [];
        foreach ($request->etudiant_ids as $id) {
            $data[$id] = ['date_adhesion' => now()];
        }

        $club->etudiants()->syncWithoutDetaching($data);

        // return new ClubResource($club);

        return back()->with('success', 'Étudiants ajoutés au club avec succès');
    }

    public function destroyEtudiant(Club $club, Etudiant $etudiant)
    {
        $club->etudiants()->detach($etudiant->id);

        // return new ClubResource($club);

        return back()->with('success', 'Étudiant retiré du club');
    }
}
