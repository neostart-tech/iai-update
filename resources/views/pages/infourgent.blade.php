@extends("layouts.master")

@section('content')

    <!-- Bannière/Hero -->
    <section class="relative w-full h-[40vh] md:h-[50vh] overflow-hidden bg-gray-900">
        <img src="/img/IMG_4667.jpg" alt="Bannière Informations urgentes" class="absolute inset-0 w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-r from-green-900/80 to-emerald-800/60"></div>
        <div class="relative z-10 flex items-center justify-center h-full text-center text-white px-4">
            <div>
                <h1 class="text-3xl md:text-4xl font-extrabold tracking-wide">Informations urgentes</h1>
                <div class="w-16 h-1 bg-amber-400 rounded-full mx-auto my-4"></div>
                <p class="text-sm md:text-base opacity-90">Documents et annonces importantes de l'IAI-TOGO</p>
                <div class="mt-6">
                    <a href="{{ route('home') }}" class="text-amber-300 hover:text-white">Accueil</a>
                    <span class="mx-2">/</span>
                    <span class="text-white">Informations urgentes</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Contenu principal de la page des informations urgentes -->
    <div class="container mx-auto px-4 py-12">
        <!-- Titre section -->
        <div class="flex items-center mb-8">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mr-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <div>
                <h2 class="text-2xl md:text-3xl font-bold text-gray-900">Informations urgentes</h2>
                <p class="text-gray-600">Tous les documents et annonces importantes de l'IAI-TOGO</p>            </div>
        </div>

        <!-- Barre de recherche -->
        <form action="{{ route('urgent.info') }}" method="get" class="mb-8">
            <div class="flex items-stretch max-w-2xl">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Rechercher une information (titre, résumé)" class="flex-1 border border-gray-300 rounded-l-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-600">
                <button type="submit" class="bg-green-700 hover:bg-green-800 text-white px-5 rounded-r-md">Rechercher</button>
            </div>
        </form>

        <!-- Liste des informations urgentes -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($items as $info)
                @php
                    $docUrl = $info->file_path ? asset('storage/'.$info->file_path) : $info->file_url;
                    $coverUrl = $info->image ? asset('storage/'.$info->image) : null;
                    $attachments = $info->attachments ?? [];

                    $formatSize = function ($bytes) {
                        if (!$bytes) return '';
                        $units = ['o', 'Ko', 'Mo', 'Go'];
                        $i = 0;
                        while ($bytes >= 1024 && $i < count($units) - 1) { $bytes /= 1024; $i++; }
                        return round($bytes, 1) . ' ' . $units[$i];
                    };
                @endphp
                <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 hover:shadow-xl transition-shadow duration-300 flex flex-col">
                    @if($coverUrl)
                        <div class="relative h-44 w-full overflow-hidden bg-gray-100">
                            <img src="{{ $coverUrl }}" alt="{{ $info->title }}" class="w-full h-full object-cover">
                            <span class="absolute top-3 left-3 bg-red-600 text-white text-xs font-semibold px-2.5 py-1 rounded-full shadow">Urgent</span>
                        </div>
                    @endif

                    <div class="p-6 flex flex-col flex-1">
                        <div class="flex items-center justify-between mb-4">
                            @unless($coverUrl)
                                <span class="bg-red-100 text-red-800 text-xs font-semibold px-2.5 py-0.5 rounded">Urgent</span>
                            @else
                                <span></span>
                            @endunless
                            <span class="text-xs text-gray-500">{{ optional($info->published_at ?? $info->created_at)->diffForHumans() }}</span>
                        </div>

                        <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $info->title }}</h3>

                        @if($info->summary)
                            <p class="text-gray-600 mb-4 whitespace-pre-line">{{ $info->summary }}</p>
                        @endif

                        <!-- Pièces jointes -->
                        @if(!empty($attachments))
                            <div class="mb-4 space-y-2">
                                @foreach($attachments as $att)
                                    <a href="{{ asset('storage/'.$att['path']) }}" target="_blank" download
                                       class="flex items-center gap-2 p-2 rounded-lg border border-gray-200 hover:border-green-400 hover:bg-green-50 transition-colors group">
                                        <svg class="w-5 h-5 text-gray-400 group-hover:text-green-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                        <span class="text-sm text-gray-700 group-hover:text-green-700 truncate flex-1">{{ $att['name'] ?? 'Pièce jointe' }}</span>
                                        <span class="text-xs text-gray-400 shrink-0">{{ $formatSize($att['size'] ?? null) }}</span>
                                    </a>
                                @endforeach
                            </div>
                        @endif

                        <div class="mt-auto flex items-center justify-between pt-4 border-t border-gray-100">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-custom-green rounded-full flex items-center justify-center text-white font-bold text-sm shrink-0">IAI</div>
                                <div class="ml-2">
                                    <p class="text-sm font-medium text-gray-900">Administration</p>
                                    <p class="text-xs text-gray-500">Publié le {{ optional($info->published_at ?? $info->created_at)->translatedFormat('d/m/Y') }}</p>
                                </div>
                            </div>
                            @if(empty($attachments) && $docUrl)
                                <a href="{{ $docUrl }}" target="_blank" @if($info->file_path) download @endif class="text-custom-green hover:text-green-800 font-medium text-sm flex items-center shrink-0">
                                    Télécharger
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-3 text-center text-gray-500">Aucune information urgente publiée pour le moment.</div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $items->links() }}
        </div>

    </div>
@endsection