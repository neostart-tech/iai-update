<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('presences', function (Blueprint $table) {
            // D'abord modifier le statut existant pour accepter plus de valeurs
            DB::statement("ALTER TABLE presences MODIFY COLUMN statut ENUM(
                'present',
                'absent', 
                'retard',
                'retard_justifie',
                'absent_justifie',
                'dispense',
                'exclu_temporairement',
                'malade',
                'sortie_anticipee'
            ) DEFAULT 'present'");
            
            // Ajouter les nouveaux champs
            $table->time('heure_depart')->nullable()->after('heure_arrivee');
            $table->integer('minutes_retard')->nullable()->after('heure_arrivee');
        });
    }

    public function down()
    {
        Schema::table('presences', function (Blueprint $table) {
            // Revenir à l'ancien statut
            DB::statement("ALTER TABLE presences MODIFY COLUMN statut ENUM('present','absent','retard','justifie') DEFAULT 'present'");
            
            $table->dropColumn(['heure_depart', 'minutes_retard']);
        });
    }
};