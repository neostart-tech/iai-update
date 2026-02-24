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
        Schema::create('bourse_etudiants', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->foreignId('etudiant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bourse_id')->constrained()->cascadeOnDelete();
            $table->foreignId('annee_scolaire_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['etudiant_id', 'bourse_id','annee_scolaire_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bourse_etudiants');
    }
};
