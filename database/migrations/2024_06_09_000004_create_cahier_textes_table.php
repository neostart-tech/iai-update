<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCahierTextesTable extends Migration
{
    public function up()
    {
        Schema::create('cahier_textes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('emploi_du_temps_id');
            $table->string('titre');
            $table->text('contenu');
            $table->string('piece_jointe')->nullable();
            $table->timestamps();

            $table->foreign('emploi_du_temps_id')->references('id')->on('emploi_du_temps')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cahier_textes');
    }
}
