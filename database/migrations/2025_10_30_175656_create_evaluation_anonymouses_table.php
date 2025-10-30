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
        Schema::create('evaluation_anonymouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_id')->constrained()->onDelete('cascade');
            $table->foreignId('etudiant_id')->constrained()->onDelete('cascade');
            $table->string('anonymous_code', 10)->unique();
            $table->string('salle_nom')->nullable(); // Nom de la salle au moment de la génération
            $table->timestamps();
            
            // Index pour recherche rapide
            $table->index(['evaluation_id', 'etudiant_id']);
            $table->index('anonymous_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluation_anonymouses');
    }
};
