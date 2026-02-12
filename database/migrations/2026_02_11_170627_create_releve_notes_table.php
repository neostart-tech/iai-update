<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('releve_notes', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->foreignId('etudiant_id')->constrained()->onDelete('cascade');
            $table->foreignId('annee_scolaire_id')->constrained();
            $table->foreignId('periode_id')->constrained();
            $table->decimal('moyenne_generale', 5, 2);
            $table->integer('total_credits_valides');
            $table->integer('total_credits_non_valides');
            $table->json('metadata')->nullable();
            $table->timestamp('calcule_le')->useCurrent();
            $table->timestamps();

            $table->unique(['etudiant_id', 'annee_scolaire_id', 'periode_id'], 'releve_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('releve_notes');
    }
};
