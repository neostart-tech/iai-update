@extends("layouts.master")

@section('content')
    @php($imagePath = "https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=870&q=80")
    @php($gbImage = "https://images.unsplash.com/photo-1635350736475-c8cef4b21906?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1170&q=80")
    
    <div class="font-sans antialiased text-gray-800">
        <!-- Hero Section -->
        <div class="relative bg-no-repeat bg-center bg-cover w-full" style="background-image: url('{{ $gbImage }}')">
            <div class="absolute inset-0 bg-gradient-to-r from-green-900 to-transparent opacity-90"></div>
            <div class="relative h-[50vh] md:h-[60vh] w-full flex items-center justify-center">
                <div class="w-full h-full text-white flex flex-col py-12 lg:py-0 gap-6 justify-center items-center px-4">
                    <div class="flex flex-col items-center gap-4 text-center max-w-4xl">
                        <h1 class="text-white text-3xl md:text-4xl lg:text-5xl font-bold uppercase tracking-wide">Opportunités</h1>
                        <div class="w-24 h-1.5 bg-[#fbef8b] rounded-full"></div>
                        <p class="text-lg md:text-xl font-medium text-center leading-relaxed">
                            Formation solide en informatique, Stage et opportunités de travail,
                            Réseaux de contact professionnels, Carrières lucratives, Possibilité
                            de travailler dans des entreprises renommées
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Section -->
        <div class="mx-auto bg-stone-100">
            <div class="py-16 md:py-20 flex flex-col items-center justify-center">
                <div class="xl:w-2/3 w-11/12 text-center max-w-4xl">
                    <h1 class="text-4xl md:text-5xl font-bold text-gray-800 mb-6">
                        Dernières actualités
                    </h1>
                    <div class="w-24 h-1.5 bg-[#fbef8b] mx-auto mb-6 rounded-full"></div>
                    <p class="text-lg text-gray-600 leading-relaxed">
                        Nouvelles concernant l'IAI-TOGO et qui sont susceptibles d'intéresser
                        les étudiants, les enseignants, les partenaires et le public en général.
                    </p>
                </div>
            </div>
            
            @if($announcements->isEmpty())
                <div class="relative flex flex-col items-center justify-center p-8 mb-16">
                    <div class="bg-white shadow-lg rounded-xl max-w-2xl w-full p-8 text-center">
                        <div class="flex justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-700 mb-2">Aucune annonce disponible</h3>
                        <p class="text-gray-500">
                            Aucune annonce n'a encore été publiée. Revenez plus tard pour découvrir les nouvelles opportunités.
                        </p>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 gap-8 pb-20 px-4 md:px-8 max-w-6xl mx-auto">
                    @foreach($announcements as $announcement)
                        <div class="bg-white rounded-xl shadow-md overflow-hidden transition-all duration-300 hover:shadow-xl">
                            <div class="p-6">
                                <div class="flex flex-col md:flex-row gap-6 items-start md:items-center">
                                    <div class="flex-shrink-0">
                                        <div class="w-20 h-20 rounded-xl overflow-hidden shadow-md">
                                            <img class="w-full h-full object-cover" alt="Opportunité {{ $announcement->title }}" src="{{ $imagePath }}">
                                        </div>
                                    </div>
                                    
                                    <div class="flex-grow">
                                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                                            <div>
                                                <span class="text-green-700 text-sm font-medium">{{ $announcement->advertiser->nom }}</span>
                                                <h3 class="text-xl font-bold text-gray-800 mt-1">{{ $announcement->title }}</h3>
                                            </div>
                                            <div>
                                                <a class="inline-flex items-center bg-green-800 hover:bg-green-700 text-white font-medium px-5 py-2.5 rounded-lg transition-colors duration-200"
                                                   href="{{ route('opportunities.detail', $announcement) }}">
                                                    Voir détails
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>
                                        
                                        <div class="flex flex-wrap gap-2 mb-4">
                                            <span class="bg-green-100 text-green-800 text-xs font-semibold px-3 py-1.5 rounded-full">
                                                {{ $announcement->type_contrat->value }}
                                            </span>
                                            <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-3 py-1.5 rounded-full">
                                                {{ $announcement->type_annonce->value }}
                                            </span>
                                            <span class="bg-purple-100 text-purple-800 text-xs font-semibold px-3 py-1.5 rounded-full">
                                                {{ $announcement->ville }}
                                            </span>
                                        </div>
                                        
                                        <div class="text-gray-600 line-clamp-3">
                                            {!! $announcement->content !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <style>
        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .transition-all {
            transition-property: all;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 300ms;
        }
    </style>
@endsection