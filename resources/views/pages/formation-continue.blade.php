@extends('layouts.master')


@section('content')
  <div class="relative">
    <div class="bg-no-repeat bg-center bg-cover w-full"
      style="background-image: url(https://images.unsplash.com/photo-1521587760476-6c12a4b040da?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1170&q=80)">
      <div class="h-[50vh] md:h-[60vh] w-full flex items-center justify-center">
        <div
          class="w-full h-full text-white bg-gray-700 opacity-80 flex flex-col py-12 lg:py-0 gap-8 justify-center items-center">
          <div class="flex flex-col items-center px-4 gap-4 text-center">
            <h1 class="text-white lg:text-5xl text-lg font-bold uppercase">FORMATION CONTINUE</h1>
            <span class="w-20 h-2 bg-[#b09d72]"></span>
            <h1 class="text-xl text-center font-bold">Notre objectif avec cette formation est de vous aider à maintenir
              et à améliorer vos compétences en informatique <br> afin que vous puissiez rester à jour dans un domaine
              qui évolue rapidement.</h1>
          </div>

          <a href="#" class="border-4 border-[#b09d72] rounded duration-500 hover:bg-[#b09d72] mt-12 py-4 px-8">
            <p class="font-bold text-xl">Nous Contacter</p>
          </a>
        </div>
      </div>
    </div>
    <div class="container mx-auto mt-16 mb-8 lg:my-16 hidden lex flex-col items-center">
      <div class="flex flex-col items-center justify-center w-full lg:w-2/3">
        <h2 class="text-lg lg:text-3xl font-semibold lg:font-bold text-green-800 text-center p-2  mb-4 uppercase">Lorem
          ipsum dolor sit amet, consectetur adipiscing elit. Etiam ullamcorper auctor erat ut ullamcorper.</h2>
        <span class="text-gray-700 px-6 text-justify text-md md:text-xl">
          <p class="mb-4 leading-snug">
            A côté des diplômes bien connus de tous, il existe des <strong>formations très opérationnelles et généralement
              plus courtes</strong> qui débouchent sur un certificat ou un titre professionnel. <br>
            Mais qu’est ce qu’une <strong>formation certifiante</strong> ? Souvent moins chères que les formations
            diplômantes, les formations certifiantes sont <strong>appréciées des recruteurs et très valorisées sur un
              CV</strong> car leur enseignement est en lien direct avec les besoins des entreprises d’une branche donnée.
            C’est là leur force, mais aussi leur faiblesse car de ce fait elles restent dédiées à un métier précis. La
            formation certifiante n’est donc pas adapté à quelqu’un qui cherche des compétences transversales lui
            permettant d’évoluer dans plusieurs secteurs d’activité.
            L’Académie CISCO Locale de l’IAI-TOGO offre trois curricula. <br>

            Tous ces cours sont accessibles via Internet. Chaque module <strong> CCNA, l’IT Essentials ou le CCNA
              Security</strong> comprend un certain nombre de chapitres et de nombreux travaux pratiques permettant aux
            participants de mettre en oeuvre l’ensemble des notions abordées. Il s’agit des cours à suivre dans le but de
            passer les certifications.
          </p>
        </span>
      </div>
    </div>


    <!--    List formations-->
    <div class="flex flex-col mb-32 mt-16">

      <div class="mb-10 md:mx-auto sm:text-center lg:max-w-2xl md:mb-12">
        <h2
          class="max-w-lg text-gray-900 text-center mb-6 font-sans text-3xl font-bold leading-none tracking-tight sm:text-4xl md:mx-auto">
          FORMATIONS CONTINUES
        </h2>
      </div>

      <div class="container mx-auto px-4 lg:px-0">
        <div class="grid grid-cols-1 sm:grid-cols-1 md:grid-cols-1 lg:grid-cols-3 xl:grid-cols-3 gap-12">
          <div class="flex flex-col items-center justify-between rounded-lg overflow-hidden duration-500 h-full">
            <img class="w-full h-[30vh] object-cover object-center"
              src="https://exploreengineering.ca/sites/default/files/2020-02/NEM_software.jpg" alt="formation-image">
            <div class="flex flex-col gap-5 text-center p-4 flex-grow">
              <h1 class="text-xl lg:text-3xl text-red-600 font-bold uppercase">Génie Logiciel</h1>
              <p class="text-lg lg:text-xl text-justify text-gray-700 leading-snug">
                La filière Génie Logiciel forme des informaticiens capables de concevoir et de maintenir des logiciels en
                s’appuyant sur des méthodes et des outils très évolués. les principaux acquis après une formation en Génie
                Logiciel sont la maîtrise des systèmes d'information, des outils d'analyse et de modélisation, de
                programmation dans les langages de pointe ainsi que l'administration des bases de données. A cet effet,
                l'IAI-TOGO met à la disposition de ses étudiants des centres de calcul équipés de micro-ordinateurs le
                tout dans un réseau local pour le partage des ressources. La formation est théorique et pratique renforcée
                par des stages d'entreprise.
              </p>
            </div>
          </div>

          <div class="flex flex-col items-center justify-between rounded-lg overflow-hidden duration-500 h-full">
            <img class="w-full h-[30vh] object-cover object-center" src="{{ asset('img/formations/asr.jpg') }}"
              alt="formation-image">
            <div class="flex flex-col gap-5 text-center p-4 flex-grow">
              <h1 class="text-xl lg:text-3xl text-red-600 font-bold uppercase">Système et Réseaux</h1>
              <p class="text-lg lg:text-xl text-justify text-gray-700 leading-snug">
                La filière Systèmes et Réseaux, créée il y a deux ans forme des informaticiens capables de concevoir,
                implanter, interconnecter et administrer des réseaux informatiques et d'assurer également la maintenance
                de tout matériel informatique. L'accent est donc mis sur l'étude des réseaux sous la norme CISCO CCNA, les
                systèmes d'exploitation, l'électricité, l'électronique et la maintenance. A cet effet, l'IAI-TOGO met à la
                disposition de ses étudiants des salles de réseaux équipées de micro-ordinateurs le tout dans un réseau
                local pour le partage des ressources. La formation est très pratique. La première promotion de sept (7)
                étudiants ont reçu leur diplôme en 2010.
              </p>
            </div>
          </div>

          <div class="flex flex-col items-center justify-between rounded-lg overflow-hidden duration-500 h-full">
            <img class="w-full h-[30vh] object-cover object-center" src="{{ asset('img/formations/mtwi.jpg') }}"
              alt="formation-image">
            <div class="flex flex-col gap-5 text-center p-4 flex-grow">
              <h1 class="text-xl lg:text-3xl text-red-600 font-bold uppercase">Multimédia</h1>
              <p class="text-lg lg:text-xl text-justify text-gray-700 leading-snug">
                La licence Multimédia, Technologie Web et Infographie (M-TWI), vise à apporter aux étudiants les
                compétences nécessaires pour réussir leur projet, qu’il soit de création, d’administration ou
                d’industrialisation des produits de communication audiovisuelle. L’identité de la formation est liée à la
                nature pluridisciplinaire des enseignements (connaissance technologiques, informatique, gestion de projet,
                création audiovisuelle, infographie) et à l’opportunité d’affirmer une bonne communication via des
                documents multimédias. Il s’agit aussi d’intégrer une culture de management de projets multimédia et
                infographie.
              </p>
            </div>
          </div>
        </div>
      </div>
			
    </div>
  </div>
@endsection
