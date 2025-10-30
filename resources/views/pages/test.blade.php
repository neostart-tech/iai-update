@extends("layouts.master")


@section('content')
<div>
    <div class="bg-no-repeat bg-center bg-cover w-full" style="background-image: url(https://images.unsplash.com/photo-1521587760476-6c12a4b040da?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1170&q=80)">
        <div class="h-[50vh] md:h-[60vh] w-full flex items-center justify-center">
            <div class="w-full h-full text-white bg-gray-700 bg-opacity-80 flex flex-col py-12 lg:py-0 gap-8 justify-center items-center">
                <div class="flex flex-col items-center px-4 gap-4 text-center">
                    <h1 class="text-white lg:text-5xl text-lg font-bold uppercase">test d'admission</h1>
                    <span class="w-20 h-2 bg-[#b09d72]"></span>
                    <h1 class="lg:text-xl text-center font-bold">Bienvenue sur la page des tests d'admission de notre école d'informatique prestigieuse. <br> Nous sommes ravis que vous ayez choisi de postuler à notre établissement et<br> nous vous remercions  de votre intérêt pour nos programmes de formation.</h1>
                </div>

                <a href="/contact" class="border-4 border-[#b09d72] rounded duration-500 hover:bg-[#b09d72] mt-4 lg:mt-12 py-4 px-4 lg:px-8">
                    <p class="font-semibold lg:font-bold lg:text-xl">Nous Contacter</p>
                </a>
            </div>
        </div>
    </div>

<!--    List formations-->
    <div class="md:mt-24 md:pb-16 px-4 lg:px-6 flex justify-center w-full flex flex-col">
        <section class="text-center md:text-left">
            <div class=" grid md:grid-cols-2 gap-x-6 md:gap-x-2 items-center">

                <div class="relative overflow-hidden bg-no-repeat bg-cover mt-4 rounded-lg relative overflow-hidden bg-no-repeat bg-cover ripple shadow-lg"
                         data-mdb-ripple="true" data-mdb-ripple-color="light">
                        <img src="https://www.radiofrance.fr/s3/cruiser-production/2022/05/2f65fa8b-5c95-4455-9845-4a067b9ae364/1200x680_gettyimages-200446112-013.jpg"
                             class="w-full h-[45vh]" alt="Louvre" />
                        <a href="#!">
                            <div class="absolute top-0 right-0 bottom-0 left-0 w-full h-full overflow-hidden bg-fixed opacity-0 hover:opacity-100 transition duration-300 ease-in-out"
                                 style="background-color: rgba(251, 251, 251, 0.2)"></div>
                        </a>
                </div>

                <a href="#" class="mb-6 mt-32 hover:opacity-80 md:mb-0 h-full w-full">
                    <div class="lg:px-6 h-full px-2">
                        <h3 class="text-2xl lg:text-4xl text-green-800 uppercase font-bold mb-6">information sur le test d'admission.</h3>

                        <p class="text-gray-600 lg:text-xl text-justify ">
                            <small>Afin de vous offrir les meilleures chances de réussite dans le domaine de l'informatique, nous avons mis en place un processus de sélection qui comprend un test d'admission. Ce test vise à évaluer vos connaissances de base  et à vous permettre de montrer vos compétences en logique, mathématiques, et résolution de problème. <br>
                                Il est important de noter que la réussite au test d'admission est obligatoire pour être admis à l'école.Le test est fait en présentiel et est composé de questions à choix multiple ainsi que des questions à réponses courtes. Bonne chance pour votre test d'admission et nous espérons vous accueillir bientôt dans notre école d'informatique !</small>
                        </p>

                    </div>
                </a>
            </div>
        </section>

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
