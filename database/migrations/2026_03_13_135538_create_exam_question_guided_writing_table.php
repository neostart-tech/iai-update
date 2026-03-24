<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table pour les questions de rédaction avec consignes
     * Exemple: Plan d'action avec points précis à aborder
     */
    public function up(): void
    {
        Schema::create('exam_question_guided_writing', function (Blueprint $table) {
            $table->id();
            
            // Lien avec la question parent
            $table->foreignId('question_id')
                  ->constrained('exam_questions')
                  ->onDelete('cascade');
            
            // Consignes pour la rédaction
            $table->json('instructions')
                  ->comment('Consignes détaillées');
            
            // Critères d'évaluation
            $table->json('criteria')
                  ->nullable()
                  ->comment('Critères pour la correction');
            
            // Limites de mots
            $table->integer('min_words')
                  ->nullable()
                  ->default(50)
                  ->comment('Nombre minimum de mots');
            
            $table->integer('max_words')
                  ->nullable()
                  ->default(500)
                  ->comment('Nombre maximum de mots');
            
            $table->timestamps();
            
            $table->index('question_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_question_guided_writing');
    }
};