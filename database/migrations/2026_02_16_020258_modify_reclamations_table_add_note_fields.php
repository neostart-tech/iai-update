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
        Schema::table('reclamations', function (Blueprint $table) {
            // Ajouter les nouveaux champs
            $table->foreignId('note_id')
                  ->nullable()
                  ->after('evaluation_id')
                  ->constrained('notes')
                  ->onDelete('cascade');
            
            $table->foreignId('traitee_par')
                  ->nullable()
                  ->after('commentaire_admin')
                  ->constrained('users')
                  ->onDelete('set null');
            
            $table->timestamp('traitee_le')
                  ->nullable()
                  ->after('traitee_par');
            
            $table->decimal('nouvelle_note', 5, 2)
                  ->nullable()
                  ->after('commentaire_admin');
            
            $table->softDeletes(); // Ajoute deleted_at

            // Ajouter des index pour les performances
            $table->index(['etudiant_id', 'statut']);
            $table->index('evaluation_id');
            $table->index('note_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reclamations', function (Blueprint $table) {
            // Supprimer les index
            $table->dropIndex(['etudiant_id', 'statut']);
            $table->dropIndex(['evaluation_id']);
            $table->dropIndex(['note_id']);
            
            // Supprimer les clés étrangères
            $table->dropForeign(['note_id']);
            $table->dropForeign(['traitee_par']);
            
            // Supprimer les colonnes
            $table->dropColumn([
                'note_id',
                'traitee_par',
                'traitee_le',
                'nouvelle_note',
                'deleted_at'
            ]);
        });
    }
};