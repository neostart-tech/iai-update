@extends("layouts.master")

@section('content')
<div class="min-h-screen">
    <!-- Hero Section avec effet parallaxe -->
    <section class="relative h-screen flex items-center justify-center overflow-hidden">
        <div class="absolute inset-0 z-0">
             <div class="absolute inset-0 bg-gradient-to-r from-[#0D7A37]/90 to-[#fbef8b]/80 z-10"></div>
            <img 
                src="https://images.unsplash.com/photo-1521587760476-6c12a4b040da?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1170&q=80" 
                alt="Formations en informatique" 
                class="w-full h-full object-cover transform scale-110 animate-zoom-out"
            >
            <div class="absolute inset-0 bg-gray-900 opacity-70"></div>
        </div>
        
        <div class="relative z-10 text-center text-white max-w-4xl mx-auto px-4 animate-fade-in-up">
            <h1 class="text-4xl md:text-6xl font-bold mb-6">Nos Formations</h1>
            <div class="w-24 h-1 bg-[#fbef8b] mx-auto mb-8 animate-expand"></div>
            <p class="text-xl md:text-2xl mb-10 leading-relaxed opacity-0 animate-fade-in-delayed">
                Notre école informatique offre une large gamme de programmes de formation pour aider les étudiants à atteindre leurs objectifs de carrière dans l'informatique.
            </p>
            <a 
                href="{{ route('contact') }}" 
                class="inline-block px-8 py-4 bg-[#0D7A37] text-white font-semibold rounded-lg shadow-lg hover:bg-green-700 transition-all duration-300 transform hover:scale-105 animate-pulse-slow"
            >
                Nous Contacter
            </a>
        </div>
        
        <div class="absolute bottom-10 left-1/2 transform -translate-x-1/2 z-10 animate-bounce-slow">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7-7H3"></path>
            </svg>
        </div>
    </section>

    <!-- Introduction Section -->
    <section class="py-20 bg-white">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 animate-fade-in-up">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">
                    Formations et certifications pour nos futurs cadres
                </h2>
                <div class="w-32 h-1 bg-gradient-to-r from-[#fbef8b] to-[#0D7A37] mx-auto mb-10 animate-expand"></div>
            </div>
            
            <div class="grid md:grid-cols-2 gap-10 items-center">
                <div class="animate-fade-in-left">
                    <p class="text-lg text-gray-700 mb-6">
                        Nos programmes de formation sont conçus par des professionnels expérimentés de l'industrie et mis à jour régulièrement pour refléter les dernières technologies et tendances du marché.
                    </p>
                    <p class="text-lg text-gray-700">
                        Nous proposons des cours en classe, en ligne et en mode hybride pour s'adapter aux besoins de chaque étudiant.
                    </p>
                </div>
                <div class="bg-gradient-to-br from-[#fbef8b]/20 to-[#0D7A37]/20 p-8 rounded-2xl shadow-lg border border-[#0D7A37]/30 animate-fade-in-right">
                    <h3 class="text-xl font-semibold text-[#0D7A37] mb-4">Avantages supplémentaires</h3>
                    <ul class="space-y-3">
                        <li class="flex items-start animate-fade-in-right-delayed-1">
                            <svg class="w-6 h-6 text-[#0D7A37] mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700">Cours de perfectionnement</span>
                        </li>
                        <li class="flex items-start animate-fade-in-right-delayed-2">
                            <svg class="w-6 h-6 text-[#0D7A37] mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700">Certifications professionnelles</span>
                        </li>
                        <li class="flex items-start animate-fade-in-right-delayed-3">
                            <svg class="w-6 h-6 text-[#0D7A37] mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700">Accompagnement personnalisé</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Formations Grid Section -->
    <section id="formations" class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12 animate-fade-in-up">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Découvrez nos formations</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Choisissez la formation qui correspond à vos ambitions professionnelles
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Formation Modulaire -->
                <div class="bg-white rounded-xl overflow-hidden shadow-md transition-all duration-300 hover:shadow-xl transform hover:-translate-y-1 animate-fade-in-up-card-1 group cursor-pointer modern-card">
                    <div class="relative overflow-hidden">
                        <img 
                            src="https://img.freepik.com/photos-gratuite/formateur-expliquant-details-du-logiciel-au-nouvel-employe_74855-1666.jpg" 
                            alt="Formation modulaire" 
                            class="w-full h-40 object-cover transition-transform duration-500 group-hover:scale-105"
                        >
                        <div class="absolute top-3 left-3">
                            <span class="bg-[#0D7A37] text-white px-3 py-1 rounded-full text-xs font-semibold">
                                3 FILIÈRES
                            </span>
                        </div>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-4">
                            <span class="text-white text-sm font-medium">En savoir plus →</span>
                        </div>
                    </div>
                    <div class="p-5">
                        <h3 class="text-lg font-bold text-gray-900 mb-2 line-clamp-2">Formation Modulaire</h3>
                        <div class="w-12 h-1 bg-gradient-to-r from-[#fbef8b] to-[#0D7A37] mb-3"></div>
                        <p class="text-sm text-gray-600 mb-4 line-clamp-3">
                            Une formation modulaire est une formation dont le programme peut être décomposé en un nombre déterminé de modules.
                        </p>
                        <a href="{{ route('formations.modulaire', ['tab' => 'modulaire']) }}" class="text-sm text-[#0D7A37] font-medium hover:text-green-700 flex items-center">
                            Détails
                            <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                            </svg>
                        </a>
                    </div>
                </div>
                
                <!-- Formation Certifiante -->
                <div class="bg-white rounded-xl overflow-hidden shadow-md transition-all duration-300 hover:shadow-xl transform hover:-translate-y-1 animate-fade-in-up-card-2 group cursor-pointer modern-card">
                    <div class="relative overflow-hidden">
                        <img 
                            src="/img/certifiante.jpg" 
                            alt="Formation certifiante" 
                            class="w-full h-40 object-cover transition-transform duration-500 group-hover:scale-105"
                        >
                        <div class="absolute top-3 left-3">
                            <span class="bg-[#0D7A37] text-white px-3 py-1 rounded-full text-xs font-semibold">
                                3 CERTIFICATIONS
                            </span>
                        </div>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-4">
                            <span class="text-white text-sm font-medium">En savoir plus →</span>
                        </div>
                    </div>
                    <div class="p-5">
                        <h3 class="text-lg font-bold text-gray-900 mb-2 line-clamp-2">Formation Certifiante</h3>
                        <div class="w-12 h-1 bg-gradient-to-r from-[#fbef8b] to-[#0D7A37] mb-3"></div>
                        <p class="text-sm text-gray-600 mb-4 line-clamp-3">
                            Les formations certifiantes sont appréciées des recruteurs et très valorisées sur un CV car leur enseignement est en lien direct avec les besoins des entreprises.
                        </p>
                        <a href="{{ route('formations.modulaire', ['tab' => 'certifiante']) }}" class="text-sm text-[#0D7A37] font-medium hover:text-green-700 flex items-center">
                            Détails
                            <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                            </svg>
                        </a>
                    </div>
                </div>
                
                <!-- Formation Alternance -->
                <div class="bg-white rounded-xl overflow-hidden shadow-md transition-all duration-300 hover:shadow-xl transform hover:-translate-y-1 animate-fade-in-up-card-3 group cursor-pointer modern-card">
                    <div class="relative overflow-hidden">
                        <img 
                            src="/img/alternace.jpg" 
                            alt="Formation en alternance" 
                            class="w-full h-40 object-cover transition-transform duration-500 group-hover:scale-105"
                        >
                        <div class="absolute top-3 left-3">
                            <span class="bg-[#0D7A37] text-white px-3 py-1 rounded-full text-xs font-semibold">
                                1 FILIÈRE
                            </span>
                        </div>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-4">
                            <span class="text-white text-sm font-medium">En savoir plus →</span>
                        </div>
                    </div>
                    <div class="p-5">
                        <h3 class="text-lg font-bold text-gray-900 mb-2 line-clamp-2">Formation par Alternance</h3>
                        <div class="w-12 h-1 bg-gradient-to-r from-[#fbef8b] to-[#0D7A37] mb-3"></div>
                        <p class="text-sm text-gray-600 mb-4 line-clamp-3">
                            Cette forme de formation permet aux personnes déjà dans la vie active de continuer à se former pour améliorer leurs compétences.
                        </p>
                        <a href="{{ route('formations.modulaire', ['tab' => 'alternance']) }}" class="text-sm text-[#0D7A37] font-medium hover:text-green-700 flex items-center">
                            Détails
                            <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                            </svg>
                        </a>
                    </div>
                </div>
                
                <!-- Formation Continue -->
                <div class="bg-white rounded-xl overflow-hidden shadow-md transition-all duration-300 hover:shadow-xl transform hover:-translate-y-1 animate-fade-in-up-card-4 group cursor-pointer modern-card">
                    <div class="relative overflow-hidden">
                        <img 
                            src="https://images.unsplash.com/photo-1517048676732-d65bc937f952?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1470&q=80" 
                            alt="Formation continue" 
                            class="w-full h-40 object-cover transition-transform duration-500 group-hover:scale-105"
                        >
                        <div class="absolute top-3 left-3">
                            <span class="bg-[#0D7A37] text-white px-3 py-1 rounded-full text-xs font-semibold">
                                3 FILIÈRES
                            </span>
                        </div>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-4">
                            <span class="text-white text-sm font-medium">En savoir plus →</span>
                        </div>
                    </div>
                    <div class="p-5">
                        <h3 class="text-lg font-bold text-gray-900 mb-2 line-clamp-2">Formation Continue</h3>
                        <div class="w-12 h-1 bg-gradient-to-r from-[#fbef8b] to-[#0D7A37] mb-3"></div>
                        <p class="text-sm text-gray-600 mb-4 line-clamp-3">
                            Notre offre de formation continue concerne tous les publics sans condition d'âge ou de niveau d'études.
                        </p>
                        <a href="{{ route('formations.modulaire', ['tab' => 'continue']) }}" class="text-sm text-[#0D7A37] font-medium hover:text-green-700 flex items-center">
                            Détails
                            <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-20 bg-gradient-to-r from-[#fbef8b] to-[#0D7A37] text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                <div class="p-6 animate-count-up">
                    <div class="text-4xl md:text-5xl font-bold mb-2 count" data-target="10">0</div>
                    <div class="text-lg">Filières disponibles</div>
                </div>
                <div class="p-6 animate-count-up">
                    <div class="text-4xl md:text-5xl font-bold mb-2 count" data-target="500">0</div>
                    <div class="text-lg">Étudiants formés</div>
                </div>
                <div class="p-6 animate-count-up">
                    <div class="text-4xl md:text-5xl font-bold mb-2 count" data-target="95">0%</div>
                    <div class="text-lg">Taux de réussite</div>
                </div>
                <div class="p-6 animate-count-up">
                    <div class="text-4xl md:text-5xl font-bold mb-2 count" data-target="20">0</div>
                    <div class="text-lg">Partenaires entreprises</div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6 animate-fade-in-up">
                Prêt à démarrer votre avenir dans l'informatique ?
            </h2>
            <p class="text-xl text-gray-600 mb-10 max-w-3xl mx-auto animate-fade-in-up">
                Explorez nos différentes options de formation et contactez notre équipe d'admission pour en savoir plus sur les étapes nécessaires pour vous inscrire.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center animate-fade-in-up">
                <a 
                    href="{{ route('contact') }}" 
                    class="px-8 py-4 bg-[#0D7A37] text-white font-semibold rounded-lg shadow-md hover:bg-green-700 transition-all duration-300 transform hover:scale-105"
                >
                    Contactez-nous
                </a>
                <a 
                    href="{{ route('formations.modulaire') }}" 
                    class="px-8 py-4 bg-white text-[#0D7A37] border border-[#0D7A37] font-semibold rounded-lg shadow-md hover:bg-[#fbef8b]/20 transition-all duration-300 transform hover:scale-105"
                >
                    Voir toutes les formations
                </a>
            </div>
        </div>
    </section>

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
</div>

<style>
    /* Animations personnalisées */
    .animate-fade-in-up {
        animation: fadeInUp 1s ease-out forwards;
        opacity: 0;
    }
    
    .animate-fade-in-left {
        animation: fadeInLeft 1s ease-out forwards;
        opacity: 0;
        animation-delay: 0.3s;
    }
    
    .animate-fade-in-right {
        animation: fadeInRight 1s ease-out forwards;
        opacity: 0;
        animation-delay: 0.3s;
    }
    
    .animate-fade-in-right-delayed-1 {
        animation: fadeInRight 0.8s ease-out forwards;
        opacity: 0;
        animation-delay: 0.5s;
    }
    
    .animate-fade-in-right-delayed-2 {
        animation: fadeInRight 0.8s ease-out forwards;
        opacity: 0;
        animation-delay: 0.7s;
    }
    
    .animate-fade-in-right-delayed-3 {
        animation: fadeInRight 0.8s ease-out forwards;
        opacity: 0;
        animation-delay: 0.9s;
    }
    
    .animate-fade-in-delayed {
        animation: fadeIn 1s ease-out forwards;
        opacity: 0;
        animation-delay: 0.8s;
    }
    
    .animate-zoom-out {
        animation: zoomOut 20s ease-in-out infinite alternate;
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
    
    .animate-fade-in-up-card-1 {
        animation: fadeInUp 1s ease-out forwards;
        opacity: 0;
        animation-delay: 0.2s;
    }
    
    .animate-fade-in-up-card-2 {
        animation: fadeInUp 1s ease-out forwards;
        opacity: 0;
        animation-delay: 0.4s;
    }
    
    .animate-fade-in-up-card-3 {
        animation: fadeInUp 1s ease-out forwards;
        opacity: 0;
        animation-delay: 0.6s;
    }
    
    .animate-fade-in-up-card-4 {
        animation: fadeInUp 1s ease-out forwards;
        opacity: 0;
        animation-delay: 0.8s;
    }
    
    .animate-count-up {
        opacity: 0;
        animation: fadeIn 1s ease-out forwards;
    }
    
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
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    @keyframes zoomOut {
        from { transform: scale(1.1); }
        to { transform: scale(1); }
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
</style>

<script>
    // Animation pour le compteur des statistiques
    document.addEventListener('DOMContentLoaded', function() {
        const counters = document.querySelectorAll('.count');
        const speed = 200; // Plus la valeur est basse, plus c'est rapide
        
        counters.forEach(counter => {
            const target = +counter.getAttribute('data-target');
            let count = 0;
            
            const updateCount = () => {
                const increment = target / speed;
                
                if (count < target) {
                    count += increment;
                    if (counter.innerHTML.includes('%')) {
                        counter.innerText = Math.ceil(count) + '%';
                    } else {
                        counter.innerText = Math.ceil(count);
                    }
                    setTimeout(updateCount, 1);
                } else {
                    if (counter.innerHTML.includes('%')) {
                        counter.innerText = target + '%';
                    } else {
                        counter.innerText = target;
                    }
                }
            };
            
            // Démarrer l'animation lorsque l'élément est dans le viewport
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        updateCount();
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.5 });
            
            observer.observe(counter.parentElement);
        });
    });
</script>
@endsection