<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table générique pour les questions avec données complexes
     * Peut stocker: tableaux d'analyse, matrices, grilles, etc.
     */
    public function up(): void
    {
        Schema::create('exam_question_complex_data', function (Blueprint $table) {
            $table->id();
            
            // Lien avec la question parent
            $table->foreignId('question_id')
                  ->constrained('exam_questions')
                  ->onDelete('cascade')
                  ->comment('La question associée');
            
            // Type spécifique de données (ex: "analyse_risques", "matrice_decision", etc.)
            $table->string('data_type')
                  ->default('analyse_risques')
                  ->comment('Type de données: analyse_risques, matrice, etc.');
            
            // Configuration de la question
            $table->json('configuration')
                  ->nullable()
                  ->comment('Configuration: grilles, seuils, barèmes, etc.');
            
            // Les données à afficher/remplir
            $table->json('data')
                  ->comment('Données de la question (lignes, colonnes, etc.)');
            
            // Métadonnées supplémentaires
            $table->json('metadata')
                  ->nullable()
                  ->comment('Métadonnées additionnelles');
            
            $table->timestamps();
            
            // Index pour recherche rapide
            $table->index(['question_id', 'data_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_question_complex_data');
    }
};