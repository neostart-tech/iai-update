@extends("layouts.master")

@section('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap');
    
    :root {
        --primary-green: #0D7A37;
        --secondary-gold: #b09d72;
        --light-bg: #f8fafc;
        --dark-text: #1A1943;
    }
    
    body {
        font-family: 'Montserrat', sans-serif;
        scroll-behavior: smooth;
    }
    
    .hero-gradient {
        background: linear-gradient(135deg, rgba(13, 122, 55, 0.9) 0%, rgba(176, 157, 114, 0.8) 100%);
    }
    
    .card-hover {
        transition: all 0.3s ease;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    }
    
    .card-hover:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    
    .btn-primary {
        background: var(--primary-green);
        color: white;
        transition: all 0.3s ease;
        border: 2px solid var(--primary-green);
    }
    
    .btn-primary:hover {
        background: transparent;
        color: var(--primary-green);
        transform: translateY(-2px);
    }
    
    .btn-secondary {
        background: var(--secondary-gold);
        color: white;
        transition: all 0.3s ease;
        border: 2px solid var(--secondary-gold);
    }
    
    .btn-secondary:hover {
        background: transparent;
        color: var(--secondary-gold);
        transform: translateY(-2px);
    }
    
    .btn-outline {
        background: transparent;
        color: white;
        border: 2px solid white;
        transition: all 0.3s ease;
    }
    
    .btn-outline:hover {
        background: white;
        color: var(--primary-green);
    }
    
    .section-title {
        position: relative;
        display: inline-block;
        margin-bottom: 3rem;
        font-weight: 700;
    }
    
    .section-title:after {
        content: '';
        position: absolute;
        width: 50%;
        height: 4px;
        background: var(--secondary-gold);
        bottom: -15px;
        left: 0;
        border-radius: 2px;
    }
    
    .center-title:after {
        left: 50%;
        transform: translateX(-50%);
    }
    
    .testimonial-card {
        position: relative;
        background: white;
        padding: 40px;
        border-radius: 12px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        border-left: 4px solid var(--primary-green);
    }
    
    .testimonial-card p {
        margin: 0;
        color: #4a5568;
        font-style: italic;
        line-height: 1.8;
    }
    
    .testimonial-icon:before {
        content: "\f10d";
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        font-size: 24px;
        color: var(--primary-green);
        position: absolute;
        top: 20px;
        left: 20px;
    }
    
    .testimonial-icon:after {
        content: "\f10e";
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        font-size: 24px;
        color: var(--primary-green);
        position: absolute;
        bottom: 20px;
        right: 20px;
    }
    
    .floating-shape {
        position: absolute;
        border-radius: 50%;
        opacity: 0.1;
        z-index: 0;
    }
    
    .shape-1 {
        width: 300px;
        height: 300px;
        background: var(--primary-green);
        top: -150px;
        right: -150px;
    }
    
    .shape-2 {
        width: 200px;
        height: 200px;
        background: var(--secondary-gold);
        bottom: -100px;
        left: -100px;
    }
    
    .stat-card {
        transition: all 0.3s ease;
        border-bottom: 4px solid transparent;
    }
    
    .stat-card:hover {
        border-bottom: 4px solid var(--primary-green);
        transform: translateY(-5px);
    }
    
    .program-card {
        overflow: hidden;
        position: relative;
        height: 300px;
    }
    
    .program-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(to bottom, transparent 0%, rgba(13, 122, 55, 0.9) 100%);
        opacity: 0;
        transition: opacity 0.3s ease;
        z-index: 1;
    }
    
    .program-card:hover::before {
        opacity: 1;
    }
    
    .program-content {
        position: absolute;
        bottom: 0;
        left: 0;
        padding: 20px;
        color: white;
        transform: translateY(100%);
        transition: transform 0.3s ease;
        z-index: 2;
        width: 100%;
    }
    
    .program-card:hover .program-content {
        transform: translateY(0);
    }
    
    .partner-logo {
        filter: grayscale(100%);
        opacity: 0.7;
        transition: all 0.3s ease;
    }
    
    .partner-logo:hover {
        filter: grayscale(0);
        opacity: 1;
        transform: scale(1.05);
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .animate-fade-in {
        animation: fadeIn 1s ease-out forwards;
    }
    
    .carousel-control-custom {
        background: rgba(255, 255, 255, 0.3);
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }
    
    .carousel-control-custom:hover {
        background: rgba(255, 255, 255, 0.7);
    }
    
    /* Carousel styles */
    .carousel-container {
        position: relative;
        overflow: hidden;
        width: 100%;
    }
    
    .carousel-track {
        display: flex;
        transition: transform 0.5s ease;
    }
    
    .carousel-slide {
        min-width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .hero-content {
            padding: 2rem 1rem;
        }
        
        .section-title {
            font-size: 1.75rem;
        }
        
        .testimonial-card {
            padding: 20px;
        }
        
        .program-card {
            height: 250px;
        }
    }
</style>
@endsection

@section('content')
<!-- Hero Banner with Carousel -->
<div class="relative w-full h-[70vh] md:h-[80vh] overflow-hidden bg-gray-900">
    <div class="relative h-full w-full" id="hero-carousel">
		
        <!-- Carousel wrapper -->
        <div class="relative h-full overflow-hidden">
            <!-- Item 1 -->
            <div class="duration-700 ease-in-out absolute inset-0 transition-all transform" data-carousel-item="active">
                <div class="flex flex-col lg:flex-row h-full">
                    <div class="w-full lg:w-1/2 h-full flex flex-col justify-center items-center px-4 md:px-8 py-12 lg:py-0 bg-gradient-to-br from-green-800 to-emerald-900 text-white">
                        <div class="bg-white/10 p-5 rounded-full backdrop-blur-sm mb-6">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 md:w-20 md:h-20 text-white">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"/>
                            </svg>
                        </div>

                        <div class="text-center mb-8">
                            <h1 class="text-2xl md:text-3xl lg:text-4xl font-bold mb-4">IAI TOGO</h1>
                            <div class="w-16 h-1 bg-amber-400 rounded-full mx-auto mb-4"></div>
                            <p class="text-lg md:text-xl max-w-md mx-auto">La référence en matière de formation informatique</p>
                        </div>

                        <a href="{{ route('contact') }}" class="bg-amber-500 hover:bg-amber-600 text-white font-bold py-3 px-8 rounded-full transition-all duration-300 transform hover:scale-105">
                            Nous Contacter
                        </a>
                    </div>

                    <div class="hidden lg:block lg:w-1/2 h-full relative">
                        <img src="/img/IMG_9271111.jpg" alt="IAI Togo" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-r from-green-900/70 to-emerald-800/50 flex items-center justify-center">
                            <div class="text-center text-white p-8">
                                <h2 class="text-3xl md:text-4xl font-bold mb-4">INSTITUT AFRICAIN D'INFORMATIQUE</h2>
                                <div class="w-16 h-1 bg-amber-400 rounded-full mx-auto mb-4"></div>
                                <p class="text-xl font-semibold bg-red-600/90 px-4 py-2 inline-block">REPRÉSENTATION DU TOGO</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Item 2 -->
            <div class="duration-700 ease-in-out absolute inset-0 transition-all transform translate-x-full" data-carousel-item>
                <div class="flex flex-col lg:flex-row h-full">
                    <div class="w-full lg:w-1/2 h-full flex flex-col justify-center items-center px-4 md:px-8 py-12 lg:py-0 bg-gradient-to-br from-green-800 to-emerald-900 text-white">
                        <div class="bg-white/10 p-5 rounded-full backdrop-blur-sm mb-6">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 md:w-20 md:h-20 text-white">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"/>
                            </svg>
                        </div>

                        <div class="text-center mb-8">
                            <h1 class="text-2xl md:text-3xl lg:text-4xl font-bold mb-4">Formation pour tous</h1>
                            <div class="w-16 h-1 bg-amber-400 rounded-full mx-auto mb-4"></div>
                            <p class="text-lg md:text-xl max-w-md mx-auto">Du débutant au professionnel confirmé</p>
                        </div>

                        <a href="{{ route('contact') }}" class="bg-amber-500 hover:bg-amber-600 text-white font-bold py-3 px-8 rounded-full transition-all duration-300 transform hover:scale-105">
                            Nous Contacter
                        </a>
                    </div>

                    <div class="hidden lg:block lg:w-1/2 h-full relative">
                        <img src="/img/IMG_4607.jpg" alt="Formation IAI Togo" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-r from-green-900/70 to-emerald-800/50"></div>
                    </div>
                </div>
            </div>

            <!-- Item 3 -->
            <div class="duration-700 ease-in-out absolute inset-0 transition-all transform translate-x-full" data-carousel-item>
                <div class="flex flex-col lg:flex-row h-full">
                    <div class="w-full lg:w-1/2 h-full flex flex-col justify-center items-center px-4 md:px-8 py-12 lg:py-0 bg-gradient-to-br from-green-800 to-emerald-900 text-white">
                        <div class="bg-white/10 p-5 rounded-full backdrop-blur-sm mb-6">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 md:w-20 md:h-20 text-white">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"/>
                            </svg>
                        </div>

                        <div class="text-center mb-8">
                            <h1 class="text-2xl md:text-3xl lg:text-4xl font-bold mb-4">Accompagnement personnalisé</h1>
                            <div class="w-16 h-1 bg-amber-400 rounded-full mx-auto mb-4"></div>
                            <p class="text-lg md:text-xl max-w-md mx-auto">Tout au long de votre parcours académique</p>
                        </div>

                        <a href="{{ route('contact') }}" class="bg-amber-500 hover:bg-amber-600 text-white font-bold py-3 px-8 rounded-full transition-all duration-300 transform hover:scale-105">
                            Nous Contacter
                        </a>
                    </div>

                    <div class="hidden lg:block lg:w-1/2 h-full relative">
                        <img src="/img/IMG_4554.jpg" alt="Accompagnement IAI Togo" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-r from-green-900/70 to-emerald-800/50"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Slider controls -->
        <button type="button" class="absolute top-1/2 left-4 z-30 flex items-center justify-center -translate-y-1/2 cursor-pointer group focus:outline-none" data-carousel-prev>
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white/30 group-hover:bg-white/50 group-focus:ring-4 group-focus:ring-white group-focus:outline-none transition-all duration-300">
                <svg aria-hidden="true" class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                <span class="sr-only">Previous</span>
            </span>
        </button>
        <button type="button" class="absolute top-1/2 right-4 z-30 flex items-center justify-center -translate-y-1/2 cursor-pointer group focus:outline-none" data-carousel-next>
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white/30 group-hover:bg-white/50 group-focus:ring-4 group-focus:ring-white group-focus:outline-none transition-all duration-300">
                <svg aria-hidden="true" class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
                <span class="sr-only">Next</span>
            </span>
        </button>

        <!-- Indicators -->
        <div class="absolute z-30 flex space-x-3 -translate-x-1/2 bottom-5 left-1/2">
            <button type="button" class="w-3 h-3 rounded-full bg-white/50 hover:bg-white" aria-current="true" aria-label="Slide 1" data-carousel-slide-to="0"></button>
            <button type="button" class="w-3 h-3 rounded-full bg-white/50 hover:bg-white" aria-current="false" aria-label="Slide 2" data-carousel-slide-to="1"></button>
            <button type="button" class="w-3 h-3 rounded-full bg-white/50 hover:bg-white" aria-current="false" aria-label="Slide 3" data-carousel-slide-to="2"></button>
        </div>
    </div>
</div>



<!-- About Section -->
<section id="about" class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="flex flex-col lg:flex-row items-center gap-12">
            <div class="w-full lg:w-1/2">
                <img src="{{ asset('img/dg_iai.jpg') }}" alt="Directeur Général IAI-Togo" class="rounded-xl shadow-lg w-full h-auto">
            </div>
            
            <div class="w-full lg:w-1/2">
                <h2 class="section-title text-3xl font-bold text-green-800 mb-6">Mot du Représentant Résident</h2>
                
                <div class="testimonial-card card-hover mb-8 testimonial-icon">
                    <p class="text-lg">
                        Bienvenue sur le portail web de l'Institut Africain d'informatique, représentation
                        du TOGO. Ouvert le 22 octobre 2002, l'IAI-TOGO est une
                        école inter-Etats d'enseignement supérieur en Informatique. Il est membre du réseau
                        IAI créé le 29 janvier 1971 à Fort Lamy (actuel Ndjaména) en république du TCHAD.
                    </p>
                </div>
                
                <div class="flex flex-col gap-2">
                    <h3 class="text-green-800 text-2xl font-bold">AGBETI Kodjo Akoro Bitantchi</h3>
                    <p class="text-gray-600 italic">Représentant Résident IAI - TOGO</p>
                </div>
                
            </div>
        </div>
    </div>
</section>



<!-- History Section -->
<section class="relative h-96 bg-cover bg-center bg-fixed" style="background-image: url(/img/IMG_4667.jpg)">
    <div class="absolute inset-0 bg-green-900 opacity-80"></div>
    <div class="container mx-auto px-4 h-full flex items-center relative z-10">
        <div class="max-w-2xl text-white">
            <h2 class="text-3xl md:text-4xl font-bold mb-6">Histoire de l'IAI</h2>
            <p class="text-lg mb-8">
               L'Institut Africain d'Informatique (L'IAI) est une école
                        supérieure en informatique. La convention
                        portant création de l'institut et les statuts y afférent ont été signés le 29 janvier 1972 à Fort
                        Lamy (actuel Ndjaména) en république du TCHAD. L'accord de siège entre l'IAI et le
                        GABON a été signé en janvier 1975. Il est par conséquent un établissement inter-Etats.
            </p>
            <a href="{{ route('about') }}" class="btn-secondary rounded-lg py-3 px-8 font-bold text-lg inline-block">
                En savoir plus
            </a>
        </div>
    </div>
</section>

<!-- Blog Section -->
@include('partials.blog')

 <!-- Partners Section -->
    <section class="py-20 bg-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 animate-fade-in-up">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Nos partenaires</h2>
                <p class="text-xl text-gray-600">Ils nous font confiance pour former leurs futurs talents</p>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-8 items-center">
                <div class="flex justify-center p-4 bg-white rounded-lg shadow-sm transform hover:scale-110 transition-all duration-500">
                    <img class="h-12 object-contain" src="/img/partenaires/troyes.png" alt="Troyes">
                </div>
                <div class="flex justify-center p-4 bg-white rounded-lg shadow-sm transform hover:scale-110 transition-all duration-500">
                    <img class="h-12 object-contain" src="/img/partenaires/belfort.jpg" alt="Belfort">
                </div>
                <div class="flex justify-center p-4 bg-white rounded-lg shadow-sm transform hover:scale-110 transition-all duration-500">
                    <img class="h-12 object-contain" src="/img/partenaires/ul.png" alt="UL">
                </div>
                <div class="flex justify-center p-4 bg-white rounded-lg shadow-sm transform hover:scale-110 transition-all duration-500">
                    <img class="h-12 object-contain" src="/img/partenaires/logo-uk.png" alt="UK">
                </div>
                <div class="flex justify-center p-4 bg-white rounded-lg shadow-sm transform hover:scale-110 transition-all duration-500">
                    <img class="h-12 object-contain" src="/img/partenaires/nice.png" alt="Nice">
                </div>
                <div class="flex justify-center p-4 bg-white rounded-lg shadow-sm transform hover:scale-110 transition-all duration-500">
                    <img class="h-12 object-contain" src="/img/partenaires/compiègne.png" alt="Compiègne">
                </div>
            </div>
        </div>
    </section>

<!-- CTA Section -->
<section class="py-16 bg-green-800 text-white">
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-3xl md:text-4xl font-bold mb-6">Prêt à commencer votre aventure à l'IAI Togo?</h2>
        <p class="text-xl mb-10 max-w-2xl mx-auto">Rejoignez notre communauté d'étudiants passionnés par l'informatique et bénéficiez d'une formation d'excellence</p>
        
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('formations') }}" class="bg-white text-green-800 rounded-lg py-3 px-8 font-bold text-lg inline-block hover:bg-gray-100 transition-colors">
                Découvrir nos formations
            </a>
            <a href="{{ route('contact') }}" class="border-2 border-white text-white rounded-lg py-3 px-8 font-bold text-lg inline-block hover:bg-white hover:text-green-800 transition-colors">
                Nous contacter
            </a>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Hero carousel functionality
        const heroCarousel = document.getElementById('hero-carousel');
        const heroSlides = heroCarousel?.querySelectorAll('[data-carousel-item]') || [];
        const heroPrevBtn = heroCarousel?.querySelector('[data-carousel-prev]');
        const heroNextBtn = heroCarousel?.querySelector('[data-carousel-next]');
        const heroIndicators = heroCarousel?.querySelectorAll('[data-carousel-slide-to]') || [];
        
        let currentHeroSlide = 0;
        
        function showHeroSlide(index) {
            if (!heroSlides || heroSlides.length === 0) return;
            heroSlides.forEach((slide, i) => {
                // Ensure base classes
                slide.classList.add('absolute', 'inset-0', 'transition-all', 'duration-700', 'ease-in-out', 'transform');
                // Clean up any previous translate classes
                slide.classList.remove('translate-x-full', '-translate-x-full', 'translate-x-0', 'opacity-0', 'opacity-100', 'z-0', 'z-10', 'pointer-events-none');

                if (i === index) {
                    slide.classList.add('translate-x-0', 'opacity-100', 'z-10');
                    slide.setAttribute('data-carousel-item', 'active');
                } else if (i < index) {
                    slide.classList.add('-translate-x-full', 'opacity-0', 'z-0', 'pointer-events-none');
                    slide.setAttribute('data-carousel-item', '');
                } else {
                    slide.classList.add('translate-x-full', 'opacity-0', 'z-0', 'pointer-events-none');
                    slide.setAttribute('data-carousel-item', '');
                }
            });
            
            heroIndicators.forEach((indicator, i) => {
                if (i === index) {
                    indicator.setAttribute('aria-current', 'true');
                    indicator.classList.replace('bg-white/50', 'bg-white');
                } else {
                    indicator.removeAttribute('aria-current');
                    indicator.classList.replace('bg-white', 'bg-white/50');
                }
            });
            
            currentHeroSlide = index;
        }
        
        function nextHeroSlide() {
            let nextIndex = (currentHeroSlide + 1) % heroSlides.length;
            showHeroSlide(nextIndex);
        }
        
        function prevHeroSlide() {
            let prevIndex = (currentHeroSlide - 1 + heroSlides.length) % heroSlides.length;
            showHeroSlide(prevIndex);
        }
        
        heroPrevBtn?.addEventListener('click', prevHeroSlide);
        heroNextBtn?.addEventListener('click', nextHeroSlide);
        
        heroIndicators.forEach((indicator, index) => {
            indicator.addEventListener('click', () => showHeroSlide(index));
        });
        
        // Initialize first slide position explicitly
        showHeroSlide(0);
        
        // Auto advance hero carousel
        let heroCarouselInterval = setInterval(nextHeroSlide, 5000);
        
        heroCarousel.addEventListener('mouseenter', () => {
            clearInterval(heroCarouselInterval);
        });
        
        heroCarousel.addEventListener('mouseleave', () => {
            heroCarouselInterval = setInterval(nextHeroSlide, 5000);
        });
        
        // Partners carousel functionality (guarded if present)
        const partnersCarousel = document.getElementById('partners-carousel');
        if (partnersCarousel) {
            const partnerSlides = partnersCarousel.querySelectorAll('.carousel-slide');
            const partnerPrevBtn = document.querySelector('.carousel-prev');
            const partnerNextBtn = document.querySelector('.carousel-next');
            const partnerIndicators = document.querySelectorAll('.carousel-indicator');
            
            let currentPartnerSlide = 0;
            
            function showPartnerSlide(index) {
                partnersCarousel.style.transform = `translateX(-${index * 100}%)`;
                
                partnerIndicators.forEach((indicator, i) => {
                    if (i === index) {
                        indicator.classList.add('bg-green-600');
                        indicator.classList.remove('bg-green-300');
                    } else {
                        indicator.classList.remove('bg-green-600');
                        indicator.classList.add('bg-green-300');
                    }
                });
                
                currentPartnerSlide = index;
            }
            
            partnerNextBtn?.addEventListener('click', () => {
                let nextIndex = (currentPartnerSlide + 1) % partnerSlides.length;
                showPartnerSlide(nextIndex);
            });
            
            partnerPrevBtn?.addEventListener('click', () => {
                let prevIndex = (currentPartnerSlide - 1 + partnerSlides.length) % partnerSlides.length;
                showPartnerSlide(prevIndex);
            });
            
            partnerIndicators.forEach((indicator, index) => {
                indicator.addEventListener('click', () => showPartnerSlide(index));
            });
            
            // Auto advance partners carousel
            setInterval(() => {
                let nextIndex = (currentPartnerSlide + 1) % partnerSlides.length;
                showPartnerSlide(nextIndex);
            }, 4000);
        }
        
        // Animation au défilement
        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.1
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fade-in');
                }
            });
        }, observerOptions);
        
        // Observer les éléments à animer
        document.querySelectorAll('.stat-card, .program-card').forEach(el => {
            observer.observe(el);
        });
    });
</script>
@endsection