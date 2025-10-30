<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tranche_paiements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('annee_scolaire_id');
            $table->string('libelle');
            $table->integer('montant');
            $table->date('date_limite');
            $table->timestamps();

            $table->foreign('annee_scolaire_id')->references('id')->on('annee_scolaires')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tranche_paiements');
    }
};