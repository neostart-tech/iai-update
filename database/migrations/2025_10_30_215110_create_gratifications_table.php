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
        Schema::create('gratifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('etudiant_id')->constrained('etudiants')->onDelete('cascade');
            $table->foreignId('unite_enseignement_id')->constrained('unite_enseignements')->onDelete('cascade');
            $table->foreignId('annee_scolaire_id')->constrained('annee_scolaires')->onDelete('cascade');
            $table->text('motif'); // Raison de la gratification
            $table->enum('type', ['excellence', 'participation', 'engagement', 'amelioration', 'autre'])->default('autre');
            $table->boolean('validee')->default(false); // Si la gratification est approuvée
            $table->foreignId('approuvee_par')->nullable()->constrained('users')->onDelete('set null'); // Qui a approuvé
            $table->timestamp('date_approbation')->nullable();
            $table->text('observation')->nullable(); // Commentaires additionnels
            $table->timestamps();
            
            $table->unique(
                ['etudiant_id', 'unite_enseignement_id', 'annee_scolaire_id'],
                'gratifications_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gratifications');
    }
};
