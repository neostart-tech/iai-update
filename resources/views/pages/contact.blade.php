@extends("layouts.master")

@section('head')
<style>
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
    
    .contact-form {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .contact-form:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 25px -5px rgba(13, 122, 55, 0.2), 0 10px 10px -5px rgba(13, 122, 55, 0.1);
    }
    
    .form-input {
        transition: all 0.3s ease;
        border: 2px solid #e5e7eb;
    }
    
    .form-input:focus {
        transform: translateY(-2px);
        border-color: #0D7A37;
        box-shadow: 0 0 0 3px rgba(13, 122, 55, 0.2);
    }
    
    .submit-btn {
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, #0D7A37 0%, #0a6930 100%);
    }
    
    .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(13, 122, 55, 0.3), 0 4px 6px -2px rgba(13, 122, 55, 0.15);
        background: linear-gradient(135deg, #0a6930 0%, #085726 100%);
    }
    
    .submit-btn:active {
        transform: translateY(0);
    }
    
    .logo-animation span {
        display: inline-block;
        position: relative;
        transform-style: preserve-3d;
        perspective: 500;
        -webkit-font-smoothing: antialiased;
        color: #0D7A37;
        font-weight: 700;
        transition: all 0.3s ease;
    }
    
    .logo-animation span:hover {
        transform: translateY(-5px);
        color: #fbef8b;
        text-shadow: 0 0 8px rgba(251, 239, 139, 0.5);
    }
    
    .logo-animation span::before,
    .logo-animation span::after {
        display: none;
        position: absolute;
        top: 0;
        left: -1px;
        transform-origin: left top;
        transition: all ease-out 0.3s;
        content: attr(data-text);
    }
    
    .logo-animation span::before {
        z-index: 1;
        color: rgba(0,0,0,0.2);
        transform: scale(1.1, 1) skew(0deg, 20deg);
    }
    
    .logo-animation span::after {
        z-index: 2;
        color: #0D7A37;
        text-shadow: -1px 0 1px #fbef8b, 1px 0 1px rgba(0,0,0,0.8);
        transform: rotateY(-40deg);
    }
    
    .logo-animation span:hover::before {
        transform: scale(1.1, 1) skew(0deg, 5deg);
    }
    
    .logo-animation span:hover::after {
        transform: rotateY(-10deg);
        color: #fbef8b;
    }
    
    .logo-animation span + span {
        margin-left: 0.3em;
    }
    
    @media (min-width: 20em) {
        .logo-animation {
            font-size: 1.5em;
        }
        .logo-animation span::before,
        .logo-animation span::after {
            display: block;
        }
    }
    
    @media (min-width: 30em) {
        .logo-animation {
            font-size: 2em;
        }
    }
    
    @media (min-width: 40em) {
        .logo-animation {
            font-size: 3em;
        }
    }
    
    @media (min-width: 60em) {
        .logo-animation {
            font-size: 4em;
        }
    }
</style>
@endsection

@section('content')
<div class="font-sans text-gray-800">
    <!-- Hero Section -->
    <div class="bg-no-repeat bg-center bg-cover w-full" style="background-image: url(https://images.unsplash.com/photo-1587560699334-bea93391dcef?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1470&q=80)">
        <div class="h-[50vh] md:h-[60vh] w-full flex items-center justify-center">
            <div class="w-full h-full text-white bg-gradient-to-r from-[#0D7A37]/90 to-[#fbef8b]/80 z-10 flex flex-col py-12 lg:py-0 gap-8 justify-center items-center animate-fade-in-up">
                <div class="flex flex-col items-center px-4 gap-4 text-center animate-fade-in">
                    <h1 class="text-white lg:text-5xl text-3xl font-bold uppercase">Contact</h1>
                    <span class="w-20 h-2 bg-[#fbef8b] animate-slide-up animate-delay-1"></span>
                    <p class="md:text-xl text-lg text-center font-bold max-w-4xl text-white animate-slide-up animate-delay-2">
                        Nous avons une équipe dévouée et une communauté dynamique qui sont prêts à vous soutenir tout au long de votre parcours scolaire.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Section -->
    <div class="bg-white py-16 md:py-20">
        <div class="container mx-auto px-4">
            <div class="flex flex-col lg:flex-row gap-12 items-start">
                <!-- Contact Form -->
                <div class="contact-form bg-white rounded-xl shadow-lg p-8 md:p-10 animate-fade-in w-full lg:w-1/2">
                    <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">Contactez-nous</h2>
                    
                    @if(session()->has('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 animate-slide-up" role="alert">
                            <span class="block sm:inline">{{ session()->pull('success') }}</span>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('contact.store') }}">
                        @csrf
                        <div class="mb-6 animate-slide-up animate-delay-1">
                            <label for="nom" class="block text-gray-700 font-medium mb-2">Nom</label>
                            <input
                                type="text"
                                name="nom"
                                class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0D7A37] focus:border-transparent"
                                id="nom"
                                placeholder="Votre nom"
                                required
                            />
                            @if($errors->has('nom'))
                                <span class="text-red-500 text-sm mt-1">{{$errors->first('nom')}}</span>
                            @endif
                        </div>
                        
                        <div class="mb-6 animate-slide-up animate-delay-2">
                            <label for="email" class="block text-gray-700 font-medium mb-2">Adresse email</label>
                            <input
                                type="email"
                                name="email"
                                class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0D7A37] focus:border-transparent"
                                id="email"
                                placeholder="Votre adresse email"
                                required
                            />
                            @if($errors->has('email'))
                                <span class="text-red-500 text-sm mt-1">{{$errors->first('email')}}</span>
                            @endif
                        </div>
                        
                        <div class="mb-6 animate-slide-up animate-delay-2">
                            <label for="tel" class="block text-gray-700 font-medium mb-2">Numéro de téléphone</label>
                            <input
                                type="tel"
                                name="tel"
                                class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0D7A37] focus:border-transparent"
                                id="tel"
                                placeholder="Votre numéro de téléphone"
                            />
                            @if($errors->has('tel'))
                                <span class="text-red-500 text-sm mt-1">{{$errors->first('tel')}}</span>
                            @endif
                        </div>
                        
                        <div class="mb-8 animate-slide-up animate-delay-3">
                            <label for="message" class="block text-gray-700 font-medium mb-2">Message</label>
                            <textarea
                                class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0D7A37] focus:border-transparent"
                                id="message"
                                name="message"
                                rows="4"
                                placeholder="Votre message"
                                required
                            ></textarea>
                            @if($errors->has('message'))
                                <span class="text-red-500 text-sm mt-1">{{$errors->first('message')}}</span>
                            @endif
                        </div>
                        
                        <button
                            type="submit"
                            class="submit-btn w-full text-white font-bold py-3 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0D7A37] focus:ring-opacity-50 transition-all duration-300 animate-slide-up animate-delay-3"
                        >
                            Envoyer le message
                        </button>
                    </form>
                </div>

                <!-- Map and Info Section -->
                <div class="w-full lg:w-1/2 animate-fade-in">
                    <!-- Map Section -->
                    <div class="rounded-xl shadow-lg overflow-hidden h-[300px] md:h-[350px] mb-8">
  <iframe 
    class="w-full h-full" 
    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3967.039120293!2d1.2085792754071871!3d6.125438004541796!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x10215f618ff4057f%3A0x283893dcd5d0ac3b!2sIAI%20-%20TOGO!5e0!3m2!1sfr!2stg!4v1667300695200!5m2!1sfr!2stg" 
    style="border:0;" 
    allowfullscreen="" 
    loading="lazy" 
    referrerpolicy="no-referrer-when-downgrade">
  </iframe>
</div>

                    
                    <!-- Contact Info -->
                    <div class="bg-white rounded-xl shadow-lg p-6 animate-slide-up">
                        <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                            <span class="bg-[#0D7A37] text-white p-2 rounded-full mr-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                            </span>
                            Informations de contact
                        </h3>
                        <div class="space-y-4">
                            <div class="flex items-center p-3 bg-[#fbef8b]/20 rounded-lg">
                                <div class="bg-[#0D7A37] rounded-full p-2 mr-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <p class="text-gray-700">Adresse de l'IAI-TOGO</p>
                            </div>
                            <div class="flex items-center p-3 bg-[#fbef8b]/20 rounded-lg">
                                <div class="bg-[#0D7A37] rounded-full p-2 mr-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.6841.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                </div>
                                <p class="text-gray-700">+228 22 20 47 00</p>
                            </div>
                            <div class="flex items-center p-3 bg-[#fbef8b]/20 rounded-lg">
                                <div class="bg-[#0D7A37] rounded-full p-2 mr-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <p class="text-gray-700">contact@iai-togo.tg</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Logo Animation Section (réduite) -->
    <div class="bg-white py-8 md:py-12">
        <div class="flex justify-center animate-fade-in">
            <div class="logo-animation flex">
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
</div>
@endsection