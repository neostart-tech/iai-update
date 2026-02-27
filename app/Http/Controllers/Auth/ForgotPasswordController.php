<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordMail;
use App\Models\Etudiant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
      public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // Vérifier dans users et etudiants
        $user = User::where('email', $request->email)->first();
        $etudiant = Etudiant::where('email', $request->email)->first();

        if (!$user && !$etudiant) {
            return response()->json([
                'status' => false,
                'message' => 'Aucun compte trouvé avec cette adresse email.'
            ], 404);
        }

        $tableUser = $user ?? $etudiant; // choisir le bon modèle

        // Générer token
        $token = Str::random(64);

        // Stocker token dans password_resets
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => Hash::make($token),
                'created_at' => Carbon::now()
            ]
        );

        // Envoyer mail
        Mail::to($request->email)->send(new ResetPasswordMail($token, $request->email));

        return response()->json([
            'status' => true,
            'message' => 'Lien de réinitialisation envoyé.'
        ], 200);
    }

    // Réinitialiser mot de passe
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (!$record || !Hash::check($request->token, $record->token)) {
            return response()->json([
                'status' => false,
                'message' => 'Token invalide ou expiré.'
            ], 400);
        }

        // Mettre à jour le mot de passe dans users ou etudiants
        $user = User::where('email', $request->email)->first();
        $etudiant = Etudiant::where('email', $request->email)->first();

        if ($user) {
            $user->password = Hash::make($request->password);
            $user->save();
        } elseif ($etudiant) {
            $etudiant->password = Hash::make($request->password);
            $etudiant->save();
        }

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json([
            'status' => true,
            'message' => 'Mot de passe réinitialisé avec succès.'
        ], 200);
    }
}
