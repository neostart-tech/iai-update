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
        Schema::create('plan_tranches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_paiement_id')->constrained()->cascadeOnDelete();
            $table->integer('ordre');
            $table->decimal('pourcentage', 5, 2);
            $table->integer('mois_apres_debut');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_tranches');
    }
};
