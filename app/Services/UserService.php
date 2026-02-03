<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * Créer un user + rôle optionnel
     */
    public function createUser(array $data, ?int $roleId = null): int
    {
        return DB::transaction(function () use ($data, $roleId) {

            // 1. Création du user
            $userId = DB::table('users')->insertGetId([
                'nom'        => $data['nom'],
                'prenom'     => $data['prenom'],
                'email'      => $data['email'] ?? null,
                'password'   => Hash::make($data['password'] ?? 'password'),
                'genre'      => $data['genre'] ?? 'Féminin',
                'matricule'  => $data['matricule'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Attribution du rôle (OPTIONNEL)
            if ($roleId) {
                DB::table('role_user')->insert([
                    'user_id'   => $userId,
                    'user_type' => 'App\\Models\\User',
                    'role_id'   => $roleId,
                ]);
            }

            return $userId;
        });
    }
}
