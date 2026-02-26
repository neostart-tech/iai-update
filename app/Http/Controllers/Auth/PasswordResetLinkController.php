<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
	public function create(): View
	{
		return view('auth.forgot-password');
	}

	// public function store(Request $request): RedirectResponse
	// {
	// 	$request->validate([
	// 		'email' => ['required', 'email'],
	// 	], [
	// 		'email.required' => 'Votre adresse email est obligatoire',
	// 		'email.email' => 'Votre adresse email n\'est pas une adresse mail valide'
	// 	]);

	// 	$status = Password::sendResetLink($request->only('email'));

	// 	return $status == Password::RESET_LINK_SENT
	// 		? back()->with('status', __($status))
	// 		: back()->withInput($request->only('email'))
	// 			->withErrors(['email' => __($status)]);
	// }



	 public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Aucun utilisateur trouvé avec cette adresse email.'
            ], 404);
        }

        // Générer token aléatoire
        $token = Str::random(64);

        // Stocker dans password_resets
        DB::table('password_resets')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => Hash::make($token),
                'created_at' => Carbon::now()
            ]
        );

        // Envoyer le mail avec lien
        Mail::to($request->email)->send(new ResetPasswordMail($token, $request->email));

        return response()->json([
            'status' => true,
            'message' => 'Lien de réinitialisation envoyé.'
        ], 200);
    }

    // Réinitialiser le mot de passe
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        $record = DB::table('password_resets')->where('email', $request->email)->first();

        if (!$record) {
            return response()->json([
                'status' => false,
                'message' => 'Lien invalide.'
            ], 400);
        }

        // Vérifier le token
        if (!Hash::check($request->token, $record->token)) {
            return response()->json([
                'status' => false,
                'message' => 'Token invalide ou expiré.'
            ], 400);
        }

        // Mettre à jour le mot de passe
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Supprimer le token
        DB::table('password_resets')->where('email', $request->email)->delete();

        return response()->json([
            'status' => true,
            'message' => 'Mot de passe réinitialisé avec succès.'
        ], 200);
    }
}
