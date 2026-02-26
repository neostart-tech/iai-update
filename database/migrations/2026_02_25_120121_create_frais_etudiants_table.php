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
        Schema::create('frais_etudiants', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->foreignId('etudiant_id')->constrained()->onDelete('cascade');
            $table->foreignId('frais_scolarite_id')->constrained()->onDelete('cascade');
            $table->foreignId('annee_scolaire_id')->constrained()->onDelete('cascade');
            $table->decimal('montant_initial', 12, 0);
            $table->decimal('montant_apres_bourse', 12, 0);
            $table->foreignId('bourse_etudiant_id')->nullable()->constrained()->onDelete('set null');

            $table->enum('type_paiement', ['tranches_globales', 'negociation'])->default('tranches_globales');

            $table->enum('frequence_paiement', ['annuel', 'trimestriel', 'mensuel','bimestriel'])->default('trimestriel');

            $table->enum('statut', ['en_cours', 'solde', 'en_retard'])->default('en_cours');
            $table->timestamps();

            $table->unique(['etudiant_id', 'annee_scolaire_id'], 'unique_frais_etudiant_annee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('frais_etudiants');
    }
};
