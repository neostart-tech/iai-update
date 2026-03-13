<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('presences', function (Blueprint $table) {
            // Ajouter seance_id (nullable d'abord)
            $table->foreignId('seance_id')->nullable()->after('emploi_du_temps_id')->constrained();
            
            // Supprimer l'ancienne contrainte unique
            // $table->dropUnique(['emploi_du_temps_id', 'etudiant_id', 'date']);
            
            // Ajouter la nouvelle contrainte unique
            $table->unique(['seance_id', 'etudiant_id'], 'unique_presence_par_seance_etudiant');
        });
    }

    public function down()
    {
        Schema::table('presences', function (Blueprint $table) {
            // Restaurer l'ancienne contrainte
            $table->unique(['emploi_du_temps_id', 'etudiant_id', 'date']);
            
            // Supprimer la nouvelle
            $table->dropUnique('unique_presence_par_seance_etudiant');
            
            // Supprimer seance_id
            $table->dropForeign(['seance_id']);
            $table->dropColumn('seance_id');
        });
    }
};