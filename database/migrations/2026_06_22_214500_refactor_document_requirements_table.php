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
        // 1. Ajouter la nouvelle colonne (nullable au début pour permettre l'insertion)
        Schema::table('document_requirements', function (Blueprint $table) {
            $table->foreignId('document_type_id')->nullable()->constrained('document_types')->cascadeOnDelete();
        });

        // 2. Migrer les données existantes
        $requirements = DB::table('document_requirements')->get();
        foreach ($requirements as $req) {
            $type = DB::table('document_types')->where('document_key', $req->document_key)->first();
            if (!$type) {
                // S'il n'y a pas encore de document_type avec cette clé, on le crée
                $typeId = DB::table('document_types')->insertGetId([
                    'nom_affichage' => $req->nom_affichage,
                    'document_key' => $req->document_key,
                    'is_multiple' => $req->is_multiple ?? false,
                    'is_photo' => false, // Par défaut false
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $typeId = $type->id;
            }

            // On met à jour l'enregistrement
            DB::table('document_requirements')->where('id', $req->id)->update([
                'document_type_id' => $typeId
            ]);
        }

        // 3. Supprimer les anciennes colonnes
        Schema::table('document_requirements', function (Blueprint $table) {
            $table->dropColumn(['nom_affichage', 'document_key', 'is_multiple']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_requirements', function (Blueprint $table) {
            $table->string('nom_affichage')->nullable();
            $table->string('document_key')->nullable();
            $table->boolean('is_multiple')->default(false);
        });

        $requirements = DB::table('document_requirements')->get();
        foreach ($requirements as $req) {
            if ($req->document_type_id) {
                $type = DB::table('document_types')->where('id', $req->document_type_id)->first();
                if ($type) {
                    DB::table('document_requirements')->where('id', $req->id)->update([
                        'nom_affichage' => $type->nom_affichage,
                        'document_key' => $type->document_key,
                        'is_multiple' => $type->is_multiple,
                    ]);
                }
            }
        }

        Schema::table('document_requirements', function (Blueprint $table) {
            $table->dropForeign(['document_type_id']);
            $table->dropColumn('document_type_id');
        });
    }
};
