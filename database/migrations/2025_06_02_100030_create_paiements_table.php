<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paiements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('etudiant_id');
          
            $table->unsignedBigInteger('tranche_paiement_id')->nullable();
            $table->integer('montant');
            $table->enum('mode_paiement', ['banque', 'semoa', 'caisse']);
            $table->string('reference')->nullable();
            $table->string('recu')->nullable();
            $table->date('date_paiement');
            $table->timestamps();

            $table->foreign('etudiant_id')->references('id')->on('etudiants')->onDelete('cascade');
            $table->foreign('tranche_paiement_id')->references('id')->on('tranche_paiements')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};