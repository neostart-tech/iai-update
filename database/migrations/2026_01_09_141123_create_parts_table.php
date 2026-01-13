<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_id')->constrained()->onDelete('cascade');
            $table->string('identifier', 10); // I, II, III, IV, V
            $table->string('title', 100);
            $table->enum('question_type', ['text', 'textarea', 'choice_single', 'choice_multiple']);
            $table->integer('order')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
            
            // Contrainte d'unicité : une évaluation ne peut pas avoir deux parties avec le même identifiant
            $table->unique(['evaluation_id', 'identifier']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parts');
    }
};
