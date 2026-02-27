<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Réinitialisation de mot de passe</title>
    <style>
        .hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(128, 191, 46, 0.3);
        }
    </style>
</head>
<body class="bg-gray-50 font-sans antialiased">
    <div class="max-w-2xl mx-auto my-8 px-4">
        <!-- Logo / Brand avec couleur #80BF2E et icône SVG -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-20 h-20 text-white rounded-2xl shadow-lg transition-transform hover:scale-105 duration-200" style="background-color: #80BF2E;">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 4C8.13401 4 5 7.13401 5 11V15C5 15.5523 4.55228 16 4 16C3.44772 16 3 15.5523 3 15V11C3 6.02944 7.02944 2 12 2C16.9706 2 21 6.02944 21 11V15C21 15.5523 20.5523 16 20 16C19.4477 16 19 15.5523 19 15V11C19 7.13401 15.866 4 12 4Z" fill="white"/>
                    <path d="M6 15V11C6 7.68629 8.68629 5 12 5C15.3137 5 18 7.68629 18 11V15C18 15.5523 18.4477 16 19 16C19.5523 16 20 15.5523 20 15V11C20 6.58172 16.4183 3 12 3C7.58172 3 4 6.58172 4 11V15C4 15.5523 4.44772 16 5 16C5.55228 16 6 15.5523 6 15Z" fill="white"/>
                    <path d="M8 15V11C8 8.79086 9.79086 7 12 7C14.2091 7 16 8.79086 16 11V15C16 15.5523 16.4477 16 17 16C17.5523 16 18 15.5523 18 15V11C18 7.68629 15.3137 5 12 5C8.68629 5 6 7.68629 6 11V15C6 15.5523 6.44772 16 7 16C7.55228 16 8 15.5523 8 15Z" fill="white"/>
                    <path d="M12 10C10.8954 10 10 10.8954 10 12V16C10 17.1046 10.8954 18 12 18C13.1046 18 14 17.1046 14 16V12C14 10.8954 13.1046 10 12 10Z" fill="white"/>
                </svg>
            </div>
        </div>

        <!-- Main Card -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
            <!-- Header avec accent vert -->
            <div class="border-b border-gray-200 px-8 py-6">
                <h1 class="text-2xl font-light text-gray-900 text-center tracking-wide">
                    RÉINITIALISATION DU MOT DE PASSE
                </h1>
                <div class="w-16 h-1 mx-auto mt-3" style="background-color: #80BF2E;"></div>
            </div>

            <!-- Content -->
            <div class="p-8">
                <div class="text-center mb-8">
                    <!-- Icône de sécurité SVG -->
                    <div class="flex justify-center mb-4">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="#80BF2E" stroke-width="1.5"/>
                            <path d="M12 8V12M12 16H12.01" stroke="#80BF2E" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <h2 class="text-3xl font-light text-gray-900 mb-3 tracking-tight">
                        Mot de passe oublié ?
                    </h2>
                    <p class="text-gray-500 text-lg">
                        Nous avons reçu une demande de réinitialisation pour votre compte.
                    </p>
                </div>

                <!-- Bouton de réinitialisation avec icône SVG -->
                <div class="text-center mb-8">
                    <a href="{{ $url }}" 
                       class="inline-flex items-center justify-center px-8 py-4 text-white font-medium rounded-lg transition-all duration-200 hover-lift"
                       style="background-color: #80BF2E;"
                       onmouseover="this.style.backgroundColor='#6AA226'" 
                       onmouseout="this.style.backgroundColor='#80BF2E'">
                        <!-- Icône cadenas SVG -->
                        <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2C9.23858 2 7 4.23858 7 7V9H6C4.89543 9 4 9.89543 4 11V19C4 20.1046 4.89543 21 6 21H18C19.1046 21 20 20.1046 20 19V11C20 9.89543 19.1046 9 18 9H17V7C17 4.23858 14.7614 2 12 2ZM12 4C13.6569 4 15 5.34315 15 7V9H9V7C9 5.34315 10.3431 4 12 4ZM12 14C10.8954 14 10 14.8954 10 16C10 17.1046 10.8954 18 12 18C13.1046 18 14 17.1046 14 16C14 14.8954 13.1046 14 12 14Z" fill="white"/>
                        </svg>
                        Créer un nouveau mot de passe
                    </a>
                </div>

                <!-- Lien direct avec icône -->
                <div class="bg-gray-50 rounded-lg p-6 mb-6 border border-gray-200">
                    <div class="flex items-center mb-3">
                        <!-- Icône lien SVG -->
                        <svg class="w-5 h-5 mr-2" style="color: #80BF2E;" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M13.8284 10.1716C12.2663 8.60948 9.73367 8.60948 8.17157 10.1716L4.17157 14.1716C2.60948 15.7337 2.60948 18.2663 4.17157 19.8284C5.73367 21.3905 8.26633 21.3905 9.82843 19.8284L10.93 18.7269M10.1716 13.8284C11.7337 15.3905 14.2663 15.3905 15.8284 13.8284L19.8284 9.82843C21.3905 8.26633 21.3905 5.73367 19.8284 4.17157C18.2663 2.60948 15.7337 2.60948 14.1716 4.17157L13.072 5.27114" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        <p class="text-sm text-gray-600 font-medium">
                            Lien direct :
                        </p>
                    </div>
                    <p class="text-xs text-gray-400 break-all bg-white p-3 rounded border border-gray-200 font-mono">
                        {{ $url }}
                    </p>
                </div>

                <!-- Message de sécurité avec icône -->
                <div class="border-t border-gray-200 pt-6">
                    <div class="flex items-start space-x-3">
                        <!-- Icône info SVG -->
                        <svg class="w-5 h-5 flex-shrink-0" style="color: #80BF2E;" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M12 8V12M12 16H12.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        <div>
                            <p class="text-sm text-gray-700">
                                <span class="font-medium">Demande non effectuée ?</span>
                                <span class="text-gray-500"> Si vous n'êtes pas à l'origine de cette demande, ignorez cet email.</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer avec icônes sociales (optionnel) -->
            <div class="bg-gray-50 px-8 py-4 border-t border-gray-200">
                <div class="flex justify-center space-x-4 mb-3">
                    <!-- Icône horloge SVG -->
                    <svg class="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M12 7V12L15 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    <!-- Icône sécurité SVG -->
                    <svg class="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2L3 7V12C3 16.97 7.03 21 12 21C16.97 21 21 16.97 21 12V7L12 2Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <!-- Icône email SVG -->
                    <svg class="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4 4H20C21.1 4 22 4.9 22 6V18C22 19.1 21.1 20 20 20H4C2.9 20 2 19.1 2 18V6C2 4.9 2.9 4 4 4Z" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M22 6L12 13L2 6" stroke="currentColor" stroke-width="1.5"/>
                    </svg>
                </div>
               
                <p class="text-xs text-center text-gray-300 mt-1">
                    © {{ date('Y') }} — Tous droits réservés
                </p>
            </div>
        </div>
    </div>
</body>
</html>