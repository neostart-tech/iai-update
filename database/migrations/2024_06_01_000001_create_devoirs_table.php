<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDevoirsTable extends Migration
{
    public function up()
    {
        Schema::create('devoirs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('emploi_du_temps_id');
            $table->string('titre');
            $table->text('consignes');
            $table->string('fichier')->nullable();
            $table->date('date_limite');
            $table->text('correction')->nullable();
            $table->timestamps();

            $table->foreign('emploi_du_temps_id')->references('id')->on('emploi_du_temps')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('devoirs');
    }
}
