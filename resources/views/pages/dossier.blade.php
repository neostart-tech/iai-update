@extends("layouts.master")


@section('content')
<div>
    <div class="bg-no-repeat bg-center bg-cover w-full" style="background-image: url(https://images.unsplash.com/photo-1521587760476-6c12a4b040da?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1170&q=80)">
        <div class="h-[60vh]  w-full flex items-center justify-center">
            <div class="w-full h-full text-white bg-gray-700 bg-opacity-80 flex flex-col py-12 lg:py-0 gap-8 justify-center items-center">
                <div class="flex flex-col items-center px-4 gap-4 text-center">
                    <h1 class="text-white lg:text-5xl text-lg font-bold uppercase">Dossiers d'inscription </h1>
                    <span class="w-20 h-2 bg-[#b09d72]"></span>
                    <h1 class="lg:text-xl text-center font-bold">Nous sommes ravis de vous annoncer que la période de dépôt de dossier pour notre école d'informatique est maintenant ouverte ! <br> Si vous souhaitez vous inscrire à notre école et suivre une formation de haut niveau pour devenir un professionnel qualifié <br>dans le domaine de l'informatique,  il est temps de préparer votre dossier.</h1>
                </div>

                <a href="/contact" class="border-4 border-[#b09d72] rounded duration-500 hover:bg-[#b09d72] mt-4 lg:mt-12 py-4 px-4 lg:px-8">
                    <p class="font-semibold lg:font-bold lg:text-xl">Nous Contacter</p>
                </a>
            </div>
        </div>
    </div>
    <div class="container mx-auto mt-12 mb-8 lg:my-16 flex flex-col items-center">
        <div class="flex flex-col items-center justify-center w-full lg:w-2/3">
            <h2 class="text-xl lg:text-3xl font-semibold lg:font-bold text-green-800 text-center p-2  mb-4 uppercase">DOSSIER D'INSCRIPTION AU CONCOURS D'ENTRée A L'IAI-TOGO.</h2>
            <span class="text-gray-700 px-6 text-justify lg:text-xl text-md">
                <p class="mb-4">
                Etudiants passionnés d'informatique ! Les dépôts de dossiers pour notre école d'informatique prestigieuse sont désormais ouverts. Si vous souhaitez poursuivre vos études dans le domaine de l'informatique et bénéficier d'une formation de qualité, ne manquez pas cette occasion ! Nous offrons des programmes adaptés aux différents niveaux et des cours enseignés par des professionnels expérimentés. Les dossiers de candidatures peuvent être déposés jusqu'au [date limite]. N'attendez plus, postulez dès maintenant !
                </p>
            </span>
        </div>
    </div>



<!--    List formations-->
    <div class="md:mt-24 md:pb-16 px-4 lg:px-6 flex justify-center w-full flex flex-col bg-[#EEE6D8] pt-20">
        <section class="text-gray-800 text-center md:text-left">
            <div class=" grid md:grid-cols-2 gap-x-6 md:gap-x-2 items-center">

                <div class="relative overflow-hidden mb-12 bg-no-repeat bg-cover rounded-lg relative overflow-hidden bg-no-repeat bg-cover ripple shadow-lg"
                         data-mdb-ripple="true" data-mdb-ripple-color="light">
                        <img src="https://www.burkina.campusfrance.org/sites/pays/files/burkina/styles/mobile_visuel_principal_page/public/AdobeStock_83999744_0.jpeg?itok=HRho2pz4"
                             class="w-full h-[45vh]" alt="Louvre" />
                        <a href="#!">
                            <div class="absolute top-0 right-0 bottom-0 left-0 w-full h-full overflow-hidden bg-fixed opacity-0 hover:opacity-100 transition duration-300 ease-in-out"
                                 style="background-color: rgba(251, 251, 251, 0.2)"></div>
                        </a>
                </div>

                <a href="#" class="mb-6 hover:opacity-80 md:mb-0 h-full w-full">
                    <div class="px-6 h-full">
                        <h3 class="text-xl lg:text-4xl text-red-600 uppercase font-bold lg:mb-6 mb-4">DOSSIER à FOURNIR </h3>
                        <div class="flex justify-center md:justify-start">
                            <div class="w-32 h-2 bg-gradient-to-r from-red-600 via-white to-green-800 lg:mb-6 mb-4"></div>
                        </div>
                        <div class="mb-3 py-2 px-4 text-white bg-green-800 font-medium text-sm rounded-lg inline-flex items-center gap-4 justify-center md:justify-start">

                            <p class="text-xl uppercase font-semibold">7 FICHIERS</p>
                        </div>
                        <p class="text-gray-700 uppercase font-bold lg:text-lg text-xl lg:mb-6 mb-2">
                            <small>Dossier d'inscription au concours d'entrée</small>
                        </p>
                        <p class="text-black lg:text-base   font-semibold">
                           - Une demande d'inscription manuscrite adressée au Représentant Résident <br>
                           - Une copie légalisée de l'extrait de naissance <br>
                           - Une copie légalisée du certificat de nationalité <br>
                           - Une copie légalisée du diplôme requis (BAC2 ou DUT) <br>
                           - Deux (02) photos d'identité <br>
                           - Un certificat Médical datant de moins de 3 mois <br>
                           - Un coupon-réponse international pour l'authentification des diplômes délivrés à l'étranger
                        </p>
                    </div>
                </a>
            </div>
        </section>

        <div class="text-black lg:py-6 lg:px-8  text-center lg:text-left">
            <h1 class="text-xl lg:text-4xl font-semibold mb-2 uppercase">NB:</h1>
            <p class="lg:text-xl text-gray-800 text-justify-center">Il est important de noter que la période de dépôt de dossier est limitée, il est donc important de ne pas attendre pour déposer votre dossier et de vous assurer qu'il est complet.
                Si vous avez des questions ou des difficultés lors du dépôt de votre dossier, n'hésitez pas à nous contacter, nous nous tenons à votre disposition pour vous assister. Ne manquez pas cette occasion de vous perfectionner dans le domaine de l'informatique, postulez dès maintenant!"</p><br>
            <div class="pb-6 pt-4 lg:mt-0">
                <a class="inline-block  px-7 py-3 bg-green-800 text-white font-bold text-sm leading-snug uppercase rounded-lg focus:outline-none focus:ring-0 transition duration-150 ease-in-out" data-mdb-ripple="true" data-mdb-ripple-color="light" href="/contact" role="button">Contactez-nous</a>
            </div>
        </div>

    </div>

    <section class="bg-white px-4 md:px-6 lg:px-0">
        <div id="controls-carousel" class="relative w-full h-full " data-carousel="static">
            <!-- Carousel wrapper -->
            <div class="relative h-full overflow-hidden md:h-full flex justify-center items-center py-20">
                <!-- Item 1 -->
                <div class="duration-700 ease-in-out transition-all transform -translate-x-full z-10 py-8 px-20"
                     data-carousel-item="">
                    <div class="grid grid-cols-2 gap-8 md:grid-cols-3">
                        <div class="col-span-1 flex items-center justify-center md:col-span-2 lg:col-span-1">
                            <img class="h-24" src="/img/partenaires/troyes.png" alt="partner-logo">
                        </div>

                        <div class="col-span-1 flex items-center justify-center md:col-span-2 lg:col-span-1">
                            <img class="h-24" src="/img/partenaires/belfort.jpg" alt="partner-logo">
                        </div>

                        <div class="col-span-1 flex items-center justify-center md:col-span-2 lg:col-span-1">
                            <img class="h-24" src="/img/partenaires/ul.png" alt="partner-logo">
                        </div>
                    </div>
                </div>

                <!-- Item 2 -->
                <div class="duration-700 ease-in-out transition-all transform -translate-x-full z-10 py-8 px-20"
                     data-carousel-item="">
                    <div class="grid grid-cols-2 gap-8 md:grid-cols-3">
                        <div class="col-span-1 flex items-center justify-center md:col-span-2 lg:col-span-1">
                            <img class="h-24" src="/img/partenaires/logo-uk.png" alt="partner-logo">
                        </div>

                        <div class="col-span-1 flex items-center justify-center md:col-span-3 lg:col-span-1">
                            <img class="h-24" src="/img/partenaires/nice.png" alt="partner-logo">
                        </div>

                        <div class="col-span-2 flex items-center justify-center md:col-span-3 lg:col-span-1">
                            <img class="h-24" src="/img/partenaires/compiègne.png" alt="partner-logo">
                        </div>
                    </div>
                </div>

            </div>
            <!-- Slider controls -->
            <button type="button"
                    class="absolute top-0 left-0 z-30 flex items-center justify-center h-full px-4 cursor-pointer group focus:outline-none"
                    data-carousel-prev="">
                <span
                    class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-gray-100 group-hover:bg-white/50 group-focus:ring-4 group-focus:ring-white group-focus:outline-none">
                    <svg aria-hidden="true" class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor"
                         viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round"
                                                                                      stroke-linejoin="round"
                                                                                      stroke-width="2"
                                                                                      d="M15 19l-7-7 7-7"></path></svg>
                    <span class="sr-only">Previous</span>
                </span>
            </button>
            <button type="button"
                    class="absolute top-0 right-0 z-30 flex items-center justify-center h-full px-4 cursor-pointer group focus:outline-none"
                    data-carousel-next="">
                <span
                    class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-gray-100 group-hover:bg-white/50 group-focus:ring-4 group-focus:ring-white group-focus:outline-none">
                    <svg aria-hidden="true" class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor"
                         viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round"
                                                                                      stroke-linejoin="round"
                                                                                      stroke-width="2"
                                                                                      d="M9 5l7 7-7 7"></path></svg>
                    <span class="sr-only">Next</span>
                </span>
            </button>
        </div>
    </section>

</div>
@endsection
