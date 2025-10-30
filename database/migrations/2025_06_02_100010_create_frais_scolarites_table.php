<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('frais_scolarites', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('annee_scolaire_id');
            $table->unsignedBigInteger('niveau_id');
            $table->integer('montant');
            $table->timestamps();

            $table->foreign('annee_scolaire_id')->references('id')->on('annee_scolaires')->onDelete('cascade');
            $table->foreign('niveau_id')->references('id')->on('niveaux')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('frais_scolarites');
    }
};