@extends("layouts.master")

@section('content')
<div>

    <!-- Banner -->
    <section class="relative bg-cover bg-center bg-no-repeat" style="background-image: url('../img/IMG_4657.jpg')">
        <div class="absolute inset-0 bg-gradient-to-r from-[#0D7A37]/90 to-[#fbef8b]/80 z-10"></div>
        <div class="relative z-10 flex items-center justify-center h-[70vh] text-center text-white px-6">
            <div class="space-y-6 max-w-3xl">
                <h1 class="text-3xl md:text-6xl font-extrabold uppercase tracking-wide">À propos de l'IAI-Togo</h1>
                <span class="block mx-auto w-24 h-1 bg-[#fbef8b]"></span>
                <p class="text-lg md:text-xl font-medium leading-relaxed">
                    Découvrez ce que l'Institut Africain d'Informatique - Togo (IAI-TOGO) a à vous offrir.
                    Voici un aperçu de notre bureau, nos statistiques et quelques faits rapides.
                </p>
                <a href="{{ route('contact') }}"
                   class="inline-block mt-6 px-8 py-4 bg-[#0D7A37] text-white font-bold rounded-lg shadow-lg hover:bg-[#0a5c2a] transition">
                   Nous Contacter
                </a>
            </div>
        </div>
    </section>

    <!-- Raisons de Choisir -->
    <section class="px-6 py-16 lg:px-20 bg-gray-50">
        <div class="text-center max-w-3xl mx-auto space-y-6">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900">Les raisons pour choisir l'IAI-TOGO</h2>
            <p class="text-gray-600 text-lg leading-relaxed">
                Notre école informatique est dédiée à la formation de professionnels qualifiés.
                Nous croyons que l'apprentissage continu est essentiel pour réussir dans ce domaine
                en constante évolution, et nous offrons des programmes de qualité, mis à jour
                régulièrement selon les dernières technologies et tendances.
            </p>
        </div>

        <!-- Stats -->
        <div class="grid gap-8 mt-12 md:grid-cols-3">
            <div class="bg-white rounded-lg shadow-lg p-8 text-center hover:shadow-2xl transition transform hover:-translate-y-1">
                <h3 class="text-5xl font-bold text-[#0D7A37]">+889</h3>
                <p class="mt-2 text-gray-700 font-medium uppercase">Ingénieurs formés</p>
            </div>
            <div class="bg-white rounded-lg shadow-lg p-8 text-center hover:shadow-2xl transition transform hover:-translate-y-1">
                <h3 class="text-5xl font-bold text-[#0D7A37]">+3</h3>
                <p class="mt-2 text-gray-700 font-medium uppercase">Filières en informatique</p>
            </div>
            <div class="bg-white rounded-lg shadow-lg p-8 text-center hover:shadow-2xl transition transform hover:-translate-y-1">
                <h3 class="text-5xl font-bold text-[#0D7A37]">+90</h3>
                <p class="mt-2 text-gray-700 font-medium uppercase">Enseignants</p>
            </div>
        </div>

        <div class="grid gap-8 mt-8 md:grid-cols-3">
            <div class="bg-white rounded-lg shadow-lg p-8 text-center hover:shadow-2xl transition transform hover:-translate-y-1">
                <h3 class="text-5xl font-bold text-[#0D7A37]">+400</h3>
                <p class="mt-2 text-gray-700 font-medium uppercase">Étudiants internationaux</p>
            </div>
            <div class="bg-white rounded-lg shadow-lg p-8 text-center hover:shadow-2xl transition transform hover:-translate-y-1">
                <h3 class="text-5xl font-bold text-[#0D7A37]">+10</h3>
                <p class="mt-2 text-gray-700 font-medium uppercase">Certifications internationales</p>
            </div>
            <div class="bg-white rounded-lg shadow-lg p-8 text-center hover:shadow-2xl transition transform hover:-translate-y-1">
                <h3 class="text-5xl font-bold text-[#0D7A37]">+6</h3>
                <p class="mt-2 text-gray-700 font-medium uppercase">Enseignants internationaux</p>
            </div>
        </div>

        <p class="mt-12 text-center text-gray-600 max-w-2xl mx-auto">
            Nos instructeurs sont des professionnels expérimentés et passionnés par l'enseignement,
            prêts à vous aider à atteindre vos objectifs de carrière.
        </p>
    </section>

    <!-- Bloc Présentation -->
    <section class="py-20 px-6 lg:px-20 bg-gradient-to-br from-[#0D7A37] to-[#0a5c2a]">
        <div class="container mx-auto">
            <div class="grid lg:grid-cols-2 gap-8 items-stretch">
                <!-- Bloc IAI-TOGO -->
                <div class="relative overflow-hidden rounded-2xl group h-full">
                    <div class="absolute inset-0 bg-cover bg-center transition-all duration-700 group-hover:scale-105" style="background-image: url('/img/gallerie/galerie12.jpg')"></div>
                    <div class="absolute inset-0 bg-black bg-opacity-50 group-hover:bg-opacity-60 transition-all duration-500"></div>
                    <div class="relative z-10 p-8 text-white h-full flex flex-col justify-between">
                        <div>
                            <h2 class="text-3xl font-bold text-[#fbef8b] mb-4">IAI-TOGO</h2>
                            <p class="text-lg leading-relaxed mb-4">
                                En application de la décision du Conseil d'Administration de délocaliser l'IAI, la
                                Représentation du TOGO (IAI-TOGO) a ouvert ses portes le 24 octobre 2002. 
                                L'accord d'établissement entre la République Togolaise et l'Institut Africain 
                                d'Informatique a été signé le 12 mai 2006.
                            </p>
                            <p class="text-lg leading-relaxed">
                                L'IAI-TOGO propose actuellement le cycle de formation des ingénieurs de travaux
                                informatiques (Licence professionnelle en informatique). 
                                Les diplômés peuvent poursuivre leurs études supérieures au siège au GABON 
                                ou dans les universités occidentales ou asiatiques (UTBM, Université Laval, etc.).
                            </p>
                        </div>
                        <div class="mt-6">
                            <a href="{{ route('candidatures.create') }}"
                               class="inline-block px-6 py-3 bg-[#fbef8b] text-[#0D7A37] font-semibold rounded-lg shadow-md hover:bg-[#f9e769] transition transform hover:-translate-y-0.5">
                               Demande d'Admission
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Bloc IAI-SIEGE -->
                <div class="relative overflow-hidden rounded-2xl group h-full">
                    <div class="absolute inset-0 bg-cover bg-center transition-all duration-700 group-hover:scale-105" style="background-image: url('https://www.orientation.ogooue-education.com/wp-content/uploads/listing-uploads/cover/2022/07/IAI-Copie.jpg')"></div>
                    <div class="absolute inset-0 bg-black bg-opacity-50 group-hover:bg-opacity-60 transition-all duration-500"></div>
                    <div class="relative z-10 p-8 text-white h-full flex flex-col justify-between">
                        <div>
                            <h2 class="text-3xl font-bold text-[#fbef8b] mb-4">IAI-SIÈGE</h2>
                            <p class="text-lg leading-relaxed mb-4">
                                Au lendemain des indépendances, la formation de cadres techniques de haut niveau 
                                adaptés aux besoins socio-économiques des pays apparaissait comme une priorité 
                                pour soutenir un développement harmonieux.
                            </p>
                            <p class="text-lg leading-relaxed mb-4">
                                C'est ainsi que les chefs d'État de l'OCAM ont décidé de créer l'Institut Africain 
                                d'Informatique (IAI) le 29 janvier 1972. L'accord de siège entre l'IAI et le GABON 
                                fut signé en janvier 1975, en faisant un établissement inter-États.
                            </p>
                            <p class="text-lg leading-relaxed">
                                Dans l'optique d'approcher l'institut des pays membres, le Conseil d'Administration 
                                a autorisé la délocalisation du premier cycle : IAI-Cameroun (1999), IAI-Niger (2001), 
                                IAI-Togo (2002).
                            </p>
                        </div>
                        <div class="mt-6">
                            <a href="/contact"
                               class="inline-block px-6 py-3 border-2 border-[#fbef8b] text-[#fbef8b] font-semibold rounded-lg hover:bg-[#fbef8b] hover:text-[#0D7A37] transition transform hover:-translate-y-0.5">
                               Contactez-nous
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

</div>

<style>
    /* Animation pour les cartes de statistiques */
    .transition {
        transition: all 0.3s ease;
    }
    
    .transform {
        transform: translateY(0);
    }
    
    .hover\:-translate-y-1:hover {
        transform: translateY(-4px);
    }
    
    .hover\:-translate-y-0\.5:hover {
        transform: translateY(-2px);
    }
    
    /* Assurer que les deux cartes aient la même hauteur */
    .grid.lg\:grid-cols-2 {
        grid-auto-rows: 1fr;
    }
    
    /* Styles pour les cartes avec images de fond */
    .relative.overflow-hidden.rounded-2xl {
        min-height: 500px;
    }
    
    @media (max-width: 1024px) {
        .relative.overflow-hidden.rounded-2xl {
            min-height: 450px;
        }
    }
    
    @media (max-width: 768px) {
        .relative.overflow-hidden.rounded-2xl {
            min-height: 400px;
        }
    }
</style>
@endsection