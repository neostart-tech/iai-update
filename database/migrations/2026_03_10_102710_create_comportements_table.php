<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('comportements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('etudiant_id')->constrained();
            $table->foreignId('presence_id')->nullable()->constrained();
            $table->foreignId('user_id')->constrained(); // Qui a noté
            $table->enum('type', ['positif', 'negatif', 'neutre', 'alerte']);
            $table->string('categorie'); // participation, attitude, discipline, travail, etc.
            $table->string('libelle'); // "Bavardage", "Aide un camarade", "Téléphone", etc.
            $table->text('description')->nullable();
            $table->integer('intensite')->default(1); // 1-5
            $table->boolean('a_communiquer_parents')->default(false);
            $table->boolean('a_remonter_conseil')->default(false);
            $table->datetime('traite_le')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('comportements');
    }
};