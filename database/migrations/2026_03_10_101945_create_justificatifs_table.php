<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('justificatifs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('etudiant_id')->constrained();
            $table->foreignId('presence_id')->nullable()->constrained();
            $table->enum('type', [
                'certificat_medical',
                'mot_parental',
                'convocation',
                'evenement_familial',
                'autre'
            ]);
            $table->string('fichier_path');
            $table->text('description')->nullable();
            $table->enum('statut', ['en_attente', 'valide', 'refuse'])->default('en_attente');
            $table->foreignId('valide_par')->nullable()->constrained('users');
            $table->datetime('valide_le')->nullable();
            $table->text('motif_refus')->nullable();
            $table->date('date_debut_validite')->nullable();
            $table->date('date_fin_validite')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('justificatifs');
    }
};