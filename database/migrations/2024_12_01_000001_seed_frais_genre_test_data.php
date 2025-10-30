<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\FraisScolarite;
use App\Models\Niveau;
use App\Models\AnneeScolaire;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Créer des frais de test avec différenciation par genre
        $anneeScolaire = AnneeScolaire::where('active', true)->first();
        $niveaux = Niveau::all();
        
        if ($anneeScolaire && $niveaux->count() > 0) {
            foreach ($niveaux->take(2) as $niveau) {
                // Frais pour les hommes
                FraisScolarite::create([
                    'annee_scolaire_id' => $anneeScolaire->id,
                    'niveau_id' => $niveau->id,
                    'montant' => 150000,
                    'genre' => 'Masculin',
                    'description' => 'Frais de scolarité pour les étudiants (hommes) - ' . $niveau->libelle
                ]);
                
                // Frais pour les femmes (avec réduction)
                FraisScolarite::create([
                    'annee_scolaire_id' => $anneeScolaire->id,
                    'niveau_id' => $niveau->id,
                    'montant' => 120000,
                    'genre' => 'Féminin',
                    'description' => 'Frais de scolarité pour les étudiantes (femmes) avec réduction - ' . $niveau->libelle
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer les données de test
        FraisScolarite::whereIn('genre', ['Masculin', 'Féminin'])->delete();
    }
};