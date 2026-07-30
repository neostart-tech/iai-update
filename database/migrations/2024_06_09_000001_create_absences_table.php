<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAbsencesTable extends Migration
{
    public function up()
    {
        Schema::create('absences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('etudiant_id');
            $table->unsignedBigInteger('cours_id');
            $table->date('date_absence');
            $table->string('motif')->nullable();
            $table->timestamps();

            $table->foreign('etudiant_id')->references('id')->on('etudiants')->onDelete('cascade');
            // Pas de contrainte FK vers `cours` : cette table a été abandonnée tôt dans le
            // projet (absente de la base de référence escendb) et n'est plus jamais créée.
        });
    }

    public function down()
    {
        Schema::dropIfExists('absences');
    }
}
