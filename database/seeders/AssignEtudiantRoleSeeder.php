<?php

namespace Database\Seeders;

use App\Models\Etudiant;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AssignEtudiantRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roleEtudiant = Role::where('nom', 'Etudiant')->first();

        if ($roleEtudiant) {
            Etudiant::all()->each(function ($etudiant) use ($roleEtudiant) {
                if (!$etudiant->roles()->where('role_id', $roleEtudiant->id)->exists()) {
                    $etudiant->roles()->attach($roleEtudiant->id);
                }
            });
        }
    }
}
