<?php

namespace App\Http\Controllers;

use App\Models\Etudiant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ChangePasswordController extends Controller
{
    public function update(Request $request)
    {
        // 1. Validation
        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => [
                'required', 
                'string',
                'confirmed',
                Password::min(8)->mixedCase()->numbers()
            ],
        ]);

        // 2. Chercher dans la table users
        $user = User::where('email', auth()->user()->email)->first();
        $userType = 'admin';

        // 3. Si pas trouvé, chercher dans la table etudiants
        if (!$user) {
            $user = Etudiant::where('email',  auth()->user()->email)->first();
            $userType = 'etudiant';
        }

        if (!$user) {
            return response()->json([
                'message' => 'Utilisateur non trouvé.'
            ], 404);
        }

        // 5. Vérifier que l'ancien mot de passe est correct
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Le mot de passe actuel est incorrect.',
                'errors' => [
                    'current_password' => ['Le mot de passe actuel est incorrect.']
                ]
            ], 422);
        }

        // 6. Vérifier que le nouveau mot de passe est différent
        if (Hash::check($request->new_password, $user->password)) {
            return response()->json([
                'message' => 'Le nouveau mot de passe doit être différent de l\'ancien.',
                'errors' => [
                    'new_password' => ['Le nouveau mot de passe doit être différent de l\'ancien.']
                ]
            ], 422);
        }

        // 7. Mettre à jour le mot de passe
        $user->password = Hash::make($request->new_password);
        $user->must_change_password = false;
        $user->save();

        return response()->json([
            'message' => 'Mot de passe modifié avec succès.',
            'success' => true,
        ], 200);
    }
}
