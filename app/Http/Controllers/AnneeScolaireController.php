<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


class AnneeScolaireController extends Controller
{
    // Affiche la liste des années scolaires
    public function index()
    {
        $annees = AnneeScolaire::all();
        return view('admin.AnneScolaire._index', compact('annees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|unique:annee_scolaires,nom',
        ]);

        AnneeScolaire::where('active', true)->update(['active' => false]);
 $slug = bin2hex(random_bytes(6));
       
       $year = now();
$annee = AnneeScolaire::create([
    'nom' => $request->nom,
     'slug' => $slug,
    'code' => "as_" . $year->format("Y") . '_' . $year->copy()->addYear()->format("Y"),
    'active' => true,
]);


        return redirect()->back()->with(successMsg('Nouvelle année créée et activée'));
    }

    public function activer($id)
    {
        AnneeScolaire::where('active', true)->update(['active' => false]);

        $annee = AnneeScolaire::findOrFail($id);
        $annee->update(['active' => true]);

        return redirect()->back()->with('success','Année activée avec succes');
    }

    public function desactiver($id)
    {
        $annee = AnneeScolaire::findOrFail($id);
        $annee->update(['active' => false]);

        return redirect()->back()->with('success','Année désactivée avec succes');
    }
}
