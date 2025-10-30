@extends("layouts.master")

@section('content')
	<div class="">

		<!-- Hero Section avec effet moderne -->
    <section class="relative h-[80vh] flex items-center justify-center overflow-hidden">
        <div class="absolute inset-0 z-0">
            <div class="absolute inset-0 bg-gradient-to-r from-[#0D7A37]/90 to-[#fbef8b]/80 z-10"></div>
            <img 
                src="https://images.unsplash.com/photo-1515165616480-efd71925068f?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1170&q=80" 
                alt="Admission à l'IAI-Togo" 
                class="w-full h-full object-cover"
            >
        </div>
        
        <div class="relative z-10 text-center text-white max-w-4xl mx-auto px-4">
            <h1 class="text-4xl md:text-6xl font-bold mb-6 animate-fade-in">Actualités</h1>
            <div class="w-24 h-1 bg-[#fbef8b] mx-auto mb-8 rounded-full"></div>
            <p class="text-xl md:text-2xl mb-10 leading-relaxed">
                {{AppGetters::getAppName()}} prépare les étudiants à des carrières dans l'industrie

							de l'informatique, en leur offrant une solide base de connaissances théoriques <br>
							et pratiques en programmation, en réseaux informatiques, en bases de données,
							en développement web, <br> et dans d'autres domaines liés à l'informatique.
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

		<!--News & Events sections-->
		@include('partials.blog')


	</div>
@endsection
