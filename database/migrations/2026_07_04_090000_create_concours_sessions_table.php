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
        Schema::create('concours_sessions', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('annee_scolaire_id')->unique()->constrained('annee_scolaires')->cascadeOnDelete();
            $table->string('libelle');
            $table->boolean('avec_epreuve_ecrite')->default(false);
            $table->date('date_debut_depot')->nullable();
            $table->date('date_fin_depot')->nullable();
            $table->date('date_epreuve')->nullable();
            $table->date('date_publication_resultats')->nullable();
            $table->string('statut')->default('brouillon'); // brouillon | ouvert | clos
            $table->boolean('est_publiee')->default(false);
            $table->text('communique')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('concours_sessions');
    }
};
