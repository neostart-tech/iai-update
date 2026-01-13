<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('evaluation_case_study_contexts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_id')->constrained('evaluations')->onDelete('cascade');
            $table->text('problematic'); // Problématique
            $table->text('resources')->nullable(); // Ressources
            $table->text('instructions')->nullable(); // Consignes de présentation
            $table->timestamps();

            $table->unique('evaluation_id'); // Une seule entrée par évaluation
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluation_case_study_contexts');
    }
};
