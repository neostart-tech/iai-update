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
        Schema::create('plans_paiement', function (Blueprint $table) {
            $table->id();
            $table->string('nom'); 
            $table->string('slug');
            $table->integer('nombre_tranches')->nullable();
            $table->boolean('est_personnalise')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans_paiement');
    }
};
