<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('uv_validations', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->foreignId('etudiant_id')->constrained()->onDelete('cascade');
            $table->foreignId('unite_valeur_id')->constrained()->onDelete('cascade');
            $table->foreignId('annee_scolaire_id')->constrained();
            $table->foreignId('periode_id')->constrained();
            $table->decimal('moyenne', 5, 2);
            $table->decimal('note_devoir', 5, 2)->nullable();
            $table->decimal('note_examen', 5, 2)->nullable();
            $table->integer('coefficient');
            $table->integer('credit_obtenu')->nullable();
            $table->boolean('validee')->default(false);
            $table->timestamps();

            $table->unique(['etudiant_id', 'unite_valeur_id', 'annee_scolaire_id', 'periode_id'], 'uv_validation_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('uv_validations');
    }
};
