<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('etudiant_profile_requests')) {
            Schema::create('etudiant_profile_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('etudiant_id')->constrained('etudiants')->cascadeOnDelete();
                $table->json('data'); // nouvelles données demandées (nom, prenom, adresse, tuteur, etc.)
                $table->json('documents')->nullable(); // fichiers téléversés temporaires
                $table->enum('statut', ['en_attente', 'valide', 'rejete'])->default('en_attente');
                $table->text('motif_refus')->nullable();
                $table->foreignId('traite_par')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('traite_le')->nullable();
                $table->timestamps();
            });
        }

        // Ajouter une colonne profil_verifie_at sur la table etudiants si non existante
        if (!Schema::hasColumn('etudiants', 'profil_verifie_at')) {
            Schema::table('etudiants', function (Blueprint $table) {
                $table->timestamp('profil_verifie_at')->nullable()->after('statut');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('etudiant_profile_requests');
        if (Schema::hasColumn('etudiants', 'profil_verifie_at')) {
            Schema::table('etudiants', function (Blueprint $table) {
                $table->dropColumn('profil_verifie_at');
            });
        }
    }
};
