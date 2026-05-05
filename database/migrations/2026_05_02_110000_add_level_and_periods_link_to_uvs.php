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
        // Ajout du niveau aux UV
        Schema::table('unite_valeurs', function (Blueprint $blueprint) {
            $blueprint->foreignId('niveau_id')->nullable()->constrained('niveaux')->onDelete('set null');
        });

        // Table pivot niveau_periode
        Schema::create('niveau_periode', function (Blueprint $table) {
            $table->id();
            $table->foreignId('niveau_id')->constrained('niveaux')->onDelete('cascade');
            $table->foreignId('periode_id')->constrained('periodes')->onDelete('cascade');
            $table->unique(['niveau_id', 'periode_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('niveau_periode');
        
        Schema::table('unite_valeurs', function (Blueprint $table) {
            $table->dropForeign(['niveau_id']);
            $table->dropColumn('niveau_id');
        });
    }
};
