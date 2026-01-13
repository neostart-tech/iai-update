<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evaluation_case_study_contexts', function (Blueprint $table) {
            // 1. Supprimer evaluation_id (ancienne relation)
            $table->dropForeign(['evaluation_id']);
            $table->dropColumn('evaluation_id');
            
            // 2. Ajouter part_id (nouvelle relation 1:1 avec partie)
            $table->foreignId('part_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('parts')
                  ->onDelete('cascade');
            
            // Ajouter une contrainte d'unicité : une partie = un contexte max
            $table->unique('part_id');
        });
    }

    public function down(): void
    {
        Schema::table('evaluation_case_study_contexts', function (Blueprint $table) {
            // Retirer l'unicité
            $table->dropUnique(['part_id']);
            
            // Retirer part_id
            $table->dropForeign(['part_id']);
            $table->dropColumn('part_id');
            
            // Rétablir evaluation_id
            $table->foreignId('evaluation_id')
                  ->nullable()
                  ->constrained('evaluations')
                  ->onDelete('cascade');
        });
    }
};