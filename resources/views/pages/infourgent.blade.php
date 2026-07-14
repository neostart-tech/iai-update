@extends("layouts.master")

@section('other-css')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,500;0,600;0,700;1,500&display=swap" rel="stylesheet">
<style>
    .urgent-card {
        border-color: #ece7db;
        transition: transform 0.3s cubic-bezier(.22,.9,.35,1), box-shadow 0.3s ease, border-color 0.3s ease;
    }
    .urgent-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 28px 40px -18px rgba(13, 122, 55, 0.22), 0 10px 18px -10px rgba(13, 122, 55, 0.12);
        border-color: rgba(13, 122, 55, 0.28);
    }
    .urgent-card .card-media-img {
        transition: transform 0.5s ease;
    }
    .urgent-card:hover .card-media-img {
        transform: scale(1.045);
    }
    .urgent-card h3 {
        font-family: 'Lora', Georgia, serif;
        letter-spacing: -0.01em;
    }
    .urgent-avatar {
        background: linear-gradient(145deg, #12903f, #0a5f2b);
        box-shadow: 0 0 0 3px rgba(13,122,55,0.08);
    }
    .urgent-search:focus-within {
        border-color: #0D7A37;
        box-shadow: 0 0 0 4px rgba(13, 122, 55, 0.12);
    }
    .urgent-hero-overlay {
        z-index: 1;
        background: linear-gradient(120deg, rgba(10,74,37,0.92) 0%, rgba(13,122,55,0.85) 55%, rgba(22,122,66,0.8) 100%);
    }
    .urgent-hero::before {
        content: '';
        position: absolute; inset: 0; z-index: 2; opacity: .14; pointer-events: none;
        background-image:
            radial-gradient(1.5px 1.5px at 20px 30px, #fff 100%, transparent),
            radial-gradient(1.5px 1.5px at 90px 80px, #fff 100%, transparent),
            radial-gradient(1px 1px at 150px 20px, #fff 100%, transparent),
            radial-gradient(1px 1px at 220px 100px, #fff 100%, transparent);
        background-size: 260px 160px;
    }
    .urgent-hero::after {
        content: '';
        position: absolute; right: -60px; top: -60px; width: 300px; height: 300px; border-radius: 50%;
        z-index: 2; background: rgba(176,157,114,.18); pointer-events: none;
    }
</style>
@endsection

@section('content')

    <!-- Bannière/Hero -->
    <section class="urgent-hero relative w-full overflow-hidden text-white py-14 md:py-20">
        <img src="/img/IMG_4667.jpg" alt="Bannière Informations urgentes" class="absolute inset-0 w-full h-full object-cover">
        <div class="urgent-hero-overlay absolute inset-0"></div>
        <div class="relative z-10 container mx-auto px-4">
            <div class="text-sm mb-4 opacity-90">
                <a href="{{ route('home') }}" class="hover:underline" style="color: #fbef8b;">Accueil</a>
                <span class="mx-2 opacity-60">/</span>
                <span>Informations urgentes</span>
            </div>
            <h1 class="text-3xl md:text-4xl font-extrabold tracking-wide max-w-xl">Informations urgentes</h1>
            <p class="text-sm md:text-base opacity-85 mt-3 max-w-md">Documents, avis officiels et annonces importantes de l'IAI-TOGO, mis à jour en continu par l'administration.</p>

            <div class="flex flex-wrap gap-8 mt-8">
                <div>
                    <div class="text-2xl md:text-3xl font-extrabold tabular-nums">{{ $stats['total'] }}</div>
                    <div class="text-xs uppercase tracking-widest opacity-70 mt-1">Publication{{ $stats['total'] > 1 ? 's' : '' }} active{{ $stats['total'] > 1 ? 's' : '' }}</div>
                </div>
                <div>
                    <div class="text-2xl md:text-3xl font-extrabold tabular-nums">{{ $stats['this_week'] }}</div>
                    <div class="text-xs uppercase tracking-widest opacity-70 mt-1">Cette semaine</div>
                </div>
                <div>
                    <div class="text-2xl md:text-3xl font-extrabold tabular-nums">{{ $stats['attachments'] }}</div>
                    <div class="text-xs uppercase tracking-widest opacity-70 mt-1">Pièces jointes</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contenu principal de la page des informations urgentes -->
    <div class="container mx-auto px-4">

        <!-- Barre d'outils flottante -->
        <div class="relative -mt-7 md:-mt-8 z-20 mb-10">
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-3">
                <form action="{{ route('urgent.info') }}" method="get">
                    <div class="urgent-search flex items-center bg-gray-50 border-2 border-gray-100 rounded-full pl-5 pr-1.5 py-1.5 transition-all duration-200">
                        <svg class="h-4 w-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
                        </svg>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Rechercher un avis, un concours, un calendrier…"
                               class="flex-1 bg-transparent px-3 py-1.5 text-sm text-gray-700 focus:outline-none">
                        <button type="submit"
                                class="text-white text-sm font-semibold px-5 py-2 rounded-full transition-colors shrink-0"
                                style="background-color: #0D7A37;"
                                onmouseover="this.style.backgroundColor='#0a5f2b'" onmouseout="this.style.backgroundColor='#0D7A37'">
                            Rechercher
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="pb-16">
        <p class="text-[11px] font-bold uppercase tracking-widest text-gray-400 mb-5 flex items-center gap-3">
            Publications récentes
            <span class="flex-1 h-px bg-gray-100"></span>
        </p>

        <!-- Liste des informations urgentes -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
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
                <article class="urgent-card group bg-white rounded-[22px] border overflow-hidden flex flex-col">
                    @if($coverUrl)
                        <div class="relative h-44 w-full overflow-hidden bg-gray-100">
                            <img src="{{ $coverUrl }}" alt="{{ $info->title }}" class="card-media-img w-full h-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/25 via-transparent to-transparent"></div>
                            <span class="absolute top-3.5 left-3.5 inline-flex items-center gap-1.5 bg-white/95 backdrop-blur text-[10.5px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full shadow-sm" style="color:#c23c34;">
                                <span class="w-1.5 h-1.5 rounded-full" style="background:#c23c34;"></span> Urgent
                            </span>
                            <span class="absolute top-3.5 right-3.5 text-white text-[11px] font-medium px-2.5 py-1">
                                {{ optional($info->published_at ?? $info->created_at)->diffForHumans() }}
                            </span>
                        </div>
                    @endif

                    <div class="p-7 flex flex-col flex-1">
                        @unless($coverUrl)
                            <div class="flex items-center justify-between mb-4">
                                <span class="inline-flex items-center gap-1.5 text-[10.5px] font-bold uppercase tracking-wider" style="color:#c23c34;">
                                    <span class="w-1.5 h-1.5 rounded-full" style="background:#c23c34;"></span> Urgent
                                </span>
                                <span class="text-xs text-gray-400">{{ optional($info->published_at ?? $info->created_at)->diffForHumans() }}</span>
                            </div>
                        @endunless

                        <h3 class="text-[19px] font-semibold text-gray-900 mb-2.5 leading-snug">{{ $info->title }}</h3>

                        @if($info->summary)
                            <p class="text-gray-500 text-[13.5px] mb-5 leading-relaxed line-clamp-3 whitespace-pre-line">{{ $info->summary }}</p>
                        @endif

                        <!-- Pièces jointes -->
                        @if(!empty($attachments))
                            <div class="mb-5 space-y-2">
                                @foreach($attachments as $att)
                                    <a href="{{ asset('storage/'.$att['path']) }}" target="_blank" download
                                       class="flex items-center gap-3 p-3 rounded-2xl border transition-colors group/att"
                                       style="border-color:#ece7db; background:#fbfaf7;"
                                       onmouseover="this.style.borderColor='rgba(13,122,55,0.35)'; this.style.background='rgba(13,122,55,0.04)'"
                                       onmouseout="this.style.borderColor='#ece7db'; this.style.background='#fbfaf7'">
                                        <div class="w-9 h-9 rounded-xl bg-white border border-gray-200 flex items-center justify-center shrink-0">
                                            <svg class="w-4 h-4 text-gray-400 group-hover/att:text-[#0D7A37]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <span class="text-[13px] text-gray-700 group-hover/att:text-[#0D7A37] truncate flex-1 font-medium">{{ $att['name'] ?? 'Pièce jointe' }}</span>
                                        <span class="text-[11px] text-gray-400 shrink-0 tabular-nums">{{ $formatSize($att['size'] ?? null) }}</span>
                                    </a>
                                @endforeach
                            </div>
                        @endif

                        <div class="mt-auto flex items-center justify-between pt-5" style="border-top:1px solid #ece7db;">
                            <div class="flex items-center min-w-0">
                                <div class="urgent-avatar w-9 h-9 rounded-full flex items-center justify-center text-white font-bold text-[11px] shrink-0">IAI</div>
                                <div class="ml-3 min-w-0">
                                    <p class="text-[13px] font-semibold text-gray-900 truncate">Administration</p>
                                    <p class="text-[11px] text-gray-400 uppercase tracking-wide">{{ optional($info->published_at ?? $info->created_at)->translatedFormat('d M Y') }}</p>
                                </div>
                            </div>
                            @if(empty($attachments) && $docUrl)
                                <a href="{{ $docUrl }}" target="_blank" @if($info->file_path) download @endif
                                   class="font-semibold text-[13px] flex items-center gap-1 shrink-0 ml-2" style="color: #0D7A37;">
                                    Télécharger
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-span-1 md:col-span-2 lg:col-span-3 flex flex-col items-center justify-center text-center py-20 px-4 bg-white rounded-2xl border border-dashed border-gray-200">
                    <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-4" style="background-color: rgba(13,122,55,0.08);">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="#0D7A37">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">
                        {{ request('q') ? 'Aucun résultat trouvé' : 'Aucune information urgente publiée' }}
                    </h3>
                    <p class="text-gray-500 text-sm max-w-sm">
                        @if(request('q'))
                            Aucune information ne correspond à « {{ request('q') }} ». Essayez un autre mot-clé.
                        @else
                            Revenez bientôt : les prochaines annonces importantes de l'IAI-TOGO apparaîtront ici.
                        @endif
                    </p>
                </div>
            @endforelse
        </div>

        @if($items->hasPages())
            <div class="mt-10 flex justify-center">
                {{ $items->links() }}
            </div>
        @endif
        </div>

    </div>
@endsection
