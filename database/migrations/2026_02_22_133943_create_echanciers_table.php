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
        Schema::create('echanciers', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->foreignId('etudiant_id')->constrained()->cascadeOnDelete();
            $table->decimal('remise_exceptionnelle', 10, 2)->default(0);
            $table->foreignId('frais_scolarite_id')->constrained();
            $table->foreignId('plan_paiement_id')->nullable()->constrained();
            $table->decimal('montant_total', 10, 2);
            $table->decimal('montant_paye', 10, 2)->default(0);
            $table->decimal('reste_a_payer', 10, 2)->default(0);
            $table->boolean('est_solde')->default(false);
            $table->timestamps();

            $table->unique(['etudiant_id', 'frais_scolarite_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('echanciers');
    }
};
