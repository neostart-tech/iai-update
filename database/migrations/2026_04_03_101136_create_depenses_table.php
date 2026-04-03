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
        Schema::create('depenses', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->decimal('montant', 12, 0);
            $table->date('date_depense');
            $table->string('categorie')->nullable();
            $table->text('description')->nullable();
            $table->string('reference')->nullable();
            $table->string('mode_paiement')->default('especes');
            $table->foreignId('user_id')->constrained();
            $table->foreignId('annee_scolaire_id')->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('depenses');
    }
};
