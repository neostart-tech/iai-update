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
        Schema::create('bourses', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->string('nom');
            $table->enum('type', ['pourcentage', 'montant_fixe']);
            $table->decimal('valeur', 10, 2);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bourses');
    }
};
