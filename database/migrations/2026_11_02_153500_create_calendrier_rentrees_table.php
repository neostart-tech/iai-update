<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendrier_rentrees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('annee_scolaire_id')->constrained('annee_scolaires')->cascadeOnDelete();
            $table->foreignId('niveau_id')->constrained('niveaux')->cascadeOnDelete();
            $table->foreignId('filiere_id')->nullable()->constrained('filieres')->nullOnDelete();
            $table->date('date_rentree');
            $table->date('date_fin')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();

            $table->index(['annee_scolaire_id', 'niveau_id']);
            $table->index(['annee_scolaire_id', 'filiere_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendrier_rentrees');
    }
};

