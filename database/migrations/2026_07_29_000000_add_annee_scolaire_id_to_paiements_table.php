<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('paiements', 'annee_scolaire_id')) {
            Schema::table('paiements', function (Blueprint $table) {
                $table->unsignedBigInteger('annee_scolaire_id')->nullable()->after('etudiant_id');
                
                // On peut ajouter la contrainte de clé étrangère
                $table->foreign('annee_scolaire_id')
                      ->references('id')
                      ->on('annee_scolaires')
                      ->onDelete('cascade');
            });
        }

        // Script de rétrocompatibilité : Remplir l'année scolaire pour les paiements existants
        // On cherche à récupérer l'année scolaire depuis l'étudiant ou le payable
        $paiements = DB::table('paiements')->get();
        foreach ($paiements as $paiement) {
            $anneeId = null;
            
            // 1. Essayer de récupérer depuis FraisInscription (payable_type = FraisInscription)
            if ($paiement->payable_type === 'App\Models\FraisInscription' || $paiement->nature_paiement === 'inscription') {
                if ($paiement->payable_id) {
                    $frais = DB::table('frais_inscriptions')->where('id', $paiement->payable_id)->first();
                    if ($frais) {
                        $anneeId = $frais->annee_scolaire_id;
                    }
                }
            }
            // 2. Essayer de récupérer depuis TranchePaiement
            elseif ($paiement->payable_type === 'App\Models\Echeance') {
                $echeance = DB::table('echeances')->where('id', $paiement->payable_id)->first();
                if ($echeance) {
                    $fraisEtudiant = DB::table('frais_etudiants')->where('id', $echeance->frais_etudiant_id)->first();
                    if ($fraisEtudiant) {
                        $anneeId = $fraisEtudiant->annee_scolaire_id;
                    }
                }
            }
            elseif ($paiement->payable_type === 'App\Models\TranchePaiement') {
                $tranche = DB::table('tranche_paiements')->where('id', $paiement->payable_id)->first();
                if ($tranche) {
                    $anneeId = $tranche->annee_scolaire_id;
                }
            }
            
            // 3. Fallback : Prendre l'année active par défaut ou la dernière année de l'étudiant
            if (!$anneeId && $paiement->etudiant_id) {
                $query = DB::table('etudiant_group')
                    ->where('etudiant_id', $paiement->etudiant_id);

                if (Schema::hasColumn('etudiant_group', 'created_at')) {
                    $query->orderBy('created_at', 'desc');
                } elseif (Schema::hasColumn('etudiant_group', 'id')) {
                    $query->orderBy('id', 'desc');
                }

                $group = $query->first();
                
                if ($group) {
                    $anneeId = $group->annee_scolaire_id;
                }
            }
            
            // 4. Dernier recours : l'année active globale
            if (!$anneeId) {
                $anneeActive = DB::table('annee_scolaires')->where('active', true)->first();
                if ($anneeActive) {
                    $anneeId = $anneeActive->id;
                }
            }

            if ($anneeId) {
                DB::table('paiements')
                    ->where('id', $paiement->id)
                    ->update(['annee_scolaire_id' => $anneeId]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paiements', function (Blueprint $table) {
            $table->dropForeign(['annee_scolaire_id']);
            $table->dropColumn('annee_scolaire_id');
        });
    }
};
