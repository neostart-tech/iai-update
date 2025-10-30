@extends("layouts.master")


@section('content')
	<div class="relative">
		<div class="bg-no-repeat bg-center bg-cover w-full"
				 style="background-image: url(https://images.unsplash.com/photo-1521587760476-6c12a4b040da?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1170&q=80)">
			<div class="h-[50vh] md:h-[60vh] w-full flex items-center justify-center">
				<div
					class="w-full h-full text-white bg-gray-700 opacity-80 flex flex-col py-12 lg:py-0 gap-8 justify-center items-center">
					<div class="flex flex-col items-center px-4 gap-4 text-center">
						<h1 class="text-white lg:text-5xl text-lg font-bold uppercase">Formation certifiante</h1>
						<span class="w-20 h-2 bg-[#b09d72]"></span>
						<h1 class="text-xl text-center font-bold">Vous souhaitez vous spécialiser dans un domaine précis de
							l'informatique et obtenir une certification reconnue par les professionnels du secteur ? <br> Notre école
							d'informatique est là pour vous aider à atteindre vos objectifs !</h1>
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
                    A côté des diplômes bien connus de tous, il existe des <strong>formations très opérationnelles et généralement plus courtes</strong> qui débouchent sur un certificat ou un titre professionnel. <br>
                        Mais qu’est ce qu’une <strong>formation certifiante</strong> ? Souvent moins chères que les formations diplômantes, les formations certifiantes sont <strong>appréciées des recruteurs et très valorisées sur un CV</strong> car leur enseignement est en lien direct avec les besoins des entreprises d’une branche donnée. C’est là leur force, mais aussi leur faiblesse car de ce fait elles restent dédiées à un métier précis. La formation certifiante n’est donc pas adapté à quelqu’un qui cherche des compétences transversales lui permettant d’évoluer dans plusieurs secteurs d’activité.
                        L’Académie CISCO Locale de l’IAI-TOGO offre trois curricula. <br>

                        Tous ces cours sont accessibles via Internet. Chaque module <strong> CCNA, l’IT Essentials ou le CCNA Security</strong> comprend un certain nombre de chapitres et de nombreux travaux pratiques permettant aux participants de mettre en oeuvre l’ensemble des notions abordées. Il s’agit des cours à suivre dans le but de passer les certifications.
                </p>
            </span>
			</div>
		</div>


		<!--    List formations-->
		<div class="flex flex-col mb-32 mt-16">

			<div class="mb-10 md:mx-auto lg:max-w-2xl md:mb-12">
				<h2
					class="max-w-lg text-gray-900 text-center mb-6 font-sans text-3xl font-bold leading-none tracking-tight sm:text-4xl">
					FORMATIONS CERTIFIANTES
				</h2>
			</div>

			{{-- Liste des formations --}}
			<div class="container mx-auto px-4 lg:px-0">
				<div class="grid grid-cols-1 sm:grid-cols-1 md:grid-cols-1 lg:grid-cols-3 xl:grid-cols-3 gap-12">
					<div class="flex flex-col items-center justify-between rounded-lg overflow-hidden duration-500 h-full">
						<img class="w-full h-[30vh] object-cover object-center"
								 src="https://images.unsplash.com/photo-1518770660439-4636190af475?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1170&q=80"
								 alt="Certification CCNA Cisco Sécurité">
						<div class="flex flex-col gap-5 text-center p-4 flex-grow">
							<h1 class="text-xl lg:text-3xl text-red-600 font-bold uppercase">Certification CCNA Cisco Sécurité</h1>
							<p class="text-lg lg:text-xl text-justify text-gray-700 leading-snug">
								Certification de niveau professionnelle qui valide les compétences en matière de sécurité des réseaux
								informatiques. Elle est
								reconnue internationalement et est très appréciée par les employeurs dans le domaine de l'informatique
								et des réseaux.
							</p>
						</div>
					</div>

					<div class="flex flex-col items-center justify-between rounded-lg overflow-hidden duration-500 h-full">
						<img class="w-full h-[30vh] object-cover object-center"
								 src="https://images.unsplash.com/photo-1610563166150-b34df4f3bcd6?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1076&q=80"
								 alt="Computer Science">
						<div class="flex flex-col gap-5 text-center p-4 flex-grow">
							<h1 class="text-xl lg:text-3xl text-red-600 font-bold uppercase">Computer Science</h1>
							<p class="text-lg lg:text-xl text-justify text-gray-700 leading-snug">
								Obtenir cette certification permet de
								démontrer une solide expertise en informatique et de posséder les compétences nécessaires pour résoudre
								des problèmes complexes en utilisant des algorithmes et des langages de programmation.
							</p>
						</div>
					</div>

					<div class="flex flex-col items-center justify-between rounded-lg overflow-hidden duration-500 h-full">
						<img class="w-full h-[30vh] object-cover object-center"
								 src="https://plus.unsplash.com/premium_photo-1661644858773-b02cfddc793a?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1169&q=80"
								 alt="Certification CCNA Cisco">
						<div class="flex flex-col gap-5 text-center p-4 flex-grow">
							<h1 class="text-xl lg:text-3xl text-red-600 font-bold uppercase">Certification CCNA Cisco</h1>
							<p class="text-lg lg:text-xl text-justify text-gray-700 leading-snug">
								Obtenir cette certification permet de démontrer
								une expertise solide dans la mise en place et la gestion de la sécurité des réseaux informatiques, ainsi
								que dans l'utilisation des équipements et technologies Cisco en matière de sécurité.
							</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection
