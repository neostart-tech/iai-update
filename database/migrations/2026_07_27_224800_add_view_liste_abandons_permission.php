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
        $exists = DB::table('permissions')
            ->where('slug', 'view-liste-abandons')
            ->exists();

        if (!$exists) {
            $permissionId = DB::table('permissions')->insertGetId([
                'slug' => 'view-liste-abandons',
                'nom' => 'Voir la liste des abandons',
                'description' => 'Voir la liste des etudiants ayant abandonne',
            ]);

            // Assigner aux rôles importants
            $roles = DB::table('roles')->whereIn('slug', [
                'admin', 
                'directeur-general', 
                'directeur-academique',
                'directeur-general-adjoint',
                'responsable-administratif-et-financier',
                'directeur-des-affaires-financieres',
                'informaticien'
            ])->get();

            $permissionRoles = [];
            foreach ($roles as $role) {
                if (DB::getSchemaBuilder()->hasTable('permission_role')) {
                    $permissionRoles[] = [
                        'permission_id' => $permissionId,
                        'role_id' => $role->id,
                    ];
                }
            }

            if (!empty($permissionRoles) && DB::getSchemaBuilder()->hasTable('permission_role')) {
                DB::table('permission_role')->insert($permissionRoles);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permission = DB::table('permissions')
            ->where('slug', 'view-liste-abandons')
            ->first();

        if ($permission) {
            if (DB::getSchemaBuilder()->hasTable('permission_role')) {
                DB::table('permission_role')->where('permission_id', $permission->id)->delete();
            }
            DB::table('permissions')->where('id', $permission->id)->delete();
        }
    }
};
