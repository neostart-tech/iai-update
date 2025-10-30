<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Etudiant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UnifiedLoginController extends Controller
{
    /**
     * Affiche le formulaire de connexion unifiée
     */
    public function showLoginForm()
    {
        return view('auth.unified-login');
    }

    /**
     * Traite la connexion en détectant automatiquement le type d'utilisateur
     */
    public function login(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
            'password' => 'required|string',
        ], [
            'identifier.required' => 'L\'identifiant est requis.',
            'password.required' => 'Le mot de passe est requis.',
        ]);

        $identifier = $request->identifier;
        $password = $request->password;

        // Tentative 1: Vérifier les étudiants (par matricule ou email)
        $etudiant = Etudiant::where('matricule', $identifier)
            ->orWhere('email', $identifier)
            ->first();

        if ($etudiant && Hash::check($password, $etudiant->password)) {
            Auth::guard('etudiants')->login($etudiant, $request->boolean('remember'));
            
            $request->session()->regenerate();
            
            return redirect()->intended(route('etudiants.dashboard'))
                ->with('success', 'Connexion réussie ! Bienvenue ' . $etudiant->prenom);
        }

        // Tentative 2: Vérifier les utilisateurs admin/enseignants (par email ou matricule)
        $user = User::where('email', $identifier)
            ->orWhere('matricule', $identifier)
            ->first();

        if ($user && Hash::check($password, $user->password)) {
            
            // Déterminer le guard approprié selon les rôles
            $userRoles = $user->roles->pluck('nom')->toArray();
            
            // Si c'est un enseignant (rôle ID 2 ou nom contenant "Enseignant")
            if (in_array('Enseignant', $userRoles) || $user->roles->contains('id', 2)) {
                Auth::guard('enseignants')->login($user, $request->boolean('remember'));
                $request->session()->regenerate();
                
                return redirect()->intended(route('enseignants.index'))
                    ->with('success', 'Connexion enseignant réussie ! Bienvenue ' . $user->prenom);
            }
            
            // Si c'est un comptable
            if (in_array('Comptable', $userRoles) || in_array('Directeur des Affaires Financières', $userRoles)) {
                Auth::guard('comptables')->login($user, $request->boolean('remember'));
                $request->session()->regenerate();
                
                return redirect()->intended(route('dashboard'))
                    ->with('success', 'Connexion comptabilité réussie ! Bienvenue ' . $user->prenom);
            }
            
            // Sinon, connexion admin par défaut
            Auth::guard('web')->login($user, $request->boolean('remember'));
            $request->session()->regenerate();
            
            return redirect()->intended(route('admin.dashboard', ['dashboard']))
                ->with('success', 'Connexion administration réussie ! Bienvenue ' . $user->prenom);
        }

        // Aucune correspondance trouvée
        throw ValidationException::withMessages([
            'identifier' => 'Ces identifiants ne correspondent à aucun compte utilisateur.',
        ]);
    }

    /**
     * Déconnexion universelle
     */
    public function logout(Request $request)
    {
        // Déconnecter de tous les guards possibles
        Auth::guard('web')->logout();
        Auth::guard('etudiants')->logout();
        Auth::guard('enseignants')->logout();
        Auth::guard('comptables')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('unified.login')
            ->with('success', 'Vous avez été déconnecté avec succès.');
    }

    /**
     * API pour vérifier le type d'utilisateur (pour l'interface)
     */
    public function checkUserType(Request $request)
    {
        $identifier = $request->get('identifier');
        
        if (empty($identifier)) {
            return response()->json(['type' => null]);
        }

        // Vérifier étudiant
        $etudiant = Etudiant::where('matricule', $identifier)
            ->orWhere('email', $identifier)
            ->first();
        
        if ($etudiant) {
            return response()->json([
                'type' => 'etudiant',
                'name' => $etudiant->prenom . ' ' . strtoupper($etudiant->nom),
                'description' => 'Compte étudiant'
            ]);
        }

        // Vérifier utilisateur admin/enseignant
        $user = User::with('roles')->where('email', $identifier)
            ->orWhere('matricule', $identifier)
            ->first();
        
        if ($user) {
            $roles = $user->roles->pluck('nom')->toArray();
            $roleNames = implode(', ', $roles);
            
            if (in_array('Enseignant', $roles)) {
                $type = 'enseignant';
                $description = 'Compte enseignant';
            } elseif (in_array('Comptable', $roles) || in_array('Directeur des Affaires Financières', $roles)) {
                $type = 'comptable';
                $description = 'Compte comptabilité';
            } else {
                $type = 'admin';
                $description = 'Compte administration';
            }
            
            return response()->json([
                'type' => $type,
                'name' => $user->prenom . ' ' . strtoupper($user->nom),
                'description' => $description,
                'roles' => $roleNames
            ]);
        }

        return response()->json(['type' => null]);
    }
}