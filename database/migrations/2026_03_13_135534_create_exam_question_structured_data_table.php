<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table pour les questions avec réponses structurées
     * Exemples: choix multiples + justifications, QCM avec textes, etc.
     */
    public function up(): void
    {
        Schema::create('exam_question_structured_data', function (Blueprint $table) {
            $table->id();
            
            // Lien avec la question parent
            $table->foreignId('question_id')
                  ->constrained('exam_questions')
                  ->onDelete('cascade');
            
            // Type de structure (ex: "strategies_4t", "qcm_justifie", etc.)
            $table->string('structure_type')
                  ->default('strategies_4t')
                  ->comment('Type de structure: strategies_4t, qcm_justifie, etc.');
            
            // Les éléments de la structure (options, choix, etc.)
            $table->json('structure')
                  ->comment('Structure de la question');
            
            // Les données à traiter par l'étudiant
            $table->json('items')
                  ->comment('Les items/questions à traiter');
            
            // Barème de notation
            $table->json('bareme')
                  ->nullable()
                  ->comment('Barème détaillé');
            
            $table->timestamps();
            
            $table->index(['question_id', 'structure_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_question_structured_data');
    }
};