@extends("layouts.master")
@php use Illuminate\Support\Facades\Storage; @endphp

@section('title', $album->name . ' | Galerie')

@section('head')
<meta name="description" content="Découvrez l'album photo '{{ $album->name }}' de l'IAI-TOGO : {{ $album->description ?? 'photos et moments capturés' }}." />
<link rel="canonical" href="{{ url('/officiel/galerie/album/' . $album->id) }}" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
    
    body {
        font-family: 'Inter', sans-serif;
        scroll-behavior: smooth;
    }
    
    /* Animations personnalisées */
    @keyframes fadeInUp {
        from { 
            opacity: 0;
            transform: translateY(30px);
        }
        to { 
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes fadeInLeft {
        from { 
            opacity: 0;
            transform: translateX(-30px);
        }
        to { 
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    @keyframes fadeInRight {
        from { 
            opacity: 0;
            transform: translateX(30px);
        }
        to { 
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    @keyframes expand {
        from { transform: scaleX(0); }
        to { transform: scaleX(1); }
    }
    
    @keyframes pulseSlow {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    
    .animate-fade-in-up {
        animation: fadeInUp 1s ease-out forwards;
        opacity: 0;
    }
    
    .animate-fade-in-left {
        animation: fadeInLeft 1s ease-out forwards;
        opacity: 0;
    }
    
    .animate-fade-in-right {
        animation: fadeInRight 1s ease-out forwards;
        opacity: 0;
    }
    
    .animate-expand {
        animation: expand 1s ease-out forwards;
        transform: scaleX(0);
        transform-origin: center;
    }
    
    .animate-pulse-slow {
        animation: pulseSlow 2s ease-in-out infinite;
    }
    
    .animate-gallery-item {
        animation: fadeInUp 0.8s ease-out forwards;
        opacity: 0;
    }
    
    /* Masonry Grid */
    .masonry-grid {
        column-count: 1;
        column-gap: 1.5rem;
    }
    
    @media (min-width: 640px) {
        .masonry-grid {
            column-count: 2;
        }
    }
    
    @media (min-width: 1024px) {
        .masonry-grid {
            column-count: 3;
        }
    }
    
    @media (min-width: 1280px) {
        .masonry-grid {
            column-count: 4;
        }
    }
    
    .masonry-item {
        break-inside: avoid;
        margin-bottom: 1.5rem;
    }
    
    /* Gallery item styles */
    .gallery-item {
        position: relative;
        overflow: hidden;
        border-radius: 12px;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        cursor: pointer;
    }
    
    .gallery-img {
        width: 100%;
        height: auto;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    
    .gallery-item:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    
    .gallery-item:hover .gallery-img {
        transform: scale(1.1);
    }
    
    .gallery-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
        padding: 1.5rem;
        color: white;
        opacity: 0;
        transform: translateY(20px);
        transition: all 0.3s ease;
    }
    
    .gallery-item:hover .gallery-overlay {
        opacity: 1;
        transform: translateY(0);
    }
    
    .gallery-title {
        font-weight: 600;
        font-size: 1.1rem;
        margin-bottom: 0.5rem;
    }
    
    .gallery-category {
        font-size: 0.9rem;
        opacity: 0.9;
    }
    
    /* Modal styles */
    .gallery-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.95);
        z-index: 50;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .gallery-modal.active {
        display: flex;
        opacity: 1;
        align-items: center;
        justify-content: center;
    }
    
    .modal-content {
        position: relative;
        max-width: 90%;
        max-height: 90%;
        overflow: hidden;
    }
    
    .modal-img {
        max-width: 100%;
        max-height: 80vh;
        object-fit: contain;
        border-radius: 8px;
    }
    
    .modal-close {
        position: absolute;
        top: -40px;
        right: 0;
        background: #0D7A37;
        color: white;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-weight: bold;
        transition: background 0.3s ease;
    }
    
    .modal-close:hover {
        background: #fbef8b;
        color: #0D7A37;
    }
    
    .modal-nav {
        position: absolute;
        top: 50%;
        width: 100%;
        display: flex;
        justify-content: space-between;
        transform: translateY(-50%);
        padding: 0 20px;
    }
    
    .modal-nav-btn {
        background: rgba(13, 122, 55, 0.8);
        color: white;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .modal-nav-btn:hover {
        background: #fbef8b;
        color: #0D7A37;
        transform: scale(1.1);
    }
    
    /* Breadcrumb */
    .breadcrumb {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #6b7280;
        font-size: 0.95rem;
    }
    
    .breadcrumb a {
        color: #0D7A37;
        transition: color 0.2s;
    }
    
    .breadcrumb a:hover {
        color: #065a27;
    }
    
    .breadcrumb span {
        color: #374151;
    }
    
    /* Album info */
    .album-info {
        background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    
    /* Sidebar tabs */
    .side-tab.active { background: #f0fdf4; color: #0D7A37; }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .masonry-grid {
            column-count: 1;
        }
        
        .modal-nav-btn {
            width: 40px;
            height: 40px;
        }
        
        .album-info {
            padding: 1.5rem;
        }
    }
</style>
@endsection

@section('content')
<div>
    <!-- Hero Section -->
    <div class="bg-no-repeat bg-center bg-cover w-full"
         style="background-image: url({{ $album->cover_path ? Storage::url($album->cover_path) : 'https://images.unsplash.com/photo-1521587760476-6c12a4b040da?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1170&q=80' }})">
        <div class="h-[50vh] w-full flex items-center justify-center">
            <div class="w-full h-full text-white bg-gradient-to-r from-[#0D7A37]/90 to-[#fbef8b]/80 z-10 flex flex-col py-12 lg:py-0 gap-8 justify-center items-center animate-fade-in-up">
                <div class="flex flex-col items-center gap-4 text-center px-4">
                    <h1 class="text-white lg:text-4xl text-2xl font-bold uppercase">{{ $album->name }}</h1>
                    <span class="w-20 h-2 bg-[#fbef8b] animate-expand"></span>
                    @if($album->description)
                    <p class="text-lg max-w-3xl animate-fade-in-up" style="animation-delay: 0.3s;">
                        {{ $album->description }}
                    </p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Breadcrumb and album info -->
    <div class="bg-white py-8">
        <div class="container mx-auto px-4">
            <div class="breadcrumb mb-6 animate-fade-in-left">
                <a href="{{ route('home') }}">Accueil</a>
                <span>/</span>
                <a href="{{ route('galerie') }}">Galerie</a>
                <span>/</span>
                <span>{{ $album->name }}</span>
            </div>
            
            <div class="album-info animate-fade-in-right">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ $album->name }}</h2>
                        <p class="text-gray-600">{{ $photos->total() }} photo(s) dans cet album</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="px-4 py-2 bg-[#0D7A37] text-white rounded-full text-sm font-medium">
                            {{ $album->photos->count() }} photos
                        </span>
                        <a href="{{ route('galerie') }}" class="flex items-center text-[#0D7A37] hover:text-[#065a27] transition-colors">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Retour à la galerie
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Grille photos + Sidebar derniers albums -->
    <div class="bg-white py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- Sidebar: Derniers albums -->
                @if(isset($latestAlbums) && $latestAlbums->count())
                <aside class="lg:col-span-4 order-2 lg:order-1">
                    <div class="rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 bg-gray-50">
                            <h3 class="text-lg font-semibold text-gray-800">Derniers albums</h3>
                        </div>
                        <div class="flex flex-col md:flex-row lg:flex-col">
                            <div class="flex flex-col md:min-w-[240px] border-r lg:border-r-0 lg:border-b border-gray-100">
                                @foreach($latestAlbums as $la)
                                <button class="side-tab text-left px-5 py-4 hover:bg-gray-50 focus:outline-none {{ $loop->first ? 'active' : '' }}" data-target="side-pane-{{ $la->id }}">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $la->cover_path ? Storage::url($la->cover_path) : '/img/gallerie/galerie19.jpg' }}" alt="{{ $la->name }}" class="w-12 h-12 rounded object-cover">
                                        <div>
                                            <div class="font-medium text-gray-900 line-clamp-1">{{ $la->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $la->photos_count }} photo(s)</div>
                                        </div>
                                    </div>
                                </button>
                                @endforeach
                            </div>
                            <div class="flex-1">
                                @foreach($latestAlbums as $la)
                                <div id="side-pane-{{ $la->id }}" class="side-pane p-5 {{ $loop->first ? 'block' : 'hidden' }}">
                                    <div class="rounded-lg overflow-hidden mb-4">
                                        <img src="{{ $la->cover_path ? Storage::url($la->cover_path) : '/img/gallerie/galerie19.jpg' }}" alt="{{ $la->name }}" class="w-full h-44 object-cover">
                                    </div>
                                    <h4 class="text-xl font-semibold text-gray-900 mb-1">{{ $la->name }}</h4>
                                    @if($la->description)
                                        <p class="text-gray-600 mb-3 line-clamp-3">{{ $la->description }}</p>
                                    @endif
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-500">{{ $la->photos_count }} photo(s)</span>
                                        <a href="{{ route('galerie.album', $la) }}" class="inline-flex items-center gap-2 text-[#0D7A37] hover:text-[#065a27] font-medium">
                                            Voir l'album
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                        </a>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </aside>
                @endif

                <!-- Contenu principal: Photos de l'album courant -->
                <section class="lg:col-span-8 order-1 lg:order-2">
                    @if($photos->count())
                    <div class="masonry-grid">
                        @foreach($photos as $index => $p)
                        <div class="masonry-item gallery-item animate-gallery-item" style="animation-delay: {{ $index * 0.1 }}s">
                            <img class="gallery-img" src="{{ Storage::url($p->file_path) }}" alt="{{ $p->alt_text ?? $p->title ?? 'Photo' }}">
                            <div class="gallery-overlay">
                                <div class="gallery-title">{{ $p->title ?? 'Photo' }}</div>
                                <div class="gallery-category">{{ $album->name }}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="mt-12 flex justify-center">
                        {{ $photos->onEachSide(1)->links('vendor.pagination.tailwind') }}
                    </div>
                    @else
                    <div class="text-center py-12">
                        <div class="text-gray-400 text-6xl mb-4">
                            <i class="fas fa-camera"></i>
                        </div>
                        <h3 class="text-xl font-medium text-gray-500 mb-2">Aucune photo dans cet album</h3>
                        <p class="text-gray-400">Revenez plus tard pour découvrir les photos de cet album.</p>
                    </div>
                    @endif
                </section>
            </div>
        </div>
    </div>

    <!-- Modal pour afficher les images en grand -->
    <div class="gallery-modal" id="galleryModal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal()">&times;</span>
            <img class="modal-img" id="modalImage" src="" alt="">
            <div class="modal-nav">
                <div class="modal-nav-btn" onclick="navigateModal(-1)">&#10094;</div>
                <div class="modal-nav-btn" onclick="navigateModal(1)">&#10095;</div>
            </div>
        </div>
    </div>

    <!-- Call to Action -->
    <div class="bg-gradient-to-r from-[#0D7A37] to-[#065a27] py-16 text-white">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold mb-4">Découvrez nos autres albums</h2>
            <p class="text-lg mb-8 max-w-2xl mx-auto">Explorez tous nos albums photos et revivez les moments forts de l'IAI-TOGO.</p>
            <a href="{{ route('galerie') }}" class="inline-block px-8 py-4 bg-white text-[#0D7A37] font-semibold rounded-lg shadow-lg hover:bg-gray-100 transition-all duration-300 transform hover:scale-105">
                Voir tous les albums
            </a>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gestion du clic sur les images pour ouvrir le modal
        document.querySelectorAll('.gallery-item').forEach((item, index) => {
            item.addEventListener('click', function() {
                const imgSrc = this.querySelector('img').src;
                const title = this.querySelector('.gallery-title')?.textContent || '';
                openModal(imgSrc, index, title);
            });
        });
        
        // Animation des images lorsqu'elles entrent dans le viewport
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animationPlayState = 'running';
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });
        
        document.querySelectorAll('.animate-gallery-item').forEach(item => {
            item.style.animationPlayState = 'paused';
            observer.observe(item);
        });
    });
    
    // Variables globales pour la navigation dans le modal
    let currentIndex = 0;
    let allImages = [];
    let allTitles = [];
    
    function openModal(imgSrc, index, title) {
        currentIndex = index;
        
        // Récupérer toutes les images de l'album
        allImages = Array.from(document.querySelectorAll('.gallery-img'))
            .map(img => img.src);
            
        // Récupérer tous les titres des images
        allTitles = Array.from(document.querySelectorAll('.gallery-title'))
            .map(titleEl => titleEl.textContent);
        
        document.getElementById('modalImage').src = imgSrc;
        document.getElementById('modalImage').alt = title;
        document.getElementById('galleryModal').classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    function closeModal() {
        document.getElementById('galleryModal').classList.remove('active');
        document.body.style.overflow = 'auto';
    }
    
    function navigateModal(direction) {
        currentIndex += direction;
        
        // Gérer les limites
        if (currentIndex < 0) {
            currentIndex = allImages.length - 1;
        } else if (currentIndex >= allImages.length) {
            currentIndex = 0;
        }
        
        document.getElementById('modalImage').src = allImages[currentIndex];
        document.getElementById('modalImage').alt = allTitles[currentIndex] || '';
    }
    
    // Fermer le modal en cliquant à l'extérieur
    document.getElementById('galleryModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });
    
    // Navigation au clavier
    document.addEventListener('keydown', function(e) {
        if (document.getElementById('galleryModal').classList.contains('active')) {
            if (e.key === 'Escape') {
                closeModal();
            } else if (e.key === 'ArrowLeft') {
                navigateModal(-1);
            } else if (e.key === 'ArrowRight') {
                navigateModal(1);
            }
        }
    });

    // Sidebar latest albums tab switching
    (function(){
        const tabs = document.querySelectorAll('.side-tab');
        const panes = document.querySelectorAll('.side-pane');
        if (!tabs.length) return;
        tabs.forEach(tab => {
            tab.addEventListener('click', function(){
                const targetId = this.getAttribute('data-target');
                tabs.forEach(t => t.classList.remove('active'));
                panes.forEach(p => p.classList.add('hidden'));
                this.classList.add('active');
                const pane = document.getElementById(targetId);
                if (pane) pane.classList.remove('hidden');
            });
        });
    })();
</script>
@endsection