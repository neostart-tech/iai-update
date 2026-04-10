<?php

namespace App\Http\Controllers\EtudiantAuth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\EtudiantLoginRequest;
use App\Models\Etudiant;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use MercurySeries\Flashy\Flashy;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.etudiants.login');
    }

    /**
     * @throws ValidationException
     */
    public function store(EtudiantLoginRequest $request)
    {
        // //Code quaund on veut utliser le blade
        // $request->authenticate();

        // $request->session()->regenerate();
        // // Flashy::success($request->user('etudiants')->greeting(), icon: 'waving_hand');
        // return redirect()->intended(route('etudiants.auth.login'));

        //Code quaund on veut utliser le nuxt
        $request->validated();
        $credentials = $request->only('email', 'password');
        //Retouner une repsonse json vu que cette route sera comme uen api en nuxt 
        $user = Etudiant::where('email', $credentials['email'])->first();
        if ($user) {
            if (!Hash::check($credentials['password'], $user->password)) {
                return response()->json([
                    'message' => 'Les informations de connexion sont invalides.'
                ], 422);
            }
            if (Auth::guard('etudiants')) {
                Auth::guard('etudiants')->login($user);
            } else {
                Auth::login($user);
            }

            $token = $user->createToken('auth_token')->plainTextToken;
        } else {
            return response()->json([
                'message' => 'Les informations de connexion sont invalides.'
            ], 422);
        }

        // Forcer le rôle étudiant si les rôles sont vides (correction temporaire pour la prod)
        if ($user->roles->isEmpty()) {
            $studentRole = new \App\Models\Role([
                'id' => 1,
                'nom' => 'Etudiant',
                'slug' => 'etudiant',
                'active' => 1
            ]);
            $user->setRelation('roles', collect([$studentRole]));
        } else {

            $studentRole = new \App\Models\Role([
                'id' => 1,
                'nom' => 'Etudiant',
                'slug' => 'etudiant',
                'active' => 1
            ]);
            $user->setRelation('roles', collect([$studentRole]));
        }


        return response()->json([
            'message' => 'Connexion réussie.',
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('etudiants')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return to_route('home');
    }

    public function update(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
            ],
        ]);

        $user = $request->user('etudiant');

        if (!$user) {
            return response()->json([
                'message' => 'Utilisateur non authentifié.'
            ], 401);
        }

        // Vérifier que l'ancien mot de passe est correct
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Le mot de passe actuel est incorrect.',
                'errors' => [
                    'current_password' => ['Le mot de passe actuel est incorrect.']
                ]
            ], 422);
        }

        // Vérifier que le nouveau mot de passe est différent
        if (Hash::check($request->new_password, $user->password)) {
            return response()->json([
                'message' => 'Le nouveau mot de passe doit être différent de l\'ancien.',
                'errors' => [
                    'new_password' => ['Le nouveau mot de passe doit être différent de l\'ancien.']
                ]
            ], 422);
        }

        // Mettre à jour le mot de passe
        $user->password = Hash::make($request->new_password);
        $user->save();

        // Optionnel : Révoquer tous les tokens
        $user->tokens()->delete();

        return response()->json([
            'message' => 'Mot de passe modifié avec succès.',
            'success' => true
        ], 200);
    }
}
