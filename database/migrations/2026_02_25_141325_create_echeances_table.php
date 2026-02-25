<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('echeances', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->foreignId('echeancier_id')->constrained()->onDelete('cascade');
            $table->foreignId('frais_etudiant_id')->constrained()->onDelete('cascade');
            $table->string('libelle');
            $table->decimal('montant', 12, 0);
            $table->decimal('montant_paye', 12, 0)->default(0);
            $table->date('date_limite');
            $table->integer('ordre')->default(0);
            $table->enum('statut', ['en_attente', 'partiel', 'paye', 'en_retard'])->default('en_attente');
            $table->timestamps();
            
            // Index pour les recherches fréquentes
            $table->index(['frais_etudiant_id', 'statut']);
            $table->index(['date_limite', 'statut']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('echeances');
    }
};