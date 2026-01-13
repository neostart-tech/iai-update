<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('evaluation_questions', function (Blueprint $table) {
            // Ajouter la colonne part_id
            $table->foreignId('part_id')->nullable()->after('evaluation_id')
                  ->constrained()->nullOnDelete();
            
            // Supprimer les anciennes colonnes (optionnel - à faire après migration des données)
            $table->dropColumn(['part', 'part_title']);
           
        });
    }

    public function down()
    {
        Schema::table('evaluation_questions', function (Blueprint $table) {
            $table->dropForeign(['part_id']);
            $table->dropColumn(['part_id']);
            
            // Recréer les anciennes colonnes si nécessaire
            $table->string('part', 10)->nullable();
            $table->string('part_title', 100)->nullable();
        });
    }
};