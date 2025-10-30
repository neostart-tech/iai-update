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
        Schema::table('frais_scolarites', function (Blueprint $table) {
            $table->enum('genre', ['Masculin', 'Féminin', 'Tous'])->default('Tous')->after('montant');
            $table->text('description')->nullable()->after('genre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('frais_scolarites', function (Blueprint $table) {
            $table->dropColumn(['genre', 'description']);
        });
    }
};