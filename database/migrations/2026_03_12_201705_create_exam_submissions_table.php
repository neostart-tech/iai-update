<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_id')->constrained()->onDelete('cascade');
            $table->foreignId('etudiant_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('exam_questions')->onDelete('cascade');
            $table->json('reponse'); // La réponse de l'étudiant
            $table->boolean('is_correct')->nullable();
            $table->float('points_obtenus')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('auto_saved_at')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            
            // Un étudiant ne peut avoir qu'une seule réponse par question
            $table->unique(['evaluation_id', 'etudiant_id', 'question_id'], 'unique_submission');
            
            $table->index(['evaluation_id', 'etudiant_id']);
            $table->index('submitted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_submissions');
    }
};