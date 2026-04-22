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
        Schema::table('frais_etudiants', function (Blueprint $table) {
            // Passer en string pour plus de flexibilité et éviter les erreurs ENUM
            $table->string('statut')->default('en_cours')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('frais_etudiants', function (Blueprint $table) {
            $table->enum('statut', ['en_cours', 'solde', 'en_retard'])->default('en_cours')->change();
        });
    }
};
