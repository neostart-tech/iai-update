@php use Illuminate\Support\Facades\Storage; @endphp
@extends('layouts.master')

@section('content')
    <div>
        <!-- Hero Section (aligné sur la page formations) -->
        <section class="relative h-[60vh] md:h-[70vh] flex items-center justify-center overflow-hidden">
            <div class="absolute inset-0 z-0">
                <div class="absolute inset-0 bg-gradient-to-r from-[#0D7A37]/90 to-[#fbef8b]/80 z-10"></div>
                <div class="absolute inset-0 bg-gray-900 opacity-60 z-20"></div>
                <img 
                    src="{{ $blog->image ? Storage::disk('public')->url($blog->image) : asset('img/IAI_Logo.png') }}"
                    alt="{{ $blog->title }}" 
                    class="w-full h-full object-cover transform scale-110 animate-zoom-out"
                >
            </div>

            <div class="relative z-30 text-center text-white max-w-4xl mx-auto px-4">
                <h1 class="text-3xl md:text-5xl font-bold mb-4 animate-fade-in">{{ $blog->title }}</h1>
                <div class="w-24 h-1 bg-[#fbef8b] mx-auto mb-4 animate-expand"></div>
                <p class="text-base md:text-lg opacity-90 animate-fade-in-delayed">
                    Publié le {{ $blog->publication_date->translatedFormat('d F Y') }} · Par {{ $blog->author_display_name }}
                </p>
            </div>
        </section>
        <div class="bg-white mt-10 px-4 pb-8">
            <div class="container mx-auto">
                <div class="flex flex-col lg:flex-row gap-8 lg:gap-12">
                    <!-- Contenu principal -->
                    <div class="flex-1 min-w-0 animate-fade-in-up">
                        <!-- Meta auteur / date / nb commentaires -->
                        <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600 mb-4">
                            <div class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span>Auteur: <strong>{{ $blog->author_display_name }}</strong></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Publié le {{ $blog->publication_date->translatedFormat('d F Y') }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v8a2 2 0 01-2 2H7l-4 4V10a2 2 0 012-2h2"/></svg>
                                <span>{{ $blog->comments_count ?? $blog->comments()->count() }} commentaire(s)</span>
                            </div>
                        </div>

                        <!-- Contenu HTML -->
                        <div class="prose max-w-none">
                            <x-cgu title="" content="{!! $blog->content !!}" />
                        </div>

                        <!-- Liste des commentaires -->
                        <div class="mt-10">
                            <h2 class="text-xl font-semibold mb-4">Commentaires ({{ $blog->comments_count ?? $blog->comments()->count() }})</h2>
                            <div class="space-y-6">
                                @forelse($blog->comments as $comment)
                                    <div class="border border-gray-200 rounded-lg p-4">
                                        <div class="flex items-center justify-between text-sm text-gray-500 mb-2">
                                            <span class="font-medium">{{ $comment->author_name }}</span>
                                            <span>{{ $comment->created_at->translatedFormat('d M Y H:i') }}</span>
                                        </div>
                                        <p class="text-gray-800">{!! nl2br(e($comment->content)) !!}</p>
                                    </div>
                                @empty
                                    <p class="text-gray-500">Aucun commentaire pour le moment. Soyez le premier à commenter.</p>
                                @endforelse
                            </div>
                        </div>

                        <!-- Formulaire d'ajout de commentaire -->
                        <div class="mt-8">
                            <h3 class="text-lg font-semibold mb-4">Laisser un commentaire</h3>
                            @if(session('success'))
                                <div class="mb-4 p-3 rounded bg-green-100 text-green-700">{{ session('success') }}</div>
                            @endif
                            @if($errors->any())
                                <div class="mb-4 p-3 rounded bg-red-100 text-red-700">
                                    <ul class="list-disc list-inside">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <form action="{{ route('blogs.comment', $blog) }}" method="post" class="space-y-4">
                                @csrf
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="author_name" class="block text-sm font-medium text-gray-700">Nom</label>
                                        <input type="text" id="author_name" name="author_name" value="{{ old('author_name') }}" class="mt-1 w-full border-2 border-black rounded-md focus:ring-0 focus:border-black" required>
                                    </div>
                                    <div>
                                        <label for="author_email" class="block text-sm font-medium text-gray-700">Email (optionnel)</label>
                                        <input type="email" id="author_email" name="author_email" value="{{ old('author_email') }}" class="mt-1 w-full border-2 border-black rounded-md focus:ring-0 focus:border-black">
                                    </div>
                                </div>
                                <div>
                                    <label for="content" class="block text-sm font-medium text-gray-700">Commentaire</label>
                                    <textarea id="content" name="content" rows="4" class="mt-1 w-full border-2 border-black rounded-md focus:ring-0 focus:border-black" required>{{ old('content') }}</textarea>
                                </div>
                                <button class="bg-green-700 hover:bg-green-800 text-white font-semibold px-4 py-2 rounded-md">Publier</button>
                            </form>
                        </div>
                    </div>

                    <!-- Sidebar droite -->
                    <aside class="w-full lg:w-96 flex-shrink-0 space-y-8 animate-fade-in-right">
                        <!-- Recherche -->
                        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
                            <h3 class="text-lg font-semibold mb-3">Rechercher</h3>
                            <form action="{{ route('blogs.index') }}" method="get" class="flex gap-2">
                                <input type="text" name="q" value="{{ request('q') }}" placeholder="Titre, contenu..." class="flex-1 border border-gray-300 rounded-md px-3 py-2 focus:ring-green-500 focus:border-green-500" />
                                <button class="bg-green-700 hover:bg-green-800 text-white px-4 py-2 rounded-md">Chercher</button>
                            </form>
                        </div>

                        <!-- Récentes actualités -->
                        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
                            <h3 class="text-lg font-semibold mb-4">Actualités récentes</h3>
                            <div class="space-y-4">
                                @foreach(($recentBlogs ?? []) as $recent)
                                    <a href="{{ route('blogs.show', $recent) }}" class="flex gap-3 group">
                                        <img src="{{ $recent->image ? Storage::disk('public')->url($recent->image) : asset('img/IAI_Logo.png') }}" alt="{{ $recent->title }}" class="w-16 h-16 object-cover rounded-md border" />
                                        <div class="min-w-0">
                                            <p class="font-medium text-gray-800 group-hover:text-green-700 line-clamp-2">{{ $recent->title }}</p>
                                            <p class="text-xs text-gray-500">{{ $recent->publication_date->translatedFormat('d M Y') }}</p>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </aside>
                </div>
            </div>
        </div>
        <!-- Animations alignées avec la page formation -->
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