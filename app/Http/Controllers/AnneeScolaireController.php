<?php

namespace App\Http\Controllers;

use App\Http\Resources\AnneeScolaireResource;
use App\Models\AnneeScolaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class AnneeScolaireController extends Controller
{
    // Affiche la liste des années scolaires
    public function index()
    {
        $annees = AnneeScolaire::all();
        return AnneeScolaireResource::collection($annees);


        // return view('admin.AnneScolaire._index', compact('annees'));
    }



    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|unique:annee_scolaires,nom',
            "date_debut" => "required",
            "date_fin" => "required",
        ]);

        return DB::transaction(function () use ($request) {

            AnneeScolaire::where('active', true)->update(['active' => false]);


            $year = now();
            $slug = bin2hex(random_bytes(6));

            $annee = AnneeScolaire::create([
                'nom' => $request->nom,
                'date_debut' => $request->date_debut,
                "date_fin" => $request->date_fin,
                'slug' => $slug,
                'code' => "as_" . $year->format("Y") . '_' . $year->copy()->addYear()->format("Y"),
                'active' => true,
            ]);

            return new AnneeScolaireResource($annee);
        });
    }

    public function update(Request $request, AnneeScolaire $annee)
    {
        $request->validate([
            'nom' => 'required|string',
            "date_debut" => "required",
            "date_fin" => "required",
        ]);

        return DB::transaction(function () use ($request, $annee) {



            $year = now();
            $slug = bin2hex(random_bytes(6));

            $annee->update([
                'nom' => $request->nom,
                'date_debut' => $request->date_debut,
                "date_fin" => $request->date_fin,
                'slug' => $slug,
                'code' => "as_" . $year->format("Y") . '_' . $year->copy()->addYear()->format("Y"),
                
            ]);

            return new AnneeScolaireResource($annee);
        });
    }
    public function activer($id)
    {
        return DB::transaction(function () use ($id) {

            $annee = AnneeScolaire::findOrFail($id);

            // Si elle est déjà active
            if ($annee->active) {
                return response()->json([
                    'message' => "Cette année est déjà active."
                ], 200);
            }

            // Désactiver toutes les autres
            AnneeScolaire::where('active', true)->update(['active' => false]);

            // Activer celle-ci
            $annee->update(['active' => true]);

            return new AnneeScolaireResource($annee);
        });
    }

    public function desactiver($id)
    {
        $annee = AnneeScolaire::findOrFail($id);

        $activeCount = AnneeScolaire::where('active', true)->count();

        if ($annee->active && $activeCount <= 1) {
            abort(404, "Impossible de désactiver l'unique année scolaire active.");
        }

        $annee->update(['active' => false]);

        return new AnneeScolaireResource($annee);
    }
}
