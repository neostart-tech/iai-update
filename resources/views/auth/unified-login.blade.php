@extends('auth.base', [
    'title' => 'Connexion Unifiée - ' . config('app.name'),
    'pageTitle' => 'Connexion'
])

@section('form')
    <form action="{{ route('unified.login.post') }}" method="post" id="unifiedLoginForm">
        @csrf
        
        <!-- Messages de statut -->
        @if(session('status'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Champ identifiant -->
        <div class="form-group mb-3">
            <x-forms.label for="identifier" content="Identifiant"/>
            <input type="text" 
                   class="form-control @error('identifier') is-invalid @enderror" 
                   id="identifier" 
                   name="identifier" 
                   value="{{ old('identifier') }}"
                   placeholder="Email, matricule ou identifiant"
                   autocomplete="username">
            
            <!-- Zone d'information dynamique sur l'utilisateur -->
            <div id="userInfo" class="mt-2" style="display: none;">
                <div class="alert alert-info py-2 mb-0">
                    <div class="d-flex align-items-center">
                        <div id="userAvatar" class="me-2">
                            <i class="fas fa-user-circle fa-2x text-primary"></i>
                        </div>
                        <div>
                            <div id="userName" class="fw-bold"></div>
                            <div id="userDescription" class="small text-muted"></div>
                            <div id="userRoles" class="small text-success"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            @error('identifier')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Champ mot de passe -->
        <div class="form-group mb-3">
            <x-forms.label for="password" content="Mot de passe"/>
            <div class="input-group">
                <input type="password" 
                       class="form-control @error('password') is-invalid @enderror" 
                       id="password" 
                       name="password" 
                       placeholder="Votre mot de passe"
                       autocomplete="current-password">
                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                    <i class="fas fa-eye" id="passwordIcon"></i>
                </button>
            </div>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Options de connexion -->
        <div class="d-flex mt-3 justify-content-between align-items-center">
            <div class="form-check">
                <input class="form-check-input input-primary" 
                       name="remember" 
                       type="checkbox" 
                       id="remember" 
                       {{ old('remember') ? 'checked' : '' }}>
                <label class="form-check-label text-muted" for="remember">
                    Se souvenir de moi
                </label>
            </div>
            <a href="{{ route('password.request') }}" class="text-decoration-none">
                <h6 class="text-secondary f-w-400 mb-0">
                    <i class="fas fa-key me-1"></i>
                    Mot de passe oublié ?
                </h6>
            </a>
        </div>

        <!-- Bouton de connexion -->
        <div class="d-grid mt-4">
            <button type="submit" class="btn btn-primary btn-lg" id="loginButton">
                <i class="fas fa-sign-in-alt me-2"></i>
                <span id="loginButtonText">Se connecter</span>
                <div class="spinner-border spinner-border-sm ms-2 d-none" role="status" id="loginSpinner">
                    <span class="visually-hidden">Connexion...</span>
                </div>
            </button>
        </div>

        <!-- Liens d'accès directs -->
        <div class="mt-4 text-center">
            <small class="text-muted">
                Ou accéder directement à :
            </small>
            <div class="row mt-2">
                <div class="col-6">
                    <a href="{{ route('etudiants.auth.login') }}" class="btn btn-outline-info btn-sm w-100">
                        <i class="fas fa-graduation-cap me-1"></i>
                        Espace Étudiant
                    </a>
                </div>
                <div class="col-6">
                    <a href="{{ route('enseignants.auth.login') }}" class="btn btn-outline-success btn-sm w-100">
                        <i class="fas fa-chalkboard-teacher me-1"></i>
                        Espace Enseignant
                    </a>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('form-head')
    <div class="text-center mb-4">
        <div class="mb-3">
            <i class="fas fa-university fa-3x text-primary mb-2"></i>
        </div>
        <h3 class="mb-2"><b>Connexion Unifiée</b></h3>
        <p class="text-muted">
            <i class="fas fa-info-circle me-1"></i>
            Une seule connexion pour tous vos espaces
        </p>
    </div>
    
    @if($errors->any())
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            {{ $errors->first() }}
        </div>
    @endif
@endsection

@push('styles')
<style>
    .form-control:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
    
    .user-type-indicator {
        border-left: 4px solid #0d6efd;
        background-color: #f8f9fa;
    }
    
    #userInfo {
        animation: fadeIn 0.3s ease-in;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .btn-outline-info:hover,
    .btn-outline-success:hover {
        transform: translateY(-1px);
        transition: all 0.3s ease;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const identifierInput = document.getElementById('identifier');
    const userInfo = document.getElementById('userInfo');
    const userName = document.getElementById('userName');
    const userDescription = document.getElementById('userDescription');
    const userRoles = document.getElementById('userRoles');
    const userAvatar = document.getElementById('userAvatar');
    const loginButton = document.getElementById('loginButton');
    const loginButtonText = document.getElementById('loginButtonText');
    const loginSpinner = document.getElementById('loginSpinner');
    const togglePassword = document.getElementById('togglePassword');
    const passwordField = document.getElementById('password');
    const passwordIcon = document.getElementById('passwordIcon');
    
    let debounceTimer;

    // Détection automatique du type d'utilisateur
    identifierInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const value = this.value.trim();
        
        if (value.length < 3) {
            userInfo.style.display = 'none';
            return;
        }

        debounceTimer = setTimeout(() => {
            fetch(`{{ route('unified.check-user-type') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ identifier: value })
            })
            .then(response => response.json())
            .then(data => {
                if (data.type) {
                    userName.textContent = data.name;
                    userDescription.textContent = data.description;
                    userRoles.textContent = data.roles ? `Rôles: ${data.roles}` : '';
                    
                    // Icônes selon le type
                    const icons = {
                        'etudiant': 'fas fa-graduation-cap text-info',
                        'enseignant': 'fas fa-chalkboard-teacher text-success',
                        'comptable': 'fas fa-calculator text-warning',
                        'admin': 'fas fa-user-cog text-primary'
                    };
                    
                    userAvatar.innerHTML = `<i class="${icons[data.type] || 'fas fa-user-circle text-secondary'} fa-2x"></i>`;
                    userInfo.style.display = 'block';
                } else {
                    userInfo.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                userInfo.style.display = 'none';
            });
        }, 500);
    });

    // Basculer la visibilité du mot de passe
    togglePassword.addEventListener('click', function() {
        const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordField.setAttribute('type', type);
        
        passwordIcon.className = type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
    });

    // Animation du bouton lors de la soumission
    document.getElementById('unifiedLoginForm').addEventListener('submit', function() {
        loginButtonText.textContent = 'Connexion en cours...';
        loginSpinner.classList.remove('d-none');
        loginButton.disabled = true;
    });

    // Focus automatique sur le champ identifiant
    identifierInput.focus();
});
</script>
@endpush