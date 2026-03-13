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
        Schema::table('enseignant_presences', function (Blueprint $table) {
            $table->time('heure_arrivee')->nullable()->after('statut');
            $table->time('heure_depart')->nullable()->after('heure_arrivee');
            $table->time('heure_depart_reelle')->nullable()->after('heure_depart'); // Heure réelle de départ (si tronquée)
            $table->integer('duree_calculee_minutes')->nullable()->after('heure_depart_reelle'); // Durée effectivement comptée
            $table->integer('duree_reelle_minutes')->nullable()->after('duree_calculee_minutes'); // Durée réelle (si différente)
            $table->enum('type_pointage', ['arrivee', 'depart_complete', 'depart_tronque'])->default('arrivee')->after('duree_reelle_minutes');
            $table->timestamp('arrivee_enregistree_at')->nullable()->after('type_pointage');
            $table->timestamp('depart_enregistree_at')->nullable()->after('arrivee_enregistree_at');
            $table->boolean('est_termine')->default(false)->after('depart_enregistree_at');
            $table->json('meta_data')->nullable()->after('est_termine'); // Pour stocker des infos s
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enseignant_presences', function (Blueprint $table) {
            $table->dropColumn([
                'heure_arrivee',
                'heure_depart',
                'heure_depart_reelle',
                'duree_calculee_minutes',
                'duree_reelle_minutes',
                'type_pointage',
                'arrivee_enregistree_at',
                'depart_enregistree_at',
                'est_termine',
                'meta_data'
            ]);
        });
    }
};
