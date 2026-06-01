<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table pour les questions composées de sous-questions
     * Exemple: Question 3a, 3b, 3c de la PARTIE III
     */
    public function up(): void
    {
        Schema::create('exam_question_multi_parts', function (Blueprint $table) {
            $table->id();
            
            // Lien avec la question parent
            $table->foreignId('question_id')
                  ->constrained('exam_questions')
                  ->onDelete('cascade');
            
            // Configuration globale
            $table->json('configuration')
                  ->nullable()
                  ->comment('Configuration globale');
            
            // Les sous-questions
            $table->json('parts')
                  ->comment('Tableau des sous-questions avec leurs types et points');
            
            $table->timestamps();
            
            $table->index('question_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_question_multi_parts');
    }
};