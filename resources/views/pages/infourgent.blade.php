@extends("layouts.master")

@section('other-css')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,500;0,600;0,700;1,500&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    /* ── Design system ── */
    :root {
        --iai-green:  #0D7A37;
        --iai-dark:   #063819;
        --iai-gold:   #b09d72;
        --iai-red:    #c23c34;
        --card-bg:    #ffffff;
        --page-bg:    #f6f7f5;
    }

    body { font-family: 'DM Sans', sans-serif; }

    /* ── Hero ── */
    .ui-hero-overlay {
        background: linear-gradient(118deg, rgba(6,56,25,0.94) 0%, rgba(13,122,55,0.88) 55%, rgba(10,95,43,0.82) 100%);
    }
    .ui-hero-dots::before {
        content:''; position:absolute; inset:0; pointer-events:none; z-index:2; opacity:.09;
        background-image: radial-gradient(circle, #fff 1px, transparent 1px);
        background-size: 22px 22px;
    }
    .ui-stat-pill {
        background: rgba(255,255,255,0.10);
        border: 1px solid rgba(255,255,255,0.18);
        backdrop-filter: blur(6px);
        border-radius: 14px;
        padding: 12px 22px;
        transition: background .2s;
    }
    .ui-stat-pill:hover { background: rgba(255,255,255,0.16); }

    /* ── Search bar ── */
    .ui-search-wrap {
        transition: box-shadow .2s, border-color .2s;
        border: 2px solid #e8ebe6;
    }
    .ui-search-wrap:focus-within {
        border-color: var(--iai-green);
        box-shadow: 0 0 0 4px rgba(13,122,55,0.10);
    }

    /* ── Card editorial ── */
    .ui-card {
        background: var(--card-bg);
        border-radius: 20px;
        border: 1px solid #e8ebe6;
        overflow: hidden;
        transition: box-shadow .28s cubic-bezier(.22,.9,.35,1), transform .28s cubic-bezier(.22,.9,.35,1), border-color .28s;
        position: relative;
    }
    .ui-card::before {
        content: '';
        position: absolute;
        left: 0; top: 0; bottom: 0;
        width: 4px;
        background: var(--iai-green);
        border-radius: 4px 0 0 4px;
        transform: scaleY(0);
        transform-origin: bottom;
        transition: transform .28s cubic-bezier(.22,.9,.35,1);
    }
    .ui-card:hover::before { transform: scaleY(1); }
    .ui-card:hover {
        box-shadow: 0 24px 48px -12px rgba(13,122,55,0.16), 0 8px 16px -8px rgba(0,0,0,0.06);
        border-color: rgba(13,122,55,0.22);
        transform: translateY(-4px);
    }

    /* Card image zoom */
    .ui-card-img { transition: transform .5s ease; }
    .ui-card:hover .ui-card-img { transform: scale(1.04); }

    /* ── Attachment row ── */
    .ui-att-row {
        border: 1.5px solid #ece9e2;
        background: #fafaf8;
        border-radius: 14px;
        transition: background .18s, border-color .18s, transform .18s;
    }
    .ui-att-row:hover {
        background: #f0f8f3;
        border-color: rgba(13,122,55,0.30);
        transform: translateX(3px);
    }
    .ui-att-icon {
        width: 38px; height: 38px;
        border-radius: 11px;
        background: #f1f1ee;
        border: 1px solid #e4e4e0;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        transition: background .18s, border-color .18s;
    }
    .ui-att-row:hover .ui-att-icon {
        background: rgba(13,122,55,0.08);
        border-color: rgba(13,122,55,0.22);
    }

    /* ── Cover image badge ── */
    .ui-badge-urgent {
        font-size: 10px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase;
        background: rgba(255,255,255,0.95); backdrop-filter: blur(6px);
        color: var(--iai-red);
        border-radius: 50px;
        padding: 4px 11px;
        display: inline-flex; align-items: center; gap: 5px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.10);
    }

    /* ── Empty state ── */
    .ui-empty {
        border: 2px dashed #dde3d9;
        border-radius: 22px;
        background: #f9faf8;
    }

    /* ── Section label ── */
    .ui-section-label {
        font-size: 10.5px; font-weight: 700;
        text-transform: uppercase; letter-spacing: .14em;
        color: #9ca3af;
    }

    /* ── Card title ── */
    .ui-card-title {
        font-family: 'Lora', Georgia, serif;
        font-weight: 700;
        letter-spacing: -.015em;
        color: #111827;
        line-height: 1.3;
    }

    /* ── Avatar ── */
    .ui-avatar {
        width: 34px; height: 34px;
        background: linear-gradient(145deg, #12903f, #0a5f2b);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 10px; font-weight: 700; color: #fff;
        box-shadow: 0 0 0 2px rgba(13,122,55,0.15);
        flex-shrink: 0;
    }

    /* ── Download button ── */
    .ui-dl-btn {
        display: inline-flex; align-items: center; gap: 5px;
        font-size: 12.5px; font-weight: 600;
        color: var(--iai-green);
        padding: 6px 14px;
        border-radius: 50px;
        border: 1.5px solid rgba(13,122,55,0.25);
        background: rgba(13,122,55,0.05);
        transition: background .18s, border-color .18s, transform .18s;
        white-space: nowrap;
    }
    .ui-dl-btn:hover {
        background: var(--iai-green);
        border-color: var(--iai-green);
        color: #fff;
        transform: translateY(-1px);
    }
</style>
@endsection

@section('content')
<div style="background: var(--page-bg); min-height: 100vh;">

    {{-- ═══ HERO ═══ --}}
    <section class="ui-hero-dots relative w-full overflow-hidden text-white" style="padding: 72px 0 80px;">
        <img src="/img/IMG_4667.jpg" alt="Informations urgentes IAI-TOGO"
             class="absolute inset-0 w-full h-full object-cover" style="z-index:0;">
        <div class="ui-hero-overlay absolute inset-0" style="z-index:1;"></div>

        {{-- Décoration dorée --}}
        <div class="absolute" style="right:-80px; top:-80px; width:320px; height:320px; border-radius:50%; background:rgba(176,157,114,0.14); pointer-events:none; z-index:2;"></div>
        <div class="absolute" style="left:-40px; bottom:-60px; width:200px; height:200px; border-radius:50%; background:rgba(176,157,114,0.08); pointer-events:none; z-index:2;"></div>

        <div class="relative container mx-auto px-4 sm:px-6 lg:px-8" style="z-index:10;">
            {{-- Fil d'Ariane --}}
            <nav class="flex items-center gap-2 text-sm mb-6 opacity-80">
                <a href="{{ route('home') }}" class="hover:opacity-100 transition-opacity" style="color:#fbef8b;">Accueil</a>
                <svg class="w-3.5 h-3.5 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span>Informations urgentes</span>
            </nav>

            <div class="max-w-3xl">
                {{-- Label --}}
                <div class="inline-flex items-center gap-2 mb-4 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-widest"
                     style="background:rgba(176,157,114,0.22); border:1px solid rgba(176,157,114,0.35); color:#d4bc8a;">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75" style="background:#b09d72;"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2" style="background:#b09d72;"></span>
                    </span>
                    Mises à jour en continu
                </div>

                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold mb-4 leading-tight"
                    style="font-family:'DM Sans',sans-serif; letter-spacing:-.02em;">
                    Informations urgentes
                </h1>
                <p class="text-base sm:text-lg opacity-80 max-w-xl leading-relaxed mb-10">
                    Documents officiels, avis de concours et annonces importantes publiés par l'administration de l'IAI-TOGO.
                </p>

                {{-- Stats --}}
                <div class="flex flex-wrap gap-3">
                    <div class="ui-stat-pill text-center">
                        <div class="text-2xl font-extrabold tabular-nums">{{ $stats['total'] }}</div>
                        <div class="text-[10px] uppercase tracking-widest opacity-65 mt-0.5">Publication{{ $stats['total'] > 1 ? 's' : '' }}</div>
                    </div>
                    <div class="ui-stat-pill text-center">
                        <div class="text-2xl font-extrabold tabular-nums">{{ $stats['this_week'] }}</div>
                        <div class="text-[10px] uppercase tracking-widest opacity-65 mt-0.5">Cette semaine</div>
                    </div>
                    <div class="ui-stat-pill text-center">
                        <div class="text-2xl font-extrabold tabular-nums">{{ $stats['attachments'] }}</div>
                        <div class="text-[10px] uppercase tracking-widest opacity-65 mt-0.5">Pièces jointes</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══ CONTENU PRINCIPAL ═══ --}}
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Barre de recherche flottante --}}
        <div class="relative -mt-8 z-20 mb-10 max-w-2xl mx-auto lg:mx-0">
            <div class="bg-white rounded-2xl shadow-xl p-3" style="border:1px solid #e8ebe6;">
                <form action="{{ route('urgent.info') }}" method="get">
                    <div class="ui-search-wrap flex items-center bg-gray-50 rounded-xl pl-4 pr-2 py-1.5">
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="#9ca3af" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/>
                        </svg>
                        <input type="text" name="q" value="{{ request('q') }}"
                               placeholder="Rechercher un avis, concours, calendrier…"
                               class="flex-1 bg-transparent px-3 py-1.5 text-sm text-gray-700 focus:outline-none">
                        <button type="submit"
                                class="text-white text-sm font-semibold px-5 py-2 rounded-lg shrink-0 transition-colors"
                                style="background:var(--iai-green);"
                                onmouseover="this.style.background='#0a5f2b'" onmouseout="this.style.background='var(--iai-green)'">
                            Rechercher
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="pb-20">
            {{-- Label section --}}
            <div class="flex items-center gap-3 mb-7">
                <span class="ui-section-label">Publications récentes</span>
                <span class="flex-1 h-px" style="background:#e2e6df;"></span>
                @if(request('q'))
                    <span class="text-xs text-gray-500 shrink-0">Résultats pour « {{ request('q') }} »</span>
                @endif
            </div>

            {{-- ═══ GRILLE DES CARTES ═══ --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 lg:gap-7">
                @forelse($items as $info)
                    @php
                        $docUrl     = $info->file_path ? asset('storage/'.$info->file_path) : $info->file_url;
                        $coverUrl   = $info->image     ? asset('storage/'.$info->image)     : null;
                        $attachments = $info->attachments ?? [];
                        $pubDate    = $info->published_at ?? $info->created_at;

                        $formatSize = function ($bytes) {
                            if (!$bytes) return '';
                            $units = ['o', 'Ko', 'Mo', 'Go'];
                            $i = 0;
                            while ($bytes >= 1024 && $i < count($units) - 1) { $bytes /= 1024; $i++; }
                            return round($bytes, 1) . ' ' . $units[$i];
                        };
                    @endphp

                    <article class="ui-card flex flex-col">

                        {{-- Image de couverture --}}
                        @if($coverUrl)
                            <div class="relative h-44 overflow-hidden bg-gray-100 shrink-0" style="border-radius:20px 20px 0 0;">
                                <img src="{{ $coverUrl }}" alt="{{ $info->clean_title ?: $info->title }}"
                                     class="ui-card-img w-full h-full object-cover">
                                <div class="absolute inset-0" style="background:linear-gradient(to top, rgba(0,0,0,.28) 0%, transparent 55%);"></div>
                                <span class="ui-badge-urgent absolute top-3.5 left-3.5">
                                    <span class="w-1.5 h-1.5 rounded-full" style="background:var(--iai-red);"></span>
                                    Urgent
                                </span>
                                <span class="absolute top-3.5 right-3.5 text-white text-[11px] font-medium px-2 py-0.5 rounded-full"
                                      style="background:rgba(0,0,0,0.30); backdrop-filter:blur(4px);">
                                    {{ optional($pubDate)->diffForHumans() }}
                                </span>
                            </div>
                        @endif

                        {{-- Corps de la carte --}}
                        <div class="p-6 flex flex-col flex-1">

                            {{-- Header sans image --}}
                            @unless($coverUrl)
                                <div class="flex items-center justify-between mb-4">
                                    <span class="inline-flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider" style="color:var(--iai-red);">
                                        <span class="relative flex h-1.5 w-1.5">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75" style="background:var(--iai-red);"></span>
                                            <span class="relative inline-flex rounded-full h-1.5 w-1.5" style="background:var(--iai-red);"></span>
                                        </span>
                                        Urgent
                                    </span>
                                    <span class="text-[11px] text-gray-400 font-medium">
                                        {{ optional($pubDate)->diffForHumans() }}
                                    </span>
                                </div>
                            @endunless

                            {{-- Titre --}}
                            <h2 class="ui-card-title text-[17px] sm:text-[18px] mb-3 leading-snug">
                                {{ $info->clean_title ?: $info->title }}
                            </h2>

                            {{-- Résumé --}}
                            @if($info->clean_summary)
                                <p class="text-gray-500 text-[13px] leading-relaxed mb-5 line-clamp-3">
                                    {{ $info->clean_summary }}
                                </p>
                            @endif

                            {{-- Pièces jointes --}}
                            @if(!empty($attachments))
                                <div class="space-y-2 mb-5">
                                    @foreach($attachments as $att)
                                        <a href="{{ asset('storage/'.$att['path']) }}" target="_blank" download
                                           class="ui-att-row flex items-center gap-3 px-3 py-2.5">
                                            <div class="ui-att-icon">
                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                          d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-[12.5px] font-semibold text-gray-800 truncate">
                                                    {{ $att['name'] ?? 'Pièce jointe' }}
                                                </p>
                                            </div>
                                            <span class="text-[11px] text-gray-400 tabular-nums shrink-0 font-medium">
                                                {{ $formatSize($att['size'] ?? null) }}
                                            </span>
                                            <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                      d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                            </svg>
                                        </a>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Footer --}}
                            <div class="mt-auto pt-4 flex items-center justify-between" style="border-top:1px solid #eceae4;">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="ui-avatar">IAI</div>
                                    <div class="min-w-0">
                                        <p class="text-[12.5px] font-semibold text-gray-900 truncate">Administration</p>
                                        <p class="text-[10.5px] text-gray-400 uppercase tracking-wide">
                                            {{ optional($pubDate)->translatedFormat('d M Y') }}
                                        </p>
                                    </div>
                                </div>

                                @if(empty($attachments) && $docUrl)
                                    <a href="{{ $docUrl }}" target="_blank" @if($info->file_path) download @endif
                                       class="ui-dl-btn ml-3 shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                        Télécharger
                                    </a>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="col-span-1 sm:col-span-2 lg:col-span-3 ui-empty flex flex-col items-center justify-center text-center py-24 px-6">
                        <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-5"
                             style="background:rgba(13,122,55,0.07); border:1.5px solid rgba(13,122,55,0.12);">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                                 stroke="var(--iai-green)" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">
                            {{ request('q') ? 'Aucun résultat trouvé' : 'Aucune information urgente publiée' }}
                        </h3>
                        <p class="text-gray-500 text-sm max-w-sm leading-relaxed">
                            @if(request('q'))
                                Aucune information ne correspond à « <strong>{{ request('q') }}</strong> ». Essayez un autre mot-clé.
                            @else
                                Revenez bientôt — les prochaines annonces importantes de l'IAI-TOGO apparaîtront ici.
                            @endif
                        </p>
                        @if(request('q'))
                            <a href="{{ route('urgent.info') }}"
                               class="mt-6 text-sm font-semibold underline-offset-2 hover:underline"
                               style="color:var(--iai-green);">
                                Voir toutes les publications
                            </a>
                        @endif
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if($items->hasPages())
                <div class="mt-12 flex justify-center">
                    {{ $items->links() }}
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
