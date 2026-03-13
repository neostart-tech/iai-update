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
        Schema::table('enseignant_presences', function (Blueprint $table) {
            $table->date('date_cours')->nullable()->after('emploi_du_temps_id');

            $table->time('heure_debut_prevue')->nullable()->after('date_cours');
            $table->time('heure_fin_prevue')->nullable()->after('heure_debut_prevue');

            $table->unique(['emploi_du_temps_id', 'date_cours'], 'unique_presence_par_jour');
            $table->index('date_cours');
            $table->index(['enseignant_id', 'date_cours']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enseignant_presences', function (Blueprint $table) {
              $table->dropUnique('unique_presence_par_jour');
            $table->dropIndex(['date_cours']);
            $table->dropIndex(['enseignant_id', 'date_cours']);
            $table->dropColumn(['date_cours', 'heure_debut_prevue', 'heure_fin_prevue']);
        });
    }
};
