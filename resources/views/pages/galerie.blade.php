@extends("layouts.master")
@php use Illuminate\Support\Facades\Storage; @endphp
@section('title', 'Galerie')

@section('head')
<meta name="description" content="Découvrez la galerie photo officielle: albums, événements et vie sur le campus." />
<link rel="canonical" href="{{ url('/officiel/galerie') }}" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
    
    body {
        font-family: 'Inter', sans-serif;
        scroll-behavior: smooth;
    }
    
    /* Animation pour le texte IAI-TOGO */
    .iai-animation {
        color: #0D7A37;
        font-family: Avenir Next, Helvetica Neue, Helvetica, Tahoma, sans-serif;
        font-weight: 700;
        background-color: white;
        padding: 20px 0;
        margin: 0;
        display: flex;
        justify-content: center;
    }
    
    .iai-animation span {
        display: inline-block;
        position: relative;
        transform-style: preserve-3d;
        perspective: 500;
        -webkit-font-smoothing: antialiased;
    }
    
    .iai-animation span::before,
    .iai-animation span::after {
        display: none;
        position: absolute;
        top: 0;
        left: -1px;
        transform-origin: left top;
        transition: all ease-out 0.3s;
        content: attr(data-text);
    }
    
    .iai-animation span::before {
        z-index: 1;
        color: rgba(0,0,0,0.2);
        transform: scale(1.1, 1) skew(0deg, 20deg);
    }
    
    .iai-animation span::after {
        z-index: 2;
        color: #0D7A37;
        text-shadow: -1px 0 1px #fbef8b, 1px 0 1px rgba(0,0,0,0.8);
        transform: rotateY(-40deg);
    }
    
    .iai-animation span:hover::before {
        transform: scale(1.1, 1) skew(0deg, 5deg);
    }
    
    .iai-animation span:hover::after {
        transform: rotateY(-10deg);
    }
    
    .iai-animation span + span {
        margin-left: 0.3em;
    }
    
    @media (min-width: 20em) {
        .iai-animation {
            font-size: 2em;
        }
        .iai-animation span::before,
        .iai-animation span::after {
            display: block;
        }
    }
    
    @media (min-width: 30em) {
        .iai-animation {
            font-size: 3em;
        }
    }
    
    @media (min-width: 40em) {
        .iai-animation {
            font-size: 5em;
        }
    }
    
    @media (min-width: 60em) {
        .iai-animation {
            font-size: 8em;
        }
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
    
    @keyframes bounceSlow {
        0%, 20%, 50%, 80%, 100% {transform: translateY(0);}
        40% {transform: translateY(-20px);}
        60% {transform: translateY(-10px);}
    }
    
    @keyframes zoomOut {
        from { transform: scale(1.1); }
        to { transform: scale(1); }
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
    
    .animate-bounce-slow {
        animation: bounceSlow 2s infinite;
    }
    
    .animate-zoom-out {
        animation: zoomOut 20s ease-in-out infinite alternate;
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
    
    /* Tab styles */
    .tab-button {
        padding: 0.75rem 1.5rem;
        background: white;
        border: none;
        font-weight: 500;
        color: #4b5563;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .tab-button.active {
        color: #0D7A37;
        border-bottom: 2px solid #0D7A37;
    }
    
    .tab-button:hover {
        color: #0D7A37;
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
    
    /* View all button */
    .view-all-btn {
        display: block;
        margin: 2rem auto;
        padding: 1rem 2.5rem;
        background: #0D7A37;
        color: white;
        border: none;
        border-radius: 50px;
        font-weight: 600;
        font-size: 1.1rem;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 6px rgba(13, 122, 55, 0.3);
    }
    
    .view-all-btn:hover {
        background: #fbef8b;
        color: #0D7A37;
        transform: translateY(-3px);
        box-shadow: 0 10px 15px rgba(13, 122, 55, 0.4);
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .masonry-grid {
            column-count: 1;
        }
        
        .tab-buttons {
            overflow-x: auto;
            white-space: nowrap;
            padding-bottom: 1rem;
        }
        
        .modal-nav-btn {
            width: 40px;
            height: 40px;
        }
    }
</style>
@endsection

@section('content')
<div>
     <!-- Hero Section -->
     <div class="bg-no-repeat bg-center bg-cover w-full"
         style="background-image: url(https://images.unsplash.com/photo-1521587760476-6c12a4b040da?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1170&q=80)">
        <div class="h-[60vh] w-full flex items-center justify-center">
            <div class="w-full h-full text-white bg-gradient-to-r from-[#0D7A37]/90 to-[#fbef8b]/80 z-10 flex flex-col py-12 lg:py-0 gap-8 justify-center items-center animate-fade-in-up">
                <div class="flex flex-col items-center gap-4 text-center">
                    <h1 class="text-white lg:text-5xl text-lg font-bold uppercase">Galerie</h1>
                    <span class="w-20 h-2 bg-[#fbef8b] animate-expand"></span>
                    <h1 class="text-xl font-bold animate-fade-in-up" style="animation-delay: 0.3s;">
                        Ici, vous pourrez découvrir l'ambiance de notre école au quotidien.
                        Que ce soit en classe, lors d'activités <br> pédagogiques ou de loisirs,
                        vous pourrez voir nos élèves et enseignants en action.
                    </h1>
                </div>

                <a href="{{ route('contact') }}" 
                   class="inline-block px-8 py-4 bg-[#0D7A37] text-white font-semibold rounded-lg shadow-lg hover:bg-green-700 transition-all duration-300 transform hover:scale-105 animate-pulse-slow">
                   Nous Contacter
                </a>
            </div>
        </div>
    </div>

    @if(isset($albums) && $albums->count())
    <!-- Galerie dynamique depuis la base de données -->
    <div class="bg-white py-16">
        <div class="container mx-auto px-4"> 
            <div class="text-center max-w-3xl mx-auto mb-12">
                <h2 class="mb-6 text-3xl lg:text-4xl font-extrabold text-gray-900 animate-fade-in-up">Notre Galerie</h2>
                <p class="text-gray-600 animate-fade-in-up" style="animation-delay: 0.15s;">Découvrez nos albums et quelques photos récentes.</p>
            </div>

            <!-- Featured Albums -->
            <section class="mb-16">
                <h3 class="text-2xl font-bold text-center mb-8">Albums à la une</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($albums->take(4) as $album)
                    <div class="bg-white rounded-xl shadow-md overflow-hidden transition-transform duration-300 hover:shadow-xl hover:-translate-y-2">
                        <div class="h-48 overflow-hidden">
                            <img src="{{ $album->cover_path ? Storage::url($album->cover_path) : '/img/gallerie/galerie19.jpg' }}" 
                                 alt="{{ $album->name }}" class="w-full h-full object-cover">
                        </div>
                        <div class="p-5">
                            <h3 class="text-xl font-semibold mb-2">{{ $album->name }}</h3>
                            <p class="text-gray-600 mb-4">{{ $album->photos->count() }} photo(s)</p>
                            <a href="{{ route('galerie.album', $album) }}" class="text-primary font-medium hover:underline">Voir l'album →</a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>

            <!-- Gallery Tabs -->
            <section class="mb-16">
                <div class="flex flex-wrap border-b border-gray-200 mb-8">
                    <button class="tab-button active px-6 py-3 text-lg font-medium border-b-2 border-primary text-primary" data-tab="all">
                        Toutes les photos
                    </button>
                    @foreach($albums as $album)
                    <button class="tab-button px-6 py-3 text-lg font-medium text-gray-600 hover:text-primary" data-tab="album-{{ $album->id }}">
                        {{ $album->name }}
                    </button>
                    @endforeach
                </div>
                
                <!-- Gallery Grid -->
                <div class="masonry-grid">
                    @foreach($albums as $album)
                        @foreach($album->photos->take(8) as $photo)
                        <div class="masonry-item gallery-item" data-category="album-{{ $album->id }}" data-index="{{ $loop->index }}">
                            <img class="gallery-img" src="{{ Storage::url($photo->file_path) }}" alt="{{ $photo->alt_text ?? $photo->title ?? 'Photo' }}">
                            <div class="gallery-overlay">
                                <div class="gallery-title">{{ $photo->title ?? 'Photo' }}</div>
                                <div class="gallery-category">{{ $album->name }}</div>
                            </div>
                        </div>
                        @endforeach
                    @endforeach
                </div>
            </section>
        </div>
    </div>
    @endif

    @if(isset($photos))
    <!-- Section: Photos paginées avec filtre -->
    <div id="all-photos" class="bg-white py-12">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-8">
                <div>
                    <h3 class="text-2xl font-bold mb-2">Toutes les photos</h3>
                    <p class="text-gray-600">Filtrez par album et parcourez la galerie.</p>
                </div>
                <form method="GET" action="{{ route('galerie') }}" class="flex items-center gap-3">
                    <label for="album" class="text-sm text-gray-600">Album</label>
                    <select id="album" name="album" class="border rounded px-3 py-2">
                        <option value="">— Tous les albums —</option>
                        @foreach(($albums ?? collect()) as $alb)
                            <option value="{{ $alb->id }}" {{ (string)$albumId === (string)$alb->id ? 'selected' : '' }}>{{ $alb->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded hover:bg-green-700">Filtrer</button>
                </form>
            </div>

            <div class="masonry-grid">
                @forelse($photos as $p)
                    <div class="masonry-item gallery-item">
                        <img class="gallery-img" src="{{ Storage::url($p->file_path) }}" alt="{{ $p->alt_text ?? $p->title ?? 'Photo' }}">
                        <div class="gallery-overlay">
                            <div class="gallery-title">{{ $p->title ?? 'Photo' }}</div>
                            <div class="gallery-category">{{ optional($p->album)->name }}</div>
                        </div>
                    </div>
                @empty
                    <div class="text-gray-500">Aucune photo à afficher.</div>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $photos->onEachSide(1)->links() }}
            </div>
        </div>
    </div>
    @endif

   

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

    <!-- Signature IAI-TOGO avec fond blanc -->
    <div class="bg-white py-12">
        <div class="iai-animation animate-fade-in-up" style="animation-delay: 0.5s;">
            <span data-text="I">I</span>
            <span data-text="A">A</span>
            <span data-text="I">I</span>
            <span data-text="-">-</span>
            <span data-text="T">T</span>
            <span data-text="O">O</span>
            <span data-text="G">G</span>
            <span data-text="O">O</span>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tab functionality
        const tabButtons = document.querySelectorAll('.tab-button');
        const galleryItems = document.querySelectorAll('.gallery-item');
        
        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                // Remove active class from all buttons
                tabButtons.forEach(btn => {
                    btn.classList.remove('active', 'border-primary', 'text-primary');
                    btn.classList.add('text-gray-600');
                });
                
                // Add active class to clicked button
                button.classList.add('active', 'border-primary', 'text-primary');
                button.classList.remove('text-gray-600');
                
                // Filter gallery items
                const category = button.getAttribute('data-tab');
                
                galleryItems.forEach(item => {
                    if (category === 'all' || item.getAttribute('data-category') === category) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
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
        
        // Gestion du clic sur les images pour ouvrir le modal
        document.querySelectorAll('.gallery-item').forEach(item => {
            item.addEventListener('click', function() {
                const imgSrc = this.querySelector('img').src;
                const category = this.getAttribute('data-category');
                const index = this.getAttribute('data-index');
                openModal(imgSrc, category, index);
            });
        });
    });
    
    // Variables globales pour la navigation dans le modal
    let currentCategory = '';
    let currentIndex = 0;
    let allImages = [];
    
    function openModal(imgSrc, category, index) {
        currentCategory = category;
        currentIndex = parseInt(index);
        
        // Récupérer toutes les images de la catégorie actuelle
        allImages = Array.from(document.querySelectorAll(`.gallery-item[data-category="${category}"]`))
            .map(item => item.querySelector('img').src);
        
        document.getElementById('modalImage').src = imgSrc;
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
</script>
@endsection