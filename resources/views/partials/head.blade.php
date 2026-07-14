@php use App\Models\{Candidature,Etudiant,User, UrgentInfo}; @endphp
<div class="" x-data="{ menuIsOpen: false, urgentIsOpen: false, formationsOpen: false }">

    <!-- Barre supérieure avec informations urgentes à gauche -->
    <div class="relative">
        <div class="mx-auto w-full border-b-4 lg:border-b-0 border-[#b09d72] bg-green-800 duration-500">

            <!-- ===== Version bureau (lg+) : tout sur une ligne ===== -->
            <div class="hidden lg:flex items-center justify-evenly py-4 px-8">
                <div class="flex items-center gap-6">
                    <div class="relative">
                        <button @click="urgentIsOpen = !urgentIsOpen"
                                class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg text-sm flex items-center shadow-md transition transform hover:scale-105">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            Informations Urgentes
                        </button>
                    </div>
                    <a href="{{ route('blogs.index') }}" class="text-white text-sm font-semibold hover:underline {{ Route::currentRouteName() == 'actualites' ? 'underline text-yellow-300' : '' }}">Actualités</a>
                    <a href="{{ route('opportunities') }}" class="text-white text-sm font-semibold hover:underline {{ Route::currentRouteName() == 'opportunities' ? 'underline text-yellow-300' : '' }}">Opportunités</a>
                </div>

                <ul class="flex items-center gap-4 text-white text-sm">
                    @if($loggedInUser = auth()->user() ?? auth()->guard('etudiants')->user() ?? auth()->guard('web_candidatures')->user())
                        <li class="text-sm font-semibold hover:border-b-2 hover:border-[#fbef8b] hover:text-[#fbef8b] pb-1">
                            <a href="https://gestion-edu.iai-togo.tg/" title="Ma session" class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Ma session
                            </a>
                        </li>
                    @else
                        <li>
                            <a href="{{ route('login') }}" class="text-sm font-semibold hover:border-b-2 hover:border-[#fbef8b] hover:text-[#fbef8b] pb-1 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                </svg>
                                Me connecter
                            </a>
                        </li>
                    @endif
                </ul>
            </div>

            <!-- ===== Version mobile (< lg) : logo/menu sur une ligne, urgences en dessous ===== -->
            <div class="lg:hidden px-4 py-2.5 space-y-2.5">
                <div class="flex items-center justify-between gap-3">
                    <a href="{{ route('home') }}" class="shrink-0">
                        <img src="https://www.iai-togo.tg/wp-content/uploads/2017/06/logo.jpeg"
                             alt="Logo {{ env('APP_NAME') }}"
                             class="h-10 w-14 object-contain">
                    </a>

                    <div class="flex items-center gap-4">
                        @if($loggedInUser = auth()->user() ?? auth()->guard('etudiants')->user() ?? auth()->guard('web_candidatures')->user())
                            <a href="https://gestion-edu.iai-togo.tg/" title="Ma session" class="text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </a>
                        @else
                            <a href="{{ route('login') }}" title="Me connecter" class="text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                </svg>
                            </a>
                        @endif
                        <button @click="menuIsOpen = !menuIsOpen" aria-label="Ouvrir le menu">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="#fbef8b"
                                 class="w-7 h-7">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <button @click="urgentIsOpen = !urgentIsOpen"
                        class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-3 rounded-lg text-xs flex items-center justify-center shadow-md transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    Informations Urgentes
                </button>
            </div>

            <!-- Fenêtre urgentes (partagée bureau/mobile) -->
            <div x-show="urgentIsOpen"
                 @click.away="urgentIsOpen = false"
                 x-transition
                 class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50 p-4">
                <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg max-h-[80vh] overflow-y-auto relative">
                    <!-- Header -->
                    <div class="flex items-center justify-between px-4 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-t-xl">
                        <h3 class="font-semibold text-sm">Informations urgentes</h3>
                        <button @click="urgentIsOpen = false" class="hover:text-gray-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Contenu dynamique: 5 dernières infos publiées -->
                    <div class="divide-y divide-gray-100">
                        @php
                            $urgentLast = UrgentInfo::where('is_published', true)
                                ->orderByDesc('published_at')
                                ->orderByDesc('created_at')
                                ->limit(5)
                                ->get();
                        @endphp
                        @forelse($urgentLast as $info)
                            @php
                                $firstAttachment = collect($info->attachments ?? [])->first();
                                $isLocalFile = false;
                                if ($info->file_path) {
                                    $downloadUrl = asset('storage/'.$info->file_path);
                                    $isLocalFile = true;
                                } elseif ($info->file_url) {
                                    $downloadUrl = $info->file_url;
                                } elseif ($firstAttachment) {
                                    $downloadUrl = asset('storage/'.$firstAttachment['path']);
                                    $isLocalFile = true;
                                } else {
                                    $downloadUrl = null;
                                }
                            @endphp
                            <div class="flex items-center gap-3 px-4 py-3 hover:bg-red-50 transition">
                                <a href="{{ route('urgent.info') }}" class="flex items-start gap-3 flex-1 min-w-0">
                                    <div class="flex-shrink-0 w-8 h-8 flex items-center justify-center bg-red-100 text-red-600 rounded-full">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-800 truncate">{{ $info->title }}</p>
                                        <span class="text-xs text-gray-500">{{ optional($info->published_at ?? $info->created_at)->diffForHumans() }}</span>
                                    </div>
                                </a>
                                @if($downloadUrl)
                                    <a href="{{ $downloadUrl }}" target="_blank" @if($isLocalFile) download @endif
                                       title="Télécharger le document joint"
                                       class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-red-600 hover:bg-red-100 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        @empty
                            <div class="px-4 py-6 text-center text-sm text-gray-500">Aucune information publiée.</div>
                        @endforelse
                    </div>

                    <!-- Footer -->
                    <div class="px-4 py-2 text-center bg-gray-50 rounded-b-xl">
                        <a href="{{ route('urgent.info') }}" class="text-xs font-semibold text-red-600 hover:underline">Voir tout →</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Menu principal desktop -->
    <div class="mx-auto border-b-4 lg:border-b-8 border-[#b09d72] w-full hidden lg:flex items-center justify-end bg-[#fbef8b] py-2 pr-8">
        <div class="mr-auto ml-8">
            <img src="https://www.iai-togo.tg/wp-content/uploads/2017/06/logo.jpeg" alt="Logo {{ env('APP_NAME') }}"
                 class="h-24 w-44 object-contain">
        </div>
        <ul class="flex items-center justify-between gap-8 lg:text-md xl:text-lg font-semibold text-[#314122]">
            <!-- Liens desktop normaux -->
            <li><a href="{{ route('home') }}" class="{{ Route::currentRouteName() == 'home' ? 'border-b-2 border-[#0D7A37] text-[#0D7A37]' : 'hover:border-b-2 hover:border-[#0D7A37] hover:text-[#0D7A37]' }}">Accueil</a></li>
            <li><a href="{{ route('about') }}" class="{{ Route::currentRouteName() == 'about' ? 'border-b-2 border-[#0D7A37] text-[#0D7A37]' : 'hover:border-b-2 hover:border-[#0D7A37] hover:text-[#0D7A37]' }}">À propos</a></li>
            <li><a href="{{ route('admission') }}" class="{{ Route::currentRouteName() == 'admission' ? 'border-b-2 border-[#0D7A37] text-[#0D7A37]' : 'hover:border-b-2 hover:border-[#0D7A37] hover:text-[#0D7A37]' }}">Admission</a></li>
            <li class="relative" x-data="{ open: false }" @click.away="open = false">
                <span @click="open = !open" class="cursor-pointer {{ Route::currentRouteName() == 'formations' ? 'border-b-2 border-[#0D7A37] text-[#0D7A37]' : 'hover:border-b-2 hover:border-[#0D7A37] hover:text-[#0D7A37]' }}">Formations</span>
                <div x-show="open" x-transition class="absolute top-full left-0 mt-0 w-64 bg-white rounded-md shadow-lg py-1 z-50 border">
                    @include('partials.formations-submenu')
                </div>
            </li>
            <li><a href="{{ route('administration') }}" class="{{ Route::currentRouteName() == 'administration' ? 'border-b-2 border-[#0D7A37] text-[#0D7A37]' : 'hover:border-b-2 hover:border-[#0D7A37] hover:text-[#0D7A37]' }}">Administration</a></li>
            <li><a href="{{ route('galerie') }}" class="{{ Route::currentRouteName() == 'galerie' ? 'border-b-2 border-[#0D7A37] text-[#0D7A37]' : 'hover:border-b-2 hover:border-[#0D7A37] hover:text-[#0D7A37]' }}">Galerie</a></li>
            <li><a href="{{ route('contact') }}" class="{{ Route::currentRouteName() == 'contact' ? 'border-b-2 border-[#0D7A37] text-[#0D7A37]' : 'hover:border-b-2 hover:border-[#0D7A37] hover:text-[#0D7A37]' }}">Contact</a></li>
        </ul>
    </div>

    <!-- Menu mobile overlay (harmonisé aux couleurs desktop) -->
    <div x-show="menuIsOpen" x-transition
         class="fixed inset-0 bg-black/50 z-[999] flex">
        <div class="bg-[#fbef8b] text-[#314122] w-3/4 max-w-xs p-6 space-y-4 relative border-r-4 border-[#b09d72] overflow-y-auto">
            <button class="absolute top-4 right-4 text-[#314122] hover:text-[#0D7A37]" @click="menuIsOpen = false" aria-label="Fermer le menu">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <div class="mb-2 pb-3 border-b border-[#b09d72]/60">
                <img src="https://www.iai-togo.tg/wp-content/uploads/2017/06/logo.jpeg" alt="Logo {{ env('APP_NAME') }}" class="h-16 w-auto object-contain">
            </div>

            <a href="{{ route('home') }}" class="block text-base font-semibold py-2 hover:text-[#0D7A37] {{ Route::currentRouteName() == 'home' ? 'border-b-2 border-[#0D7A37] text-[#0D7A37]' : '' }}">Accueil</a>
            <a href="{{ route('about') }}" class="block text-base font-semibold py-2 hover:text-[#0D7A37] {{ Route::currentRouteName() == 'about' ? 'border-b-2 border-[#0D7A37] text-[#0D7A37]' : '' }}">À propos</a>
            <a href="{{ route('admission') }}" class="block text-base font-semibold py-2 hover:text-[#0D7A37] {{ Route::currentRouteName() == 'admission' ? 'border-b-2 border-[#0D7A37] text-[#0D7A37]' : '' }}">Admission</a>

            <div>
                <button @click="formationsOpen = !formationsOpen" class="w-full flex justify-between items-center text-base font-semibold py-2 hover:text-[#0D7A37]">
                    <span class="{{ Route::currentRouteName() == 'formations' ? 'border-b-2 border-[#0D7A37] text-[#0D7A37]' : '' }}">Formations</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform" :class="{ 'rotate-180': formationsOpen }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="formationsOpen" x-transition class="pl-1 space-y-1">
                    @include('partials.formations-submenu')
                </div>
            </div>

            <a href="{{ route('administration') }}" class="block text-base font-semibold py-2 hover:text-[#0D7A37] {{ Route::currentRouteName() == 'administration' ? 'border-b-2 border-[#0D7A37] text-[#0D7A37]' : '' }}">Administration</a>
            <a href="{{ route('galerie') }}" class="block text-base font-semibold py-2 hover:text-[#0D7A37] {{ Route::currentRouteName() == 'galerie' ? 'border-b-2 border-[#0D7A37] text-[#0D7A37]' : '' }}">Galerie</a>
            <a href="{{ route('contact') }}" class="block text-base font-semibold py-2 hover:text-[#0D7A37] {{ Route::currentRouteName() == 'contact' ? 'border-b-2 border-[#0D7A37] text-[#0D7A37]' : '' }}">Contact</a>

            <div class="pt-3 mt-1 border-t border-[#b09d72]/60 space-y-1">
                <a href="{{ route('blogs.index') }}" class="block text-base font-semibold py-2 hover:text-[#0D7A37] {{ Route::currentRouteName() == 'actualites' ? 'border-b-2 border-[#0D7A37] text-[#0D7A37]' : '' }}">Actualités</a>
                <a href="{{ route('opportunities') }}" class="block text-base font-semibold py-2 hover:text-[#0D7A37] {{ Route::currentRouteName() == 'opportunities' ? 'border-b-2 border-[#0D7A37] text-[#0D7A37]' : '' }}">Opportunités</a>
            </div>
        </div>
    </div>
</div>
