<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('seances', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('emploi_du_temps_id')->constrained()->onDelete('cascade');
            $table->date('date_seance');
            $table->time('heure_debut_prevue')->nullable();
            $table->time('heure_fin_prevue')->nullable();
            $table->time('heure_debut_reelle')->nullable();
            $table->time('heure_fin_reelle')->nullable();
            $table->enum('statut', [
                'planifie',
                'en_cours',
                'termine',
                'annule',
                'reporte',
                'rattrapage'
            ])->default('planifie');
            $table->string('motif_annulation')->nullable();
            $table->foreignId('remplacant_id')->nullable()->constrained('users');
            $table->foreignId('salle_reelle_id')->nullable()->constrained('salles');
            $table->text('notes_seance')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->unique(['emploi_du_temps_id', 'date_seance'], 'unique_seance_par_cours_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('seances');
    }
};