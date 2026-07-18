<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoursTable extends Migration
{
    public function up()
    {
        Schema::create('cours', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->unsignedBigInteger('groupe_id');
            $table->unsignedBigInteger('uv_id');
            $table->date('date_cours');
            $table->timestamps();

            // Pas de contrainte FK vers `groupes`/`uvs` : ces tables n'ont jamais existé
            // dans le schéma actuel (absentes de la base de référence escendb).
        });
    }

    public function down()
    {
        Schema::dropIfExists('cours');
    }
}
