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
        Schema::create('reorientations', function (Blueprint $table) {
             $table->id();

            $table->unsignedBigInteger('candidature_id');

            $table->unsignedBigInteger('filiere_id');
            $table->unsignedBigInteger('niveau_id');
            $table->unsignedBigInteger('annee_scolaire_id');

            $table->text('motif')->nullable();

            $table->timestamps();

          
            $table->foreign('candidature_id')->references('id')->on('candidatures')->onDelete('cascade');
            $table->foreign('filiere_id')->references('id')->on('filieres')->onDelete('cascade');
            $table->foreign('niveau_id')->references('id')->on('niveaux')->onDelete('cascade');
             $table->foreign('annee_scolaire_id')->references('id')->on('annee_scolaires')->onDelete('cascade');

        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reorientations');
    }
};
