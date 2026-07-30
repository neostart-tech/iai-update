<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('type_diplome_champs', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('type_diplome_id')->constrained('type_diplomes')->cascadeOnDelete();
            // Ensemble fixe : mention_bac, serie, numero_table, etablissement_diplome, annee_bac
            $table->string('champ_key');
            $table->boolean('obligatoire')->default(false);
            $table->timestamps();

            $table->unique(['type_diplome_id', 'champ_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('type_diplome_champs');
    }
};
