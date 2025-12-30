<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\Etudiant;
use App\Models\User;
use Illuminate\Http\Request;

class ClubController extends Controller
{
    public function index()
    {
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

        Club::create($request->all());

        return redirect()->back()
            ->with('success', 'Club créé avec succès');
    }

    public function update(Request $request, Club $club)
    {

        $request->validate([
            'nom' => 'required',
            'responsable_id' => 'required|exists:users,id',
        ]);


        $club->update($request->only($request->validate()));

        return redirect()->back()
            ->with('success', 'Club mise a jour avec succès');
    }


    public function getEtudiant(Club $club)
    {
        $etudiants = Etudiant::whereDoesntHave('clubs', function ($query) use ($club) {
            $query->where('clubs.id', $club->id);
        })->get();
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

        return back()->with('success', 'Étudiants ajoutés au club avec succès');
    }

    public function destroyEtudiant(Club $club, Etudiant $etudiant)
    {
        $club->etudiants()->detach($etudiant->id);
        return back()->with('success', 'Étudiant retiré du club');
    }
}
