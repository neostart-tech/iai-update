<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Supprimer la permission de la base de données
        $permission = DB::table('permissions')
            ->where('slug', 'attribuer-groupe-candidature')
            ->first();

        if ($permission) {
            // Supprimer d'abord les associations dans permission_role (nom de table typique si Spatie est customisé)
            if (DB::getSchemaBuilder()->hasTable('permission_role')) {
                DB::table('permission_role')->where('permission_id', $permission->id)->delete();
            }
            if (DB::getSchemaBuilder()->hasTable('role_has_permissions')) {
                DB::table('role_has_permissions')->where('permission_id', $permission->id)->delete();
            }
            if (DB::getSchemaBuilder()->hasTable('model_has_permissions')) {
                DB::table('model_has_permissions')->where('permission_id', $permission->id)->delete();
            }
            
            // Supprimer la permission elle-même
            DB::table('permissions')->where('id', $permission->id)->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // On la recrée si on doit annuler la migration
        $exists = DB::table('permissions')
            ->where('slug', 'attribuer-groupe-candidature')
            ->exists();

        if (!$exists) {
            DB::table('permissions')->insert([
                'slug' => 'attribuer-groupe-candidature',
                'nom' => 'Attribuer un groupe/classe a des candidats',
                'description' => 'Attribuer un groupe/classe a des candidats',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
};
