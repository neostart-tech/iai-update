@extends('layouts.master')

@section('content')
<div>
  <!-- Hero Section moderne -->
  <section class="relative h-[50vh] md:h-[60vh] flex items-center justify-center overflow-hidden">
    <div class="absolute inset-0 z-0">
      <div class="absolute inset-0 bg-gradient-to-r from-[#0D7A37]/90 to-[#fbef8b]/80 z-10"></div>
      <div class="absolute inset-0 bg-gray-900 opacity-60 z-20"></div>
      <img src="/img/IMG_4667.jpg" alt="Actualité" class="w-full h-full object-cover transform scale-110 animate-zoom-out" />
    </div>
    <div class="relative z-30 text-center text-white max-w-4xl mx-auto px-4">
      <h1 class="text-3xl md:text-5xl font-bold mb-4 animate-fade-in">{{ $event->nom }}</h1>
      <div class="w-24 h-1 bg-[#fbef8b] mx-auto mb-4 animate-expand"></div>
      <p class="text-base md:text-lg opacity-90 animate-fade-in-delayed">
        {{ $event->location ?? 'En ligne' }} · {{ $datePublication ? $datePublication->translatedFormat('d F Y') : '' }}
      </p>
    </div>
  </section>

  <!-- Corps -->
  <div class="bg-white mt-10 px-4 pb-8">
    <div class="container mx-auto">
      <div class="flex flex-col lg:flex-row gap-8 lg:gap-12">
        <!-- Colonne principale -->
        <div class="flex-1 min-w-0 animate-fade-in-up">
          <!-- Meta auteur / date / nb commentaires -->
          <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600 mb-4">
            <div class="flex items-center gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
              <span>Auteur: <strong>{{ $auteur->name ?? 'Inconnu' }}</strong></span>
            </div>
            <div class="flex items-center gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              <span>Publié le {{ $datePublication ? $datePublication->translatedFormat('d F Y') : '' }}</span>
            </div>
            <div class="flex items-center gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v8a2 2 0 01-2 2H7l-4 4V10a2 2 0 012-2h2"/></svg>
              <span>{{ $nombreCommentaires }} commentaire(s)</span>
            </div>
          </div>

          <!-- Contenu -->
          <div class="prose max-w-none">
            <x-cgu title="" content="{!! $event->details !!}" />
          </div>

          <!-- Commentaires -->
          <div class="mt-10">
            <h2 class="text-xl font-semibold mb-4">Commentaires ({{ $nombreCommentaires }})</h2>
            <div class="space-y-6">
              @forelse($commentaires as $comment)
                <div class="border border-gray-200 rounded-lg p-4">
                  <div class="flex items-center justify-between text-sm text-gray-500 mb-2">
                    <span class="font-medium">{{ $comment->user->name ?? 'Anonyme' }}</span>
                    <span>{{ $comment->created_at->translatedFormat('d M Y H:i') }}</span>
                  </div>
                  <p class="text-gray-800">{!! nl2br(e($comment->content)) !!}</p>
                </div>
              @empty
                <p class="text-gray-500">Aucun commentaire pour le moment. Soyez le premier à commenter.</p>
              @endforelse
            </div>
          </div>

          <!-- Formulaire commentaire -->
          <div class="mt-8">
            <h3 class="text-lg font-semibold mb-4">Laisser un commentaire</h3>
            <form method="POST" action="{{ route('events.comment', $event->id) }}" class="space-y-4">
              @csrf
              <div>
                <label for="content" class="block text-sm font-medium text-gray-700">Commentaire</label>
                <textarea id="content" name="content" rows="4" class="mt-1 w-full border-2 border-black rounded-md focus:ring-0 focus:border-black" placeholder="Votre commentaire..." required></textarea>
              </div>
              <button type="submit" class="bg-green-700 hover:bg-green-800 text-white font-semibold px-4 py-2 rounded-md">Publier</button>
            </form>
          </div>
        </div>

        <!-- Sidebar -->
        <aside class="w-full lg:w-96 flex-shrink-0 space-y-8 animate-fade-in-right">
          <!-- Recherche -->
          <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <h3 class="text-lg font-semibold mb-3">Rechercher une actualité</h3>
            <form method="GET" action="{{ route('events.search') }}" class="flex gap-2">
              <input type="text" name="q" value="{{ request('q') }}" placeholder="Titre, contenu..." class="flex-1 border-2 border-black rounded-md px-3 py-2 focus:ring-0 focus:border-black" />
              <button type="submit" class="bg-green-700 hover:bg-green-800 text-white px-4 py-2 rounded-md">Chercher</button>
            </form>
          </div>

          <!-- Récentes actualités -->
          <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <h3 class="text-lg font-semibold mb-4">Actualités récentes</h3>
            <div class="space-y-4">
              @foreach($recentEvents as $recent)
                <a href="{{ route('events.show', $recent->id) }}" class="flex gap-3 group">
                  <div class="w-16 h-16 rounded-md bg-gray-100 border flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                  </div>
                  <div class="min-w-0">
                    <p class="font-medium text-gray-800 group-hover:text-green-700 line-clamp-2">{{ $recent->nom }}</p>
                    <p class="text-xs text-gray-500">{{ $recent->publication_date ? \Carbon\Carbon::parse($recent->publication_date)->translatedFormat('d M Y') : '' }}</p>
                  </div>
                </a>
              @endforeach
            </div>
          </div>
        </aside>
      </div>
    </div>
  </div>

  <!-- Animations -->
  <style>
    .animate-fade-in { animation: fadeIn 1s ease-out forwards; opacity: 0; }
    .animate-fade-in-delayed { animation: fadeIn 1s ease-out forwards; opacity: 0; animation-delay: .6s; }
    .animate-expand { animation: expand 1s ease-out forwards; transform: scaleX(0); transform-origin: center; }
    .animate-zoom-out { animation: zoomOut 20s ease-in-out infinite alternate; }
    .animate-fade-in-up { animation: fadeInUp .8s ease-out forwards; opacity: 0; }
    .animate-fade-in-right { animation: fadeInRight .8s ease-out forwards; opacity: 0; }

    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes fadeInRight { from { opacity: 0; transform: translateX(20px); } to { opacity: 1; transform: translateX(0); } }
    @keyframes zoomOut { from { transform: scale(1.1); } to { transform: scale(1); } }
    @keyframes expand { from { transform: scaleX(0); } to { transform: scaleX(1); } }
  </style>
</div>
@endsection