@extends('layouts.master')

@section('head')
<style>
    .hero-gradient {
        background: linear-gradient(135deg, 
            rgba(13, 122, 55, 0.85) 0%, 
            rgba(13, 122, 55, 0.78) 35%, 
            rgba(251, 239, 139, 0.8) 100%);
    }
    
    .opportunity-detail-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }
    
    .opportunity-detail-card:hover {
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        transform: translateY(-5px);
    }
    
    .badge {
        display: inline-flex;
        align-items: center;
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.875rem;
    }
    
    .apply-button {
        background: linear-gradient(135deg, #0D7A37, #0a5c2a);
        transition: all 0.3s ease;
    }
    
    .apply-button:hover {
        background: linear-gradient(135deg, #0a5c2a, #083c1c);
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(13, 122, 55, 0.3);
    }
    
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
    }
    
    .info-item {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 12px;
        border-left: 4px solid #0D7A37;
    }
    
    .content-section {
        line-height: 1.8;
    }
    
    .content-section h3 {
        color: #0D7A37;
        margin: 1.5rem 0 0.5rem 0;
    }
    
    .content-section ul {
        list-style-type: disc;
        padding-left: 1.5rem;
        margin: 0.5rem 0;
    }
    
    .content-section p {
        margin-bottom: 1rem;
    }
</style>
@endsection

@section('content')
<div>
    <!-- Bannière améliorée -->
    <div class="bg-no-repeat bg-center bg-cover w-full relative"
         style="background-image: url('{{ asset('img/cgu.jpg') }}')">
        <div class="absolute inset-0 hero-gradient"></div>
        <div class="h-[50vh] md:h-[60vh] w-full flex items-center justify-center relative">
            <div class="w-full h-full text-white flex flex-col py-12 lg:py-0 gap-6 justify-center items-center">
                <div class="flex flex-col items-center gap-4 px-4 text-center">
                    <h1 class="text-white text-3xl md:text-4xl lg:text-5xl font-bold uppercase tracking-wide">
                        Opportunité
                    </h1>
                    <div class="w-24 h-1.5 bg-[#fbef8b] rounded-full"></div>
                    <h2 class="text-xl md:text-2xl lg:text-3xl font-semibold text-center max-w-4xl">
                        {{ $announcement->title }}
                    </h2>
                    <div class="flex flex-wrap gap-3 justify-center mt-4">
                        <span class="badge bg-green-100 text-green-800">
                            {{ $announcement->type_contrat->value }}
                        </span>
                        <span class="badge bg-blue-100 text-blue-800">
                            {{ $announcement->type_annonce->value }}
                        </span>
                        <span class="badge bg-purple-100 text-purple-800">
                            {{ $announcement->ville }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Détails de l'opportunité -->
    <div class="max-w-7xl mx-auto px-4 md:px-8 py-12 md:py-16">
        <div class="opportunity-detail-card p-6 md:p-8">
            <!-- En-tête -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6 mb-8">
                <div>
                    <h3 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">
                        {{ $announcement->title }}
                    </h3>
                    <p class="text-green-700 font-medium">
                        Publié par: {{ $announcement->advertiser->nom }}
                    </p>
                </div>
            </div>

            <!-- Informations principales -->
            <div class="info-grid mb-8">
                <div class="info-item">
                    <h4 class="font-semibold text-gray-700 mb-2">📍 Lieu</h4>
                    <p class="text-gray-900">{{ $announcement->ville }}</p>
                </div>
                
                <div class="info-item">
                    <h4 class="font-semibold text-gray-700 mb-2">📝 Type de contrat</h4>
                    <p class="text-gray-900">{{ $announcement->type_contrat->value }}</p>
                </div>
                
                <div class="info-item">
                    <h4 class="font-semibold text-gray-700 mb-2">🏢 Type d'annonce</h4>
                    <p class="text-gray-900">{{ $announcement->type_annonce->value }}</p>
                </div>
                
                <div class="info-item">
                    <h4 class="font-semibold text-gray-700 mb-2">💰 Salaire</h4>
                    <p class="text-gray-900">{{ $announcement->salaire ?? 'À négocier' }}</p>
                </div>
                
                <div class="info-item">
                    <h4 class="font-semibold text-gray-700 mb-2">⏰ Durée</h4>
                    <p class="text-gray-900">{{ $announcement->duree ?? 'Non spécifiée' }}</p>
                </div>
                
                <div class="info-item">
                    <h4 class="font-semibold text-gray-700 mb-2">📅 Date de publication</h4>
                    <p class="text-gray-900">{{ $announcement->created_at->format('d/m/Y') }}</p>
                </div>
            </div>

            <!-- Description détaillée -->
            <div class="content-section mb-8">
                <h3 class="text-xl font-bold text-green-800 mb-4">Description du poste</h3>
                <div class="prose max-w-none">
                    {!! $announcement->content !!}
                </div>
            </div>

            <!-- Compétences requises -->
            <div class="content-section mb-8">
                <h3 class="text-xl font-bold text-green-800 mb-4">Compétences requises</h3>
                <div class="flex flex-wrap gap-2">
                    <span class="bg-gray-200 text-gray-800 px-3 py-1 rounded-full text-sm">PHP</span>
                    <span class="bg-gray-200 text-gray-800 px-3 py-1 rounded-full text-sm">JavaScript</span>
                    <span class="bg-gray-200 text-gray-800 px-3 py-1 rounded-full text-sm">MySQL</span>
                    <span class="bg-gray-200 text-gray-800 px-3 py-1 rounded-full text-sm">React</span>
                    <span class="bg-gray-200 text-gray-800 px-3 py-1 rounded-full text-sm">Node.js</span>
                    <span class="bg-gray-200 text-gray-800 px-3 py-1 rounded-full text-sm">Git</span>
                </div>
            </div>

            <!-- Avantages -->
            <div class="content-section mb-8">
                <h3 class="text-xl font-bold text-green-800 mb-4"> Avantages</h3>
                <ul class="list-disc list-inside space-y-2 text-gray-700">
                    <li>Environnement de travail dynamique et stimulant</li>
                    <li>Possibilité de formation continue</li>
                    <li>Mutuelle santé prise en charge</li>
                    <li>Ticket restaurant</li>
                    <li>Remote work partiel possible</li>
                </ul>
            </div>

            <!-- Processus de candidature -->
            <div class="content-section mb-8">
                <h3 class="text-xl font-bold text-green-800 mb-4"> Processus de candidature</h3>
                <ol class="list-decimal list-inside space-y-2 text-gray-700">
                    <li>Envoyer votre CV et lettre de motivation</li>
                    <li>Entretien téléphonique initial</li>
                    <li>Test technique (le cas échéant)</li>
                    <li>Entretien en présentiel</li>
                    <li>Réponse sous 48h</li>
                </ol>
            </div>

            <!-- CTA Final -->
            <div class="bg-green-50 rounded-xl p-6 text-center">
                <h3 class="text-2xl font-bold text-green-800 mb-4">Prêt à postuler ?</h3>
                <p class="text-gray-700 mb-6">Rejoignez notre équipe dynamique et développez vos compétences dans un environnement stimulant.</p>
                <a href="#" class="apply-button text-white font-bold py-3 px-8 rounded-lg inline-block">
                    Postuler maintenant
                </a>
                <p class="text-sm text-gray-600 mt-4">
                    Nous contacterons les candidats sélectionnés dans les 72h
                </p>
            </div>
        </div>
    </div>

    <!-- Opportunités similaires -->
    <div class="bg-gray-50 py-12 md:py-16">
        <div class="max-w-7xl mx-auto px-4 md:px-8">
            <h2 class="text-2xl md:text-3xl font-bold text-center text-gray-900 mb-8">
                Autres opportunités similaires
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Card 1 -->
                <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow">
                    <h4 class="font-bold text-lg text-gray-900 mb-2">Développeur Fullstack</h4>
                    <p class="text-green-700 text-sm mb-3">Lomé · CDI</p>
                    <p class="text-gray-600 text-sm mb-4">Rejoignez notre équipe technique pour développer des solutions innovantes...</p>
                    <a href="#" class="text-green-700 font-semibold text-sm hover:text-green-900">
                        Voir détails →
                    </a>
                </div>
                
                <!-- Card 2 -->
                <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow">
                    <h4 class="font-bold text-lg text-gray-900 mb-2">Data Analyst</h4>
                    <p class="text-green-700 text-sm mb-3">Kara · Stage</p>
                    <p class="text-gray-600 text-sm mb-4">Analysez des données complexes et aidez à la prise de décision stratégique...</p>
                    <a href="#" class="text-green-700 font-semibold text-sm hover:text-green-900">
                        Voir détails →
                    </a>
                </div>
                
                <!-- Card 3 -->
                <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow">
                    <h4 class="font-bold text-lg text-gray-900 mb-2">DevOps Engineer</h4>
                    <p class="text-green-700 text-sm mb-3">Lomé · Freelance</p>
                    <p class="text-gray-600 text-sm mb-4">Optimisez nos infrastructures cloud et automatisez nos processus de déploiement...</p>
                    <a href="#" class="text-green-700 font-semibold text-sm hover:text-green-900">
                        Voir détails →
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection