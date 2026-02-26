<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Supprimer l'ancienne contrainte
        Schema::table('frais_etudiants', function (Blueprint $table) {
            $table->dropUnique('unique_frais_etudiant_annee');
        });
        
        // Créer la nouvelle contrainte incluant frais_scolarite_id
        Schema::table('frais_etudiants', function (Blueprint $table) {
            $table->unique(['etudiant_id', 'annee_scolaire_id', 'frais_scolarite_id'], 'unique_frais_etudiant_annee_frais');
        });
    }

    public function down()
    {
        // Restaurer l'ancienne contrainte
        Schema::table('frais_etudiants', function (Blueprint $table) {
            $table->dropUnique('unique_frais_etudiant_annee_frais');
        });
        
        Schema::table('frais_etudiants', function (Blueprint $table) {
            $table->unique(['etudiant_id', 'annee_scolaire_id'], 'unique_frais_etudiant_annee');
        });
    }
};