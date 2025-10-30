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

            $table->foreign('groupe_id')->references('id')->on('groupes')->onDelete('cascade');
            $table->foreign('uv_id')->references('id')->on('uvs')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cours');
    }
}
