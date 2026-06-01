<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('syllabuses', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->foreignId('unite_valeur_id')->constrained('unite_valeurs')->onDelete('cascade');
            $table->text('description')->nullable();
            $table->text('objectifs')->nullable();
            $table->text('competences')->nullable();
            $table->longText('plan_cours')->nullable();
            $table->text('evaluation')->nullable();
            $table->text('ressources')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('syllabuses');
    }
};
