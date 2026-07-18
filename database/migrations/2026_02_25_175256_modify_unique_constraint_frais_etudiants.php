<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Créer la nouvelle contrainte incluant frais_scolarite_id d'abord : la contrainte
        // FK sur etudiant_id s'appuie sur unique_frais_etudiant_annee (aucun autre index ne
        // couvre cette colonne seule), donc la supprimer avant d'en recréer une équivalente
        // échoue avec "Cannot drop index ... needed in a foreign key constraint".
        Schema::table('frais_etudiants', function (Blueprint $table) {
            $table->unique(['etudiant_id', 'annee_scolaire_id', 'frais_scolarite_id'], 'unique_frais_etudiant_annee_frais');
        });

        // Supprimer l'ancienne contrainte
        Schema::table('frais_etudiants', function (Blueprint $table) {
            $table->dropUnique('unique_frais_etudiant_annee');
        });
    }

    public function down()
    {
        // Restaurer l'ancienne contrainte d'abord, même raison que dans up().
        Schema::table('frais_etudiants', function (Blueprint $table) {
            $table->unique(['etudiant_id', 'annee_scolaire_id'], 'unique_frais_etudiant_annee');
        });

        Schema::table('frais_etudiants', function (Blueprint $table) {
            $table->dropUnique('unique_frais_etudiant_annee_frais');
        });
    }
};