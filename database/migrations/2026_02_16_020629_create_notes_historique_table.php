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
        Schema::create('notes_historique', function (Blueprint $table) {
            $table->id();
            $table->foreignId('note_id')
                  ->constrained()
                  ->onDelete('cascade');
            $table->foreignId('reclamation_id')
                  ->constrained()
                  ->onDelete('cascade');
            $table->decimal('ancienne_note', 5, 2);
            $table->decimal('nouvelle_note', 5, 2);
            $table->foreignId('modifiee_par')
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->timestamps();
            
            // Index pour les recherches
            $table->index('note_id');
            $table->index('reclamation_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notes_historique');
    }
};