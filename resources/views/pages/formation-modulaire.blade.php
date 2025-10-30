@extends("layouts.master")

@section('head')
<style>
    /* Styles existants */
    .animate-fade-in {
        animation: fadeIn 0.8s ease-out forwards;
        opacity: 0;
    }
    
    .animate-slide-up {
        animation: slideUp 0.8s ease-out forwards;
        opacity: 0;
    }
    
    .animate-delay-1 {
        animation-delay: 0.2s;
    }
    
    .animate-delay-2 {
        animation-delay: 0.4s;
    }
    
    .animate-delay-3 {
        animation-delay: 0.6s;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    @keyframes slideUp {
        from { 
            opacity: 0;
            transform: translateY(20px);
        }
        to { 
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .formation-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .formation-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    
    .nav-tab {
        transition: all 0.3s ease;
        position: relative;
    }
    
    .nav-tab::after {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        width: 4px;
        background-color: #0D7A37;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .nav-tab.active {
        background-color: #fbef8b;
        color: #0D7A37;
        font-weight: bold;
    }
    
    .nav-tab.active::after {
        opacity: 1;
    }
    
    .nav-tab:hover {
        background-color: rgba(251, 239, 139, 0.3);
    }
    
    .contact-btn {
        transition: all 0.3s ease;
        background: linear-gradient(135deg, #0D7A37 0%, #0a6930 100%);
    }
    
    .contact-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(13, 122, 55, 0.3), 0 4px 6px -2px rgba(13, 122, 55, 0.15);
        background: linear-gradient(135deg, #0a6930 0%, #085726 100%);
    }
    
    /* Styles pour les sous-onglets */
    .sub-tabs-container {
        display: none;
        margin-top: 20px;
        padding: 0 20px;
    }
    
    .sub-tabs {
        display: flex;
        justify-content: center;
        margin-bottom: 30px;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .sub-tab {
        padding: 12px 24px;
        cursor: pointer;
        transition: all 0.3s ease;
        border-bottom: 3px solid transparent;
        margin: 0 5px;
        font-weight: 600;
        color: #4a5568;
    }
    
    .sub-tab.active {
        color: #0D7A37;
        border-bottom: 3px solid #0D7A37;
        background-color: rgba(251, 239, 139, 0.2);
    }
    
    .sub-tab:hover {
        color: #0D7A37;
        background-color: rgba(251, 239, 139, 0.1);
    }
    
    .sub-tab-content {
        display: none;
        animation: fadeIn 0.5s ease;
    }
    
    .sub-tab-content.active {
        display: block;
    }
</style>
@endsection

@section('content')
<div class="font-sans text-gray-800 min-h-screen flex flex-col lg:flex-row">
    <!-- Navigation verticale à gauche -->
    <div class="w-full lg:w-1/4 bg-white shadow-lg p-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-6 border-b-2 border-[#0D7A37] pb-2">Types de Formation</h2>
        <ul class="space-y-2">
            <li>
                <button class="nav-tab w-full text-left py-3 px-4 rounded-lg active" data-tab="alternance">
                    Formation par Alternance
                </button>
            </li>
            <li>
                <button class="nav-tab w-full text-left py-3 px-4 rounded-lg" data-tab="certifiante">
                    Formation Certifiante
                </button>
            </li>
            <li>
                <button class="nav-tab w-full text-left py-3 px-4 rounded-lg" data-tab="continue">
                    Formation Continue
                </button>
            </li>
            <li>
                <button class="nav-tab w-full text-left py-3 px-4 rounded-lg" data-tab="modulaire">
                    Formation Modulaire
                </button>
            </li>
        </ul>
        
        <div class="mt-8 p-4 bg-[#fbef8b]/20 rounded-lg">
            <h3 class="font-bold text-lg mb-2">Besoin d'aide?</h3>
            <p class="text-sm mb-4">Notre équipe est à votre disposition pour vous guider dans le choix de votre formation.</p>
            <a href="{{ route('contact') }}" class="contact-btn block text-center text-white font-bold py-2 px-4 rounded-lg">
                Nous contacter
            </a>
        </div>
    </div>

    <!-- Contenu principal -->
    <div class="w-full lg:w-3/4 bg-gray-50">

     <!-- Contenu pour Formation par Alternance -->
     <div class="tab-content active" id="alternance">
            <div class="bg-no-repeat bg-center bg-cover w-full" style="background-image: url(https://images.unsplash.com/photo-1521587760476-6c12a4b040da?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1170&q=80)">
                <div class="h-[40vh] md:h-[50vh] w-full flex items-center justify-center">
                    <div class="w-full h-full text-white bg-gray-700 bg-opacity-80 flex flex-col py-8 lg:py-0 gap-6 justify-center items-center">
                        <div class="flex flex-col items-center px-4 gap-4 text-center animate-fade-in">
                            <h1 class="text-white lg:text-4xl text-2xl font-bold uppercase">FORMATION PAR ALTERNANCE</h1>
                            <span class="w-20 h-2 bg-[#fbef8b] animate-slide-up animate-delay-1"></span>
                            <p class="text-lg text-center font-bold max-w-3xl animate-slide-up animate-delay-2">
                            Un type de formation qui offre aux étudiants la possibilité d'apprendre les compétences nécessaires pour
                            travailler dans le domaine de l'informatique de manière non traditionnelle.
                            </p>
                        </div>
                        <a href="{{ route('contact') }}" class="contact-btn mt-8 py-3 px-6 animate-slide-up animate-delay-3">
                            <p class="font-bold text-lg">Nous Contacter</p>
                        </a>
                    </div>
                </div>
            </div>

            <div class="container mx-auto py-12 px-4">
                <div class="mb-10 text-center">
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">FORMATION PAR ALTERNANCE</h2>
                </div>

                <!-- Sous-onglets pour Formation alternance -->
                <div class="sub-tabs-container" id="alternance-sub-tabs">
                    <div class="sub-tabs">
                        <div class="sub-tab active" data-subtab="genie-logiciel">Licence Multimédia</div>
                        <div class="sub-tab" data-subtab="systeme-reseau">Technologie Web</div>
                        <div class="sub-tab" data-subtab="multimedia">Infographie (M-TWI)</div>
                    </div>

                    <!-- Contenu pour  multimédia-->
                    <div class="sub-tab-content active" id="genie-logiciel">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="formation-card bg-white rounded-xl overflow-hidden shadow-lg h-full animate-fade-in">
                                <img class="w-full h-48 object-cover" src="img/Licence Multimédia.jpg" alt="Génie Logiciel">
                                <div class="p-6 flex flex-col gap-4">
                                    <h3 class="text-xl font-bold text-[#0D7A37]">Licence Multimédia</h3>
                                    <p class="text-gray-700">
                                    La licence Multimédia est une formation pluridisciplinaire qui allie connaissances théoriques et pratiques. Elle permet aux étudiants de développer leurs compétences en matière de création de contenu multimédia, de développement de sites web, de design graphique, d'animation 3D, de programmation et de bien d'autres domaines encore.
                                    </p>
                                </div>
                            </div> 
                        </div>
                    </div>

                    <!-- Contenu pour Technologie Web -->
                    <div class="sub-tab-content" id="systeme-reseau">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="formation-card bg-white rounded-xl overflow-hidden shadow-lg h-full animate-fade-in">
                                <img class="w-full h-48 object-cover" src="{{ asset('img/Technologie Web.jpg') }}" alt="Administration Système">
                                <div class="p-6 flex flex-col gap-4">
                                    <h3 class="text-xl font-bold text-[#0D7A37]">Technologie Web</h3>
                                    <p class="text-gray-700">
                                    La technologie Web est un domaine en constante évolution qui regroupe l'ensemble des technologies utilisées pour développer et mettre en ligne des sites web et des applications web. Il comprend de nombreux langages de programmation tels que HTML, CSS, JavaScript et PHP, ainsi que des frameworks tels que React, Angular et Vue.js.
                                    </p>
                                </div>
                            </div>                   
                        </div>
                    </div>

                    <!-- Contenu pour Infographie (M-TWI) -->
                    <div class="sub-tab-content" id="multimedia">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="formation-card bg-white rounded-xl overflow-hidden shadow-lg h-full animate-fade-in">
                                <img class="w-full h-48 object-cover" src="{{ asset('img/Design Graphique et UIUX.jpg') }}" alt="Design Graphique">
                                <div class="p-6 flex flex-col gap-4">
                                    <h3 class="text-xl font-bold text-[#0D7A37]">Infographie (M-TWI)</h3>
                                    <p class="text-gray-700">
                                    L'infographie (M-TWI) est une formation universitaire qui vise à fournir aux étudiants les compétences nécessaires pour travailler dans le domaine de l'infographie et de la communication visuelle. Elle couvre un large éventail de domaines, tels que la conception graphique, la photographie, la vidéographie, la réalisation de films, la direction artistique et bien d'autres encore.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenu pour Formation Certifiante -->
        <div class="tab-content hidden" id="certifiante">
            <div class="bg-no-repeat bg-center bg-cover w-full" style="background-image: url(https://images.unsplash.com/photo-1521587760476-6c12a4b040da?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1170&q=80)">
                <div class="h-[40vh] md:h-[50vh] w-full flex items-center justify-center">
                    <div class="w-full h-full text-white bg-gray-700 bg-opacity-80 flex flex-col py-8 lg:py-0 gap-6 justify-center items-center">
                        <div class="flex flex-col items-center px-4 gap-4 text-center animate-fade-in">
                            <h1 class="text-white lg:text-4xl text-2xl font-bold uppercase">Formation Certifiante</h1>
                            <span class="w-20 h-2 bg-[#fbef8b] animate-slide-up animate-delay-1"></span>
                            <p class="text-lg text-center font-bold max-w-3xl animate-slide-up animate-delay-2">
                                Vous souhaitez vous spécialiser dans un domaine précis de l'informatique et obtenir une certification reconnue par les professionnels du secteur ?
                            </p>
                        </div>
                        <a href="{{ route('contact') }}" class="contact-btn mt-8 py-3 px-6 animate-slide-up animate-delay-3">
                            <p class="font-bold text-lg">Nous Contacter</p>
                        </a>
                    </div>
                </div>
            </div>

            <div class="container mx-auto py-12 px-4">
                <div class="mb-10 text-center">
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">FORMATIONS CERTIFIANTES</h2>
                </div>

                <!-- Sous-onglets pour Formation Certifiante -->
                <div class="sub-tabs-container" id="certifiante-sub-tabs" style="display:none;">
                    <div class="sub-tabs">
                        <div class="sub-tab active" data-subtab="cert-prog-1">Certification CCNA Cisco Sécurité</div>
                        <div class="sub-tab" data-subtab="cert-prog-2">Computer Science</div>
                        <div class="sub-tab" data-subtab="cert-prog-3">Certification CCNA Cisco</div>
                    </div>

                    <div class="sub-tab-content active" id="cert-prog-1">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="formation-card bg-white rounded-xl overflow-hidden shadow-lg h-full animate-fade-in">
                                <img class="w-full h-48 object-cover" src="{{ asset('img/Fondamentaux Réseaux (CCNA).jpg') }}" alt="Fondamentaux Réseaux">
                                <div class="p-6 flex flex-col gap-4">
                                    <h3 class="text-xl font-bold text-[#0D7A37]">Fondamentaux Réseaux (CCNA)</h3>
                                    <p class="text-gray-700">Préparation aux concepts réseaux, adressage IP, routage et switching.</p>
                                </div>
                            </div>
                            <div class="formation-card bg-white rounded-xl overflow-hidden shadow-lg h-full animate-fade-in animate-delay-1">
                                <img class="w-full h-48 object-cover" src="https://images.unsplash.com/photo-1518770660439-4636190af475?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlf极8fGVufDB8fHx8&auto=format&fit=crop&w=1170&q=80" alt="Certification CCNA">
                                <div class="p-6 flex flex-col gap-4">
                                    <h3 class="text-xl font-bold text-[#0D7A37]">Certification CCNA Cisco Sécurité</h3>
                                    <p class="text-gray-700">Certification de niveau professionnelle qui valide les compétences en matière de sécurité des réseaux informatiques. Reconnue internationalement.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="sub-tab-content" id="cert-prog-2">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="formation-card bg-white rounded-xl overflow-hidden shadow-lg h-full animate-fade-in">
                                <img class="w-full h-48 object-cover" src="{{ asset('img/Ateliers pratiques.jpg') }}" alt="Ateliers pratiques">
                                <div class="p-6 flex flex-col gap-4">
                                    <h3 class="text-xl font-bold text-[#0D7A37]">Ateliers pratiques</h3>
                                    <p class="text-gray-700">Laboratoires, simulations et cas réels pour consolider les acquis.</p>
                                </div>
                            </div>
                            <div class="formation-card bg-white rounded-xl overflow-hidden shadow-lg h-full animate-fade-in animate-delay-1">
                                <img class="w-full h-48 object-cover" src="https://plus.unsplash.com/premium_photo-1661644858773-b02cfddc793a?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1169&q=80" alt="Certification CCNA">
                                <div class="p-6 flex flex-col gap-4">
                                    <h3 class="text-xl font-bold text-[#0D7A37]">Computer Science</h3>
                                    <p class="text-gray-700"> Obtenir cette certification permet de démontrer une solide expertise en informatique et de posséder les compétences nécessaires pour résoudre des problèmes complexes.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="sub-tab-content" id="cert-prog-3">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="formation-card bg-white rounded-xl overflow-hidden shadow-lg h-full animate-fade-in">
                                <img class="w-full h-48 object-cover" src="{{ asset('img/devops.jpg') }}" alt="Cloud & DevOps">
                                <div class="p-6 flex flex-col gap-4">
                                    <h3 class="text-xl font-bold text-[#0D7A37]">Cloud & DevOps</h3>
                                    <p class="text-gray-700">Conteneurs, CI/CD et déploiements cloud pour la production.</p>
                                </div>
                            </div>
                            <div class="formation-card bg-white rounded-xl overflow-hidden shadow-lg h-full animate-fade-in animate-delay-1">
                                <img class="w-full h-48 object-cover" src="https://plus.unsplash.com/premium_photo-1661644858773-b02cfddc793a?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1169&q=80" alt="Certification CCNA">
                                <div class="p-6 flex flex-col gap-4">
                                    <h3 class="text-xl font-bold text-[#0D7A37]">Certification CCNA Cisco</h3>
                                    <p class="text-gray-700">Obtenir cette certification permet de démontrer une expertise solide dans la mise en place et la gestion de la sécurité des réseaux informatiques.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        

        <!-- Contenu pour Formation Continue -->
        <div class="tab-content hidden" id="continue">
            <div class="bg-no-repeat bg-center bg-cover w-full" style="background-image: url(https://images.unsplash.com/photo-1521587760476-6c12a4b040da?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1170&q=80)">
                <div class="h-[40vh] md:h-[50vh] w-full flex items-center justify-center">
                    <div class="w-full h-full text-white bg-gray-700 bg-opacity-80 flex flex-col py-8 lg:py-0 gap-6 justify-center items-center">
                        <div class="flex flex-col items-center px-4 gap-4 text-center animate-fade-in">
                            <h1 class="text-white lg:text-4xl text-2xl font-bold uppercase">FORMATION CONTINUE</h1>
                            <span class="w-20 h-2 bg-[#fbef8b] animate-slide-up animate-delay-1"></span>
                            <p class="text-lg text-center font-bold max-w-3xl animate-slide-up animate-delay-2">
                                Notre objectif est de vous aider à maintenir et à améliorer vos compétences en informatique afin que vous puissiez rester à jour dans un domaine qui évolue rapidement.
                            </p>
                        </div>
                        <a href="{{ route('contact') }}" class="contact-btn mt-8 py-3 px-6 animate-slide-up animate-delay-3">
                            <p class="font-bold text-lg">Nous Contacter</p>
                        </a>
                    </div>
                </div>
            </div>

            <div class="container mx-auto py-12 px-4">
                <div class="mb-10 text-center">
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">FORMATIONS CONTINUES</h2>
                </div>

                <!-- Sous-onglets pour Formation Continue -->
                <div class="sub-tabs-container" id="continue-sub-tabs">
                    <div class="sub-tabs">
                        <div class="sub-tab active" data-subtab="genie-logiciel">Génie Logiciel</div>
                        <div class="sub-tab" data-subtab="systeme-reseau">Système et Réseaux</div>
                        <div class="sub-tab" data-subtab="multimedia">Multimédia</div>
                    </div>

                    <!-- Contenu pour Génie Logiciel -->
                    <div class="sub-tab-content active" id="genie-logiciel">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="formation-card bg-white rounded-xl overflow-hidden shadow-lg h-full animate-fade-in">
                                <img class="w-full h-48 object-cover" src="https://exploreengineering.ca/sites/default/files/2020-02/NEM_software.jpg" alt="Génie Logiciel">
                                <div class="p-6 flex flex-col gap-4">
                                    <h3 class="text-xl font-bold text-[#0D7A37]">Développement Web Fullstack</h3>
                                    <p class="text-gray-700">
                                        Maîtrisez les technologies front-end et back-end pour créer des applications web complètes et performantes.
                                    </p>
                                </div>
                            </div>

                            <div class="formation-card bg-white rounded-xl overflow-hidden shadow-lg h-full animate-fade-in animate-delay-1">
                                <img class="w-full h-48 object-cover" src="{{ asset('img/Développement d\'Applications Mobiles.jpg') }}" alt="Applications Mobiles">
                                <div class="p-6 flex flex-col gap-4">
                                    <h3 class="text-xl font-bold text-[#0D7A37]">Développement d'Applications Mobiles</h3>
                                    <p class="text-gray-700">
                                        Apprenez à créer des applications natives et hybrides pour iOS et Android avec les technologies modernes.
                                    </p>
                                </div>
                            </div>

                            
                        </div>
                    </div>

                    <!-- Contenu pour Système et Réseaux -->
                    <div class="sub-tab-content" id="systeme-reseau">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="formation-card bg-white rounded-xl overflow-hidden shadow-lg h-full animate-fade-in">
                                <img class="w-full h-48 object-cover" src="{{ asset('img/Réseaux et Sécurité.jpg') }}" alt="Administration Système">
                                <div class="p-6 flex flex-col gap-4">
                                    <h3 class="text-xl font-bold text-[#0D7A37]">Administration Système Linux/Windows</h3>
                                    <p class="text-gray-700">
                                        Devenez expert dans l'administration des systèmes d'exploitation serveurs et la gestion des infrastructures.
                                    </p>
                                </div>
                            </div>

                            <div class="formation-card bg-white rounded-xl overflow-hidden shadow-lg h-full animate-fade-in animate-delay-1">
                                <img class="w-full h-48 object-cover" src="https://images.pexels.com/photos/3861972/pexels-photo-3861972.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1" alt="Réseaux">
                                <div class="p-6 flex flex-col gap-4">
                                    <h3 class="text-xl font-bold text-[#0D7A37]">Réseaux et Sécurité</h3>
                                    <p class="text-gray-700">
                                        Apprenez à concevoir, implémenter et sécuriser des infrastructures réseau d'entreprise.
                                    </p>
                                </div>
                            </div>

                            
                        </div>
                    </div>

                    <!-- Contenu pour Multimédia -->
                    <div class="sub-tab-content" id="multimedia">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="formation-card bg-white rounded-xl overflow-hidden shadow-lg h-full animate-fade-in">
                                <img class="w-full h-48 object-cover" src="{{ asset('img/Design Graphique et UIUX.jpg') }}" alt="Design Graphique">
                                <div class="p-6 flex flex-col gap-4">
                                    <h3 class="text-xl font-bold text-[#0D7A37]">Design Graphique et UI/UX</h3>
                                    <p class="text-gray-700">
                                        Créez des interfaces utilisateur attrayantes et intuitives avec les principes du design graphique et de l'expérience utilisateur.
                                    </p>
                                </div>
                            </div>

                            <div class="formation-card bg-white rounded-xl overflow-hidden shadow-lg h-full animate-fade-in animate-delay-1">
                                <img class="w-full h-48 object-cover" src="{{ asset('img/Animation 3D et Effets Spéciaux.jpg') }}" alt="Animation 3D">
                                <div class="p-6 flex flex-col gap-4">
                                    <h3 class="text-xl font-bold text-[#0D7A37]">Animation 3D et Effets Spéciaux</h3>
                                    <p class="text-gray-700">
                                        Maîtrisez les techniques d'animation 3D et de création d'effets visuels pour le cinéma, la TV et les jeux vidéo.
                                    </p>
                                </div>
                            </div>

                            <div class="formation-card bg-white rounded-xl overflow-hidden shadow-lg h-full animate-fade-in animate-delay-2">
                                <img class="w-full h-48 object-cover" src="{{ asset('img/Production Vidéo et Montage.jpg') }}" alt="Montage Vidéo">
                                <div class="p-6 flex flex-col gap-4">
                                    <h3 class="text-xl font-bold text-[#0D7A37]">Production Vidéo et Montage</h3>
                                    <p class="text-gray-700">
                                        Apprenez les techniques professionnelles de tournage, montage et post-production vidéo.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenu pour Formation Modulaire -->
        <div class="tab-content hidden" id="modulaire">
            <div class="bg-no-repeat bg-center bg-cover w-full" style="background-image: url(https://images.unsplash.com/photo-1521587760476-6c12a4b040da?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1170&q=80)">
                <div class="h-[40vh] md:h-[50vh] w-full flex items-center justify-center">
                    <div class="w-full h-full text-white bg-gray-700 bg-opacity-80 flex flex-col py-8 lg:py-0 gap-6 justify-center items-center">
                        <div class="flex flex-col items-center px-4 gap-4 text-center animate-fade-in">
                            <h1 class="text-white lg:text-4xl text-2xl font-bold uppercase">Formation Modulaire</h1>
                            <span class="w-20 h-2 bg-[#fbef8b] animate-slide-up animate-delay-1"></span>
                            <p class="text-lg text-center font-bold max-w-3xl animate-slide-up animate-delay-2">
                                Notre école d'informatique est fière de vous proposer une formation modulaire de qualité qui vous permettra de développer vos compétences.
                            </p>
                        </div>
                        <a href="{{ route('contact') }}" class="contact-btn mt-8 py-3 px-6 animate-slide-up animate-delay-3">
                            <p class="font-bold text-lg">Nous Contacter</p>
                        </a>
                    </div>
                </div>
            </div>

            <div class="container mx-auto py-12 px-4">
                <div class="mb-10 text-center">
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">FORMATIONS MODULAIRES</h2>
                </div>

                <!-- Sous-onglets pour Formation Modulaire -->
                <div class="sub-tabs-container" id="modulaire-sub-tabs" style="display:none;">
                    <div class="sub-tabs">
                        <div class="sub-tab active" data-subtab="mod-pack-1">Technologie web</div>
                        <div class="sub-tab" data-subtab="mod-pack-2">Multimédia</div>
                        <div class="sub-tab" data-subtab="mod-pack-3">Infographie</div>
                    </div>

                    <div class="sub-tab-content active" id="mod-pack-1">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="formation-card bg-white rounded-xl overflow-hidden shadow-lg h-full animate-fade-in">
                                <img class="w-full h-48 object-cover" src="https://images.pexels.com/photos/270404/pexels-photo-270404.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1" alt="HTML/CSS">
                                <div class="p-6 flex flex-col gap-4">
                                    <h3 class="text-xl font-bold text-[#0D7A37]">HTML/CSS</h3>
                                    <p class="text-gray-700">Bases du web, responsive design et accessibilité.</p>
                                </div>
                            </div>
                            <div class="formation-card bg-white rounded-xl overflow-hidden shadow-lg h-full animate-fade-in animate-delay-1">
                                <img class="w-full h-48 object-cover" src="https://images.unsplash.com/photo-1610563166150-b34df4f3bcd6?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1076&q=80" alt="Technologie web">
                                <div class="p-6 flex flex-col gap-4">
                                    <h3 class="text-xl font-bold text-[#0D7A37]">Technologie web</h3>
                                    <p class="text-gray-700">  Concevoir la mise en page de sites web en tenant compte des critères ergonomiques, du référencement et de l'accessibilité.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="sub-tab-content" id="mod-pack-2">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="formation-card bg-white rounded-xl overflow-hidden shadow-lg h-full animate-fade-in">
                                <img class="w-full h-48 object-cover" src="{{ asset('img/Multimédia.jpg') }}" alt="Multimédia">
                                <div class="p-6 flex flex-col gap-4">
                                    <h3 class="text-xl font-bold text-[#0D7A37]">Multimédia</h3>
                                    <p class="text-gray-700">Concevoir une animation pour le Web ou pour l'affichage dynamique. Réaliser une animation en 2D et 3D.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="sub-tab-content" id="mod-pack-3">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

                            <div class="formation-card bg-white rounded-xl overflow-hidden shadow-lg h-full animate-fade-in animate-delay-1">
                                <img class="w-full h-48 object-cover" src="http://res.cloudinary.com/arinfo-la-roche/image/upload/v1621968742/iajs0ltwch6fom1oafs5.jpg" alt="Infographie">
                                <div class="p-6 flex flex-col gap-4">
                                    <h3 class="text-xl font-bold text-[#0D7A37]">Infographie</h3>
                                    <p class="text-gray-700">Préparer la production et les médias pour une meilleure visibilité de l'entreprise. Réaliser des graphismes et illustrations fixes élaborés.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>                  
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tabs = document.querySelectorAll('.nav-tab');
        const tabContents = document.querySelectorAll('.tab-content');
        const subTabsContainers = document.querySelectorAll('.sub-tabs-container');

        // Initialisation des sous-onglets dans un conteneur donné
        function initSubTabs(container) {
            const subTabs = container.querySelectorAll('.sub-tab');
            const subContents = container.querySelectorAll('.sub-tab-content');

            const activateSub = (subId) => {
                subTabs.forEach(st => st.classList.remove('active'));
                subContents.forEach(sc => sc.classList.remove('active'));
                const activeBtn = Array.from(subTabs).find(st => st.getAttribute('data-subtab') === subId);
                const activeContent = container.querySelector(`#${subId}`);
                if (activeBtn) activeBtn.classList.add('active');
                if (activeContent) activeContent.classList.add('active');
            };

            // Définir un sous-onglet par défaut
            if (subTabs.length > 0) {
                activateSub(subTabs[0].getAttribute('data-subtab'));
            }

            // Écouteurs de clic pour les sous-onglets
            subTabs.forEach(st => {
                st.addEventListener('click', () => {
                    activateSub(st.getAttribute('data-subtab'));
                });
            });
        }
        
        // Fonction pour changer d'onglet
        function switchTab(tabId) {
            // Masquer tous les contenus d'onglets
            tabContents.forEach(content => {
                content.classList.add('hidden');
                content.classList.remove('active');
            });
            
            // Afficher le contenu de l'onglet sélectionné
            const targetContent = document.getElementById(tabId);
            if (targetContent) {
                targetContent.classList.remove('hidden');
                targetContent.classList.add('active');
            }
            
            // Mettre à jour l'onglet actif
            tabs.forEach(tab => {
                tab.classList.remove('active');
                if (tab.getAttribute('data-tab') === tabId) {
                    tab.classList.add('active');
                }
            });
            
            // Afficher uniquement les sous-onglets de l'onglet actif
            subTabsContainers.forEach(ctn => { ctn.style.display = 'none'; });
            if (targetContent) {
                const activeContainer = targetContent.querySelector('.sub-tabs-container');
                if (activeContainer) {
                    activeContainer.style.display = 'block';
                    if (!activeContainer.dataset.inited) {
                        initSubTabs(activeContainer);
                        activeContainer.dataset.inited = '1';
                    }
                }
            }

            // Mettre à jour l'URL avec le paramètre ?tab=
            try {
                const url = new URL(window.location.href);
                url.searchParams.set('tab', tabId);
                window.history.replaceState({}, '', url.toString());
            } catch (e) {
                // no-op si URL API indisponible
            }
        }
        
        // Ajouter l'événement de clic aux onglets
        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                const target = this.getAttribute('data-tab');
                switchTab(target);
            });
        });
        
        // Gestion des sous-onglets pour Formation Continue
        const subTabs = document.querySelectorAll('.sub-tab');
        const subTabContents = document.querySelectorAll('.sub-tab-content');
        
        subTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                const target = this.getAttribute('data-subtab');
                
                // Mettre à jour l'onglet actif
                subTabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                // Afficher le contenu correspondant
                subTabContents.forEach(content => {
                    content.classList.remove('active');
                    if (content.id === target) {
                        content.classList.add('active');
                    }
                });
            });
        });
        
        // Déterminer l'onglet initial depuis l'URL (?tab= or /<tab>)
        const allowedTabs = ['alternance', 'certifiante', 'modulaire', 'continue'];
        const params = new URLSearchParams(window.location.search);
        let initialTab = params.get('tab');

        if (!initialTab) {
            const parts = window.location.pathname.split('/').filter(Boolean);
            const last = parts[parts.length - 1];
            if (allowedTabs.includes(last)) {
                initialTab = last;
            }
        }

        // Fallback si aucun onglet valide trouvé
        if (!allowedTabs.includes(initialTab)) {
            initialTab = 'alternance';
        }

        if (tabs.length > 0) {
            switchTab(initialTab);
        }
    });
</script>
@endsection