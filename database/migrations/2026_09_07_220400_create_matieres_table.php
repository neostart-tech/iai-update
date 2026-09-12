<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('matieres', function (Blueprint $table) {
            $table->engine = 'InnoDB'; // Add this explicitly to avoid MyISAM default on some MySQL setups
            $table->id();
            $table->string('nom');
            $table->string('code')->nullable();
            $table->string('slug')->unique();
        });

        // 2. Ajouter matiere_id à unite_valeurs (nullable au début pour permettre l'insertion)
        Schema::table('unite_valeurs', function (Blueprint $table) {
            $table->foreignId('matiere_id')->nullable()->constrained('matieres')->onDelete('cascade');
        });

        // 3. Migration des données (Script de transfert)
        // On récupère toutes les UVs avec leurs noms et codes
        $uvs = DB::table('unite_valeurs')->select('id', 'nom', 'code', 'slug')->get();
        
        $matieresCache = [];

        foreach ($uvs as $uv) {
            // Clé unique pour ne pas recréer la même matière
            $key = strtolower(trim($uv->nom));
            
            if (!isset($matieresCache[$key])) {
                $baseSlug = Str::slug($uv->nom);
                $slug = $baseSlug;
                $i = 1;
                while (DB::table('matieres')->where('slug', $slug)->exists()) {
                    $slug = $baseSlug . '-' . $i;
                    $i++;
                }
                $matiereId = DB::table('matieres')->insertGetId([
                    'nom' => $uv->nom,
                    'code' => $uv->code,
                    'slug' => $slug,
                ]);
                $matieresCache[$key] = $matiereId;
            }

            // On met à jour l'UV avec l'ID de la matière
            DB::table('unite_valeurs')
                ->where('id', $uv->id)
                ->update(['matiere_id' => $matieresCache[$key]]);
        }

        // 4. On rend matiere_id obligatoire et on supprime les anciennes colonnes
        Schema::table('unite_valeurs', function (Blueprint $table) {
            $table->unsignedBigInteger('matiere_id')->nullable(false)->change();
            
            // On supprime les anciennes colonnes devenues redondantes
            $table->dropColumn(['nom', 'code', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. On remet les colonnes dans unite_valeurs
        Schema::table('unite_valeurs', function (Blueprint $table) {
            $table->string('nom')->nullable();
            $table->string('code')->nullable();
            $table->string('slug')->nullable();
        });

        // 2. On restaure les données depuis matieres
        $uvs = DB::table('unite_valeurs')->join('matieres', 'unite_valeurs.matiere_id', '=', 'matieres.id')
                 ->select('unite_valeurs.id', 'matieres.nom', 'matieres.code')
                 ->get();

        foreach ($uvs as $uv) {
            DB::table('unite_valeurs')
                ->where('id', $uv->id)
                ->update([
                    'nom' => $uv->nom,
                    'code' => $uv->code,
                    'slug' => Str::slug($uv->nom) . '-' . uniqid()
                ]);
        }

        // 3. On supprime la colonne matiere_id et la table matieres
        Schema::table('unite_valeurs', function (Blueprint $table) {
            $table->dropForeign(['matiere_id']);
            $table->dropColumn('matiere_id');
        });

        Schema::dropIfExists('matieres');
    }
};
