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
        Schema::table('frais_inscriptions', function (Blueprint $table) {
            $table->unsignedBigInteger('annee_scolaire_id')->nullable()->after('id');
            $table->foreign('annee_scolaire_id')->references('id')->on('annee_scolaires')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('frais_inscriptions', function (Blueprint $table) {
            $table->dropForeign(['annee_scolaire_id']);
            $table->dropColumn('annee_scolaire_id');
        });
    }
};
