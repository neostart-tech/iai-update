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
        Schema::table('candidatures', function (Blueprint $table) {
            if (!Schema::hasColumn('candidatures', 'niveau_id')) {
                $table->unsignedBigInteger('niveau_id')->after('etudiant_id');
                $table->foreign('niveau_id')->references('id')->on('niveaux')->nullOnDelete();
            }

            if (!Schema::hasColumn('candidatures', 'filiere_id')) {
                $table->unsignedBigInteger('filiere_id')->after('niveau_id');
                $table->foreign('filiere_id')->references('id')->on('filieres')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidatures', function (Blueprint $table) {
           Schema::table('candidatures', function (Blueprint $table) {
            if (Schema::hasColumn('candidatures', 'filiere_id')) {
                $table->dropForeign(['filiere_id']);
                $table->dropColumn('filiere_id');
            }

            if (Schema::hasColumn('candidatures', 'niveau_id')) {
                $table->dropForeign(['niveau_id']);
                $table->dropColumn('niveau_id');
            }
        });
        });
    }
};
