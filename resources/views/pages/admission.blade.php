@extends('layouts.master')

@section('content')
<div class="min-h-screen bg-gray-50">

    <!-- Hero Section avec effet moderne -->
    <section class="relative h-[80vh] flex items-center justify-center overflow-hidden">
        <div class="absolute inset-0 z-0">
            <div class="absolute inset-0 bg-gradient-to-r from-[#0D7A37]/90 to-[#fbef8b]/80 z-10"></div>
            <img 
                src="../img/IMG_9271.jpg" 
                alt="Admission à l'IAI-Togo" 
                class="w-full h-full object-cover"
            >
        </div>
        
        <div class="relative z-10 text-center text-white max-w-4xl mx-auto px-4">
            <h1 class="text-4xl md:text-6xl font-bold mb-6 animate-fade-in">Admission à l'IAI-Togo</h1>
            <div class="w-24 h-1 bg-[#fbef8b] mx-auto mb-8 rounded-full"></div>
            <p class="text-xl md:text-2xl mb-10 leading-relaxed">
                Commencez votre parcours académique dès aujourd'hui en rejoignant l'IAI-Togo.
                Découvrez notre processus d'admission étape par étape.
            </p>

        
<!-- href="{{ route('candidatures.create') }}"-->
            <a href="{{ route('candidatures.create') }}"
               class="inline-block px-8 py-4 bg-[#0D7A37] text-white font-bold rounded-xl shadow-lg hover:bg-[#0a5c2a] transition-all duration-300 transform hover:scale-105">
               Faire une demande d'admission
            </a>
        </div>
        
        <div class="absolute bottom-10 left-1/2 transform -translate-x-1/2 z-10 animate-bounce">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
            </svg>
        </div>
    </section>

    <!--  $steps = [
                    ['route'=>'formations','text'=>'Trouvez votre programme', 'icon'=>'🎓'],
                    ['route'=>'dossiers','text'=>'Préparez votre dossier', 'icon'=>'📄'],
                    ['route'=>'candidatures.create','text'=>'Déposez votre demande', 'icon'=>'📝'],
                    ['route'=>'test','text'=>'Passez le test écrit', 'icon'=>'✏️'],
                    ['route'=>'#','text'=>'Consultez les résultats', 'icon'=>'📊'],
                    ['route'=>'#','text'=>'Inscription définitive', 'icon'=>'✅']
                ];-->

    <!-- Processus Admission -->
<section class="py-20 bg-gradient-to-br from-[#fdfdfd] via-[#f7fbe9] to-[#f0f8f5] relative">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">Le processus d'admission</h2>
            <div class="w-32 h-1 bg-gradient-to-r from-[#0D7A37] to-[#fbef8b] mx-auto mb-10"></div>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Suivez ces étapes simples pour postuler à l'IAI-Togo et rejoindre une communauté d'apprenants passionnés.
            </p>
        </div>

        @php
            $steps = [
                'Trouvez votre programme',
                'Préparez votre dossier',
                'Déposez votre demande',
                'Passez le test écrit',
                'Consultez les résultats',
                'Inscription définitive'
            ];
        @endphp

        <!-- Ligne centrale -->
        <div class="relative">
            <div class="absolute left-1/2 transform -translate-x-1/2 h-full w-1 bg-[#0D7A37]/30"></div>

            <div class="space-y-20">
                @foreach($steps as $index => $step)
                    <div class="relative flex items-center {{ $index % 2 == 0 ? 'justify-start' : 'justify-end' }}">
                        <div class="w-1/2 {{ $index % 2 == 0 ? 'pr-10 text-right' : 'pl-10 text-left' }}">
                            <div class="relative group bg-white rounded-2xl p-6 shadow-lg border border-gray-100 hover:border-[#0D7A37]/40 hover:shadow-xl transition-all duration-300">
                                <!-- Numéro étape -->
                                <div class="absolute -top-5 {{ $index % 2 == 0 ? 'right-5' : 'left-5' }} 
                                            flex items-center justify-center w-10 h-10 
                                            rounded-full bg-gradient-to-br from-[#0D7A37] to-[#0a5c2a] text-white font-bold shadow-md">
                                    {{ $index+1 }}
                                </div>

                                <!-- Texte -->
                                <h3 class="text-lg md:text-xl font-semibold text-gray-800 group-hover:text-[#0D7A37] transition">
                                    {{ $step }}
                                </h3>
                            </div>
                        </div>

                        <!-- Point central -->
                        <div class="absolute left-1/2 transform -translate-x-1/2 w-6 h-6 rounded-full bg-[#fbef8b] border-4 border-white shadow"></div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>


    <!-- Concours Section -->
    <section class="py-20 bg-gradient-to-br from-[#0D7A37] to-[#0a5c2a]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">Concours d'entrée</h2>
                <div class="w-32 h-1 bg-[#fbef8b] mx-auto mb-10"></div>
                <p class="text-xl text-white/90 max-w-3xl mx-auto">
                    Découvrez les différentes options de concours pour intégrer l'IAI-Togo
                </p>
            </div>
            
            <div class="grid lg:grid-cols-2 gap-10">
                <!-- Bloc Concours IAI -->
                <div class="bg-white/10 backdrop-blur-md rounded-2xl p-8 border border-white/20 hover:border-white/30 transition-all duration-300 shadow-lg hover:shadow-xl">
                    <div class="text-center mb-8">
                        <h3 class="text-2xl font-bold text-[#fbef8b]">Concours d'entrée à l'IAI</h3>
                        <p class="text-white/90 mt-2">Cycle « Ingénieur Concepteur »</p>
                    </div>
                    
                    <p class="text-white/90 leading-relaxed mb-8 text-center">
                        Le concours d'entrée en 1ère année se déroule chaque année avec des épreuves en Anglais, Mathématiques et Français.
                    </p>
                    
                    <div class="space-y-4 mb-8">
                        <div class="flex justify-between items-center bg-white/10 rounded-xl p-4 text-white hover:bg-white/15 transition">
                            <span class="font-medium">Anglais</span>
                            <div class="flex items-center space-x-4">
                                <span>2h</span>
                                <span class="px-2 py-1 bg-[#0D7A37] rounded text-xs">Coeff 3</span>
                            </div>
                        </div>
                        <div class="flex justify-between items-center bg-white/10 rounded-xl p-4 text-white hover:bg-white/15 transition">
                            <span class="font-medium">Mathématiques</span>
                            <div class="flex items-center space-x-4">
                                <span>4h</span>
                                <span class="px-2 py-1 bg-[#0D7A37] rounded text-xs">Coeff 6</span>
                            </div>
                        </div>
                        <div class="flex justify-between items-center bg-white/10 rounded-xl p-4 text-white hover:bg-white/15 transition">
                            <span class="font-medium">Français</span>
                            <div class="flex items-center space-x-4">
                                <span>2h</span>
                                <span class="px-2 py-1 bg-[#0D7A37] rounded text-xs">Coeff 2</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <a href="#" class="inline-flex items-center px-6 py-3 bg-[#fbef8b] text-[#0D7A37] font-semibold rounded-lg hover:bg-[#f9e769] transition transform hover:-translate-y-0.5">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Télécharger le communiqué
                        </a>
                    </div>
                </div>

                <!-- Bloc Concours IAI-Togo -->
                <div class="bg-white/10 backdrop-blur-md rounded-2xl p-8 border border-white/20 hover:border-white/30 transition-all duration-300 shadow-lg hover:shadow-xl">
                    <div class="text-center mb-8">
                        <h3 class="text-2xl font-bold text-[#fbef8b]">Concours d'entrée à l'IAI-Togo</h3>
                        <p class="text-white/90 mt-2">Cycle « Ingénieur des Travaux Informatiques et Licence Professionnelle »</p>
                    </div>
                    
                    <p class="text-white/90 leading-relaxed mb-8 text-center">
                        Concours d'entrée en 1ère année avec des épreuves similaires mais adaptées au programme de l'IAI-Togo.
                    </p>
                    
                    <div class="space-y-4 mb-8">
                        <div class="flex justify-between items-center bg-white/10 rounded-xl p-4 text-white hover:bg-white/15 transition">
                            <span class="font-medium">Anglais</span>
                            <div class="flex items-center space-x-4">
                                <span>2h</span>
                                <span class="px-2 py-1 bg-[#0D7A37] rounded text-xs">Coeff 3</span>
                            </div>
                        </div>
                        <div class="flex justify-between items-center bg-white/10 rounded-xl p-4 text-white hover:bg-white/15 transition">
                            <span class="font-medium">Mathématiques</span>
                            <div class="flex items-center space-x-4">
                                <span>4h</span>
                                <span class="px-2 py-1 bg-[#0D7A37] rounded text-xs">Coeff 6</span>
                            </div>
                        </div>
                        <div class="flex justify-between items-center bg-white/10 rounded-xl p-4 text-white hover:bg-white/15 transition">
                            <span class="font-medium">Français</span>
                            <div class="flex items-center space-x-4">
                                <span>2h</span>
                                <span class="px-2 py-1 bg-[#0D7A37] rounded text-xs">Coeff 2</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <a href="#" class="inline-flex items-center px-6 py-3 bg-[#fbef8b] text-[#0D7A37] font-semibold rounded-lg hover:bg-[#f9e769] transition transform hover:-translate-y-0.5">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            Résultats du concours
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">Besoin d'informations supplémentaires ?</h2>
            <p class="text-xl text-gray-600 mb-10 max-w-3xl mx-auto">
                Des questions sur le processus d'admission ? Contactez-nous, nous sommes impatients de vous aider.
            </p>
            <div class="flex flex-col sm:flex-row gap-6 justify-center">
                <a href="{{ route('candidatures.create') }}" class="inline-flex items-center px-8 py-4 bg-[#0D7A37] text-white font-semibold rounded-xl shadow-md hover:bg-[#0a5c2a] transition transform hover:-translate-y-0.5">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Demande d'admission
                </a>
                <a href="{{ route('contact') }}" class="inline-flex items-center px-8 py-4 bg-[#fbef8b] text-[#0D7A37] font-semibold rounded-xl shadow-md hover:bg-[#f9e769] transition transform hover:-translate-y-0.5">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    Contactez-nous
                </a>
            </div>
        </div>
    </section>

</div>

<style>
    .animate-fade-in {
        animation: fadeIn 1s ease-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .animate-bounce {
        animation: bounce 2s infinite;
    }
    
    @keyframes bounce {
        0%, 20%, 50%, 80%, 100% {transform: translateY(0) translateX(-50%);}
        40% {transform: translateY(-30px) translateX(-50%);}
        60% {transform: translateY(-15px) translateX(-50%);}
    }
    
    /* Style pour les connecteurs entre les étapes */
    .connector {
        position: relative;
    }
    
    @media (min-width: 1024px) {
        .connector::after {
            content: '';
            position: absolute;
            top: 50%;
            right: -20px;
            width: 40px;
            height: 2px;
            background: #fbef8b;
            z-index: 1;
        }
        
        .connector::before {
            content: '→';
            position: absolute;
            top: 42%;
            right: -28px;
            color: #0D7A37;
            font-weight: bold;
            z-index: 2;
        }
        
        /* Cacher le connecteur pour le dernier élément */
        .connector:last-child::after,
        .connector:last-child::before {
            display: none;
        }
        
        /* Ajustements pour la disposition en grille */
        .grid.grid-cols-3 .connector:nth-child(3n)::after,
        .grid.grid-cols-3 .connector:nth-child(3n)::before {
            display: none;
        }
    }
</style>
@endsection