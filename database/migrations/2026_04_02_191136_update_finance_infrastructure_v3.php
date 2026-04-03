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
        // 1. Mise à jour des paiements
        Schema::table('paiements', function (Blueprint $table) {
            if (!Schema::hasColumn('paiements', 'nature_paiement')) {
                $table->string('nature_paiement')->default('scolarite')->after('montant');
            }
            
            // On modifie mode_paiement pour être plus flexible (string au lieu d'enum restreint)
            $table->string('mode_paiement')->default('especes')->change();
            
            if (!Schema::hasColumn('paiements', 'frais_retrait_mm')) {
                $table->decimal('frais_retrait_mm', 12, 2)->default(0)->after('mode_paiement');
            }
            
            if (!Schema::hasColumn('paiements', 'commentaire')) {
                $table->text('commentaire')->nullable()->after('frais_retrait_mm');
            }
        });

        // 2. Mise à jour du suivi de l'étudiant
        Schema::table('etudiant_group', function (Blueprint $table) {
            if (!Schema::hasColumn('etudiant_group', 'statut_scolaire')) {
                $table->string('statut_scolaire')->default('actif')->after('annee_scolaire_id');
            }
        });

        // 3. Mise à jour de la table financière par étudiant
        Schema::table('frais_etudiants', function (Blueprint $table) {
            if (!Schema::hasColumn('frais_etudiants', 'est_en_abandon')) {
                $table->boolean('est_en_abandon')->default(false)->after('statut');
            }
            if (!Schema::hasColumn('frais_etudiants', 'date_abandon')) {
                $table->date('date_abandon')->nullable()->after('est_en_abandon');
            }
            if (!Schema::hasColumn('frais_etudiants', 'montant_inscription_du')) {
                $table->decimal('montant_inscription_du', 12, 0)->nullable()->after('date_abandon');
            }
            if (!Schema::hasColumn('frais_etudiants', 'montant_scolarite_du')) {
                $table->decimal('montant_scolarite_du', 12, 0)->nullable()->after('montant_inscription_du');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paiements', function (Blueprint $table) {
             // Revenir à l'enum d'origine si nécessaire, mais on va simplement laisser tel quel ou dropper si on veut être strict.
             // On ne droppe pas mode_paiement car il existait.
        });
    }
};
