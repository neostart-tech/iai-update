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
        if (Schema::hasColumn('frais_inscriptions', 'niveau_id')) {
            Schema::table('frais_inscriptions', function (Blueprint $table) {
                $table->dropColumn('niveau_id');
            });
        }
        if (Schema::hasColumn('frais_inscriptions', 'filiere_id')) {
            Schema::table('frais_inscriptions', function (Blueprint $table) {
                $table->dropColumn('filiere_id');
            });
        }

        Schema::table('frais_inscriptions', function (Blueprint $table) {
            $table->foreignId('niveau_id')->nullable()->constrained('niveaux')->nullOnDelete();
            $table->foreignId('filiere_id')->nullable()->constrained('filieres')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('frais_inscriptions', function (Blueprint $table) {
            $table->dropForeign(['niveau_id']);
            $table->dropForeign(['filiere_id']);
            $table->dropColumn(['niveau_id', 'filiere_id']);
        });
    }
};
