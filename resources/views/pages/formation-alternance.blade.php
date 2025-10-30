@extends('layouts.master')


@section('content')
  <div class="relative">
    <div class="bg-no-repeat bg-center bg-cover w-full"
      style="background-image: url(https://images.unsplash.com/photo-1521587760476-6c12a4b040da?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1170&q=80)">
      <div class="h-[50vh] md:h-[60vh] w-full flex items-center justify-center">
        <div
          class="w-full h-full text-white bg-gray-700 opacity-80 flex flex-col py-12 lg:py-0 gap-8 justify-center items-center">
          <div class="flex flex-col items-center px-4 gap-4 text-center">
            <h1 class="text-white lg:text-5xl text-lg font-bold uppercase">FORMATION PAR ALTERNANCE</h1>
            <span class="w-20 h-2 bg-[#b09d72]"></span>
            <h1 class="text-xl text-center font-bold">Un type de formation qui offre aux étudiants la possibilité
              d'apprendre les compétences nécessaires pour <br> travailler dans le domaine de l'informatique de manière
              non traditionnelle.</h1>
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
          FORMATIONS ALTERNANCES
        </h2>
        <h1>
          La licence Multimédia, Technologie Web et Infographie (M-TWI), est la seule formation par
          alternance dont dispose l’Institut Africain d’Informatique, Représentation du Togo.
        </h1>
      </div>

      <div class="container mx-auto px-4 lg:px-0">
        <div class="grid grid-cols-1 sm:grid-cols-1 md:grid-cols-1 lg:grid-cols-3 xl:grid-cols-3 gap-12">
          <div class="flex flex-col items-center justify-between rounded-lg overflow-hidden duration-500 h-full">
            <img class="w-full h-[30vh] object-cover object-center"
                 src="https://www.efhr.eu/wp-content/uploads/2017/11/media-wall-1600x700_c.jpg" alt="Licence Multimédia">
            <div class="flex flex-col gap-5 text-center p-4 flex-grow">
              <h1 class="text-xl lg:text-3xl text-red-600 font-bold uppercase">Licence Multimédia</h1>
              <p class="text-lg lg:text-xl text-justify text-gray-700 leading-snug">
                La licence Multimédia est une
                formation pluridisciplinaire qui allie connaissances théoriques et pratiques. Elle permet aux étudiants de
                développer leurs compétences en matière de création de contenu multimédia, de développement de sites web,
                de design graphique, d'animation 3D, de programmation et de bien d'autres domaines encore.
              </p>
            </div>
          </div>

          <div class="flex flex-col items-center justify-between rounded-lg overflow-hidden duration-500 h-full">
            <img class="w-full h-[30vh] object-cover object-center"
                 src="https://images.pexels.com/photos/546819/pexels-photo-546819.jpeg?auto=compress&cs=tinysrgb&w=600"
                 alt="Technologie Web">
            <div class="flex flex-col gap-5 text-center p-4 flex-grow">
              <h1 class="text-xl lg:text-3xl text-red-600 font-bold uppercase">Technologie Web</h1>
              <p class="text-lg lg:text-xl text-justify text-gray-700 leading-snug">
                La technologie Web est un domaine en constante
                évolution qui regroupe l'ensemble des technologies utilisées pour développer et mettre en ligne des sites
                web et des applications web. Il comprend de nombreux langages de programmation tels que HTML, CSS,
                JavaScript et PHP, ainsi que des frameworks tels que React, Angular et Vue.js.
              </p>
            </div>
          </div>

          <div class="flex flex-col items-center justify-between rounded-lg overflow-hidden duration-500 h-full">
            <img class="w-full h-[30vh] object-cover object-center"
                 src="https://infosconcourseducation.com/wp-content/uploads/2022/11/depositphotos_533947378-stock-photo-african-graphic-web-designer-using-1.webp"
                 alt="Infographie (M-TWI)">
            <div class="flex flex-col gap-5 text-center p-4 flex-grow">
              <h1 class="text-xl lg:text-3xl text-red-600 font-bold uppercase">Infographie (M-TWI)</h1>
              <p class="text-lg lg:text-xl text-justify text-gray-700 leading-snug">
                L'infographie (M-TWI) est une formation
                universitaire qui vise à fournir aux étudiants les compétences nécessaires pour travailler dans le domaine
                de l'infographie et de la communication visuelle. Elle couvre un large éventail de domaines, tels que la
                conception graphique, la photographie, la vidéographie, la réalisation de films, la direction artistique
                et bien d'autres encore.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
