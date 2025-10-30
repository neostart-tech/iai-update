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
        Schema::create('reclamations', function (Blueprint $table) {
              $table->id();
     $table->foreignId('etudiant_id')->constrained('etudiants')->onDelete('cascade');
    $table->foreignId('evaluation_id')->nullable()->constrained('evaluations')->onDelete('cascade');
    $table->text('motif');
    $table->string('fichier_justificatif')->nullable();
    $table->enum('statut', ['en_attente', 'approuvee', 'rejetee'])->default('en_attente');
    $table->text('commentaire_admin')->nullable();
    $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reclamations');
    }
};
