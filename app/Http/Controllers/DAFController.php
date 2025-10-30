<?php

namespace App\Http\Controllers;

use App\Models\FraisScolarite;
use App\Models\Niveau;
use App\Models\AnneeScolaire;
use App\Enums\GenreEnum;
use Illuminate\Http\Request;

class DAFController extends Controller
{
    /**
     * Interface de paramétrage des frais différenciés par genre
     */
    public function configureFraisGenre()
    {
        // Les permissions sont déjà vérifiées par le middleware
        
        $frais = FraisScolarite::with(['anneeScolaire', 'niveau'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            
        $annees = AnneeScolaire::all();
        $niveaux = Niveau::all();
        
        $statistiques = [
            'total_frais' => FraisScolarite::count(),
            'frais_hommes' => FraisScolarite::where('genre', 'Masculin')->count(),
            'frais_femmes' => FraisScolarite::where('genre', 'Féminin')->count(),
            'frais_mixtes' => FraisScolarite::where('genre', 'Tous')->count(),
        ];
        
        return view('daf.frais-genre.index', compact('frais', 'annees', 'niveaux', 'statistiques'));
    }
    
    /**
     * Créer des frais différenciés par genre
     */
    public function storeFraisGenre(Request $request)
    {
        // Les permissions sont déjà vérifiées par le middleware
        
        $request->validate([
            'niveau_id' => 'required|exists:niveaux,id',
            'montant_hommes' => 'nullable|integer|min:0',
            'montant_femmes' => 'nullable|integer|min:0',
            'montant_mixte' => 'nullable|integer|min:0',
            'description' => 'nullable|string|max:255',
        ]);
        
        $anneActive = AnneeScolaire::where('active', true)->first();
        
        // Supprimer les anciens frais pour ce niveau et cette année
        FraisScolarite::where('niveau_id', $request->niveau_id)
            ->where('annee_scolaire_id', $anneActive->id)
            ->delete();
        
        // Créer les nouveaux frais
        if ($request->montant_hommes) {
            FraisScolarite::create([
                'annee_scolaire_id' => $anneActive->id,
                'niveau_id' => $request->niveau_id,
                'montant' => $request->montant_hommes,
                'genre' => 'Masculin',
                'description' => $request->description . ' - Spécifique aux hommes'
            ]);
        }
        
        if ($request->montant_femmes) {
            FraisScolarite::create([
                'annee_scolaire_id' => $anneActive->id,
                'niveau_id' => $request->niveau_id,
                'montant' => $request->montant_femmes,
                'genre' => 'Féminin',
                'description' => $request->description . ' - Spécifique aux femmes'
            ]);
        }
        
        if ($request->montant_mixte) {
            FraisScolarite::create([
                'annee_scolaire_id' => $anneActive->id,
                'niveau_id' => $request->niveau_id,
                'montant' => $request->montant_mixte,
                'genre' => 'Tous',
                'description' => $request->description . ' - Pour tous les étudiants'
            ]);
        }
        
        return redirect()->back()->with('success', 'Configuration des frais différenciés créée avec succès');
    }
    
    /**
     * Rapport des frais par genre
     */
    public function rapportFraisGenre()
    {
        // Les permissions sont déjà vérifiées par le middleware
        
        $rapports = [];
        $niveaux = Niveau::all();
        
        foreach ($niveaux as $niveau) {
            $fraisHommes = FraisScolarite::where('niveau_id', $niveau->id)
                ->where('genre', 'Masculin')
                ->first();
                
            $fraisFemmes = FraisScolarite::where('niveau_id', $niveau->id)
                ->where('genre', 'Féminin')
                ->first();
                
            $fraisMixtes = FraisScolarite::where('niveau_id', $niveau->id)
                ->where('genre', 'Tous')
                ->first();
            
            $rapports[] = [
                'niveau' => $niveau,
                'frais_hommes' => $fraisHommes,
                'frais_femmes' => $fraisFemmes,
                'frais_mixtes' => $fraisMixtes,
                'difference' => $fraisHommes && $fraisFemmes 
                    ? ($fraisHommes->montant - $fraisFemmes->montant) 
                    : 0
            ];
        }
        
        return view('daf.frais-genre.rapport', compact('rapports'));
    }
}