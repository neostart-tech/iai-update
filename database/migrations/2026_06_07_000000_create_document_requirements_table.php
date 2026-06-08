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
        Schema::create('document_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('niveau_id')->constrained()->cascadeOnDelete();
            $table->foreignId('filiere_id')->nullable()->constrained()->cascadeOnDelete();
            
            // La clé correspondant à la colonne dans la table albums (ex: 'lettre_motivation', 'cv')
            $table->string('document_key');
            
            // Le nom affiché à l'étudiant (ex: "Lettre de motivation")
            $table->string('nom_affichage');
            
            $table->boolean('is_obligatoire')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_requirements');
    }
};
