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
        Schema::create('echeances', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->foreignId('echeancier_id')->constrained()->cascadeOnDelete();
            $table->string('libelle');
            $table->decimal('montant', 10, 2);
            $table->decimal('montant_paye', 10, 2)->default(0); 
            $table->date('date_limite');
            $table->enum('statut', ['en_attente', 'partiel', 'paye', 'retard'])
                ->default('en_attente');
            $table->timestamps();

            $table->index(['echeancier_id', 'statut']);
            $table->index('date_limite');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('echeances');
    }
};
