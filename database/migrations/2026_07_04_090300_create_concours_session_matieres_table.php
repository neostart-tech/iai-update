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
        Schema::create('concours_session_matieres', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('concours_session_id')->constrained('concours_sessions')->cascadeOnDelete();
            $table->foreignId('concours_matiere_id')->constrained('concours_matieres')->cascadeOnDelete();
            $table->foreignId('niveau_id')->constrained('niveaux')->cascadeOnDelete();
            $table->foreignId('filiere_id')->nullable()->constrained('filieres')->nullOnDelete();
            $table->decimal('coefficient', 5, 2)->default(1);
            $table->timestamps();

            $table->unique(['concours_session_id', 'concours_matiere_id', 'niveau_id', 'filiere_id'], 'concours_session_matiere_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('concours_session_matieres');
    }
};
