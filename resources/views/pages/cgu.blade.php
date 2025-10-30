@extends('layouts.master')

@section('content')
	<div>
		<div class="bg-no-repeat bg-center bg-cover w-full"
				 style="background-image: url('{{ asset('img/cgu.jpg') }}')">
			<div class="h-[60vh]  w-full flex items-center justify-center">
				<div
					class="w-full h-full text-white bg-gray-700 bg-opacity-80 flex flex-col py-12 lg:py-0 gap-8 justify-center items-center">
					<div class="flex flex-col items-center gap-4 px-4 text-center">
						<h1 class="text-white lg:text-5xl text-lg font-bold uppercase">Conditions générales d'utilisation</h1>
						<span class="w-20 h-2 bg-[#b09d72]"></span>
						<h1 class="lg:text-xl font-semibold lg:font-bold text-center">Les présentes Conditions Générales
							d'Utilisation (CGU) <br> ont pour objet de définir les modalités d'accès et d'utilisation du site de
							IAI-Togo (ci-après
							"IAI-Togo").</h1>
					</div>
				</div>
			</div>
		</div>
		<div class="bg-white mt-10 px-4 pb-8">
			<div class="container mx-auto">
				<div class="flex flex-col lg:flex-row gap-8 lg:gap-12 text-justify">
					<div class="col-12 flex flex-col lg:border border-red-600 p-2 lg:px-20">
						<x-cgu
							title="Acceptation des Conditions"
							content="L'accès et l'utilisation de IAI-Togo impliquent l'acceptation sans réserve des présentes CGU. En s'inscrivant ou en utilisant
									IAI-Togo, l'utilisateur accepte les termes et conditions ici énoncés."
						/>

						<x-cgu
							title="Définition"
							content="Utilisateur : Toute personne ayant accès à IAI-Togo, y compris les élèves, les enseignants, les administrateurs et les parents.
								Contenu : Toute information, donnée, texte, logiciel, musique, son, photographie, image, vidéo,
								message ou autre matériel publié ou diffusé sur IAI-Togo."
						/>

						<x-cgu
							title="Accès à IAI-Togo"
							content="L'accès à IAI-Togo est réservé aux utilisateurs autorisés. Chaque utilisateur reçoit des
							identifiants personnels qui doivent rester confidentiels. L'utilisateur est responsable de toute activité
							effectuée avec ses identifiants."
						/>

						<x-cgu
							title="Utilisation de IAI-Togo"
							content="
							<ul>
								<li>
									<strong>Utilisation conforme :</strong> Les utilisateurs doivent utiliser IAI-Togo conformément aux lois et règlements en vigueur et ne doivent pas enfreindre les droits des tiers.
								</li>
								<li>
									<strong>Contenu :</strong> Les utilisateurs sont responsables de tout contenu qu'ils publient sur IAI-Togo. Ils s'engagent à ne pas publier de contenu illicite, diffamatoire, obscène, violent, ou autrement répréhensible.
								</li>
								<li>
									<strong>Sécurité :</strong> Les utilisateurs doivent s'abstenir de toute action pouvant compromettre la sécurité ou l'intégrité de IAI-Togo, y compris le piratage ou l'introduction de virus.
								</li>
							</ul>
							"
						/>
						<x-cgu
							title="Responsabilités de l'Administrateur"
							content="
							<ul>
								<li>
									<strong>Maintenance :</strong> L'administrateur s'engage à assurer la maintenance et la disponibilité de IAI-Togo, dans la mesure du possible.
								</li>
								<li>
									<strong>Sécurité des données :</strong> L'administrateur s'engage à protéger les données personnelles des utilisateurs conformément à la réglementation en vigueur (notamment le RGPD).
								</li>
							</ul>
						"
						/>
						<x-cgu
							title="Propriété Intellectuelle"
							content="IAI-Togo et son contenu, y compris les textes, images, et logiciels, sont protégés par des droits
							 de propriété intellectuelle. Toute reproduction, distribution ou utilisation non autorisée est interdite."
						/>
						<x-cgu
							title="Données Personnelles"
							content="Les données personnelles des utilisateurs sont collectées et traitées dans le respect des
								dispositions légales applicables. Les utilisateurs disposent d'un droit d'accès, de rectification et de
								suppression de leurs données personnelles."
						/>
						<x-cgu
							title="Limitation de Responsabilité"
							content="L'administrateur ne saurait être tenu responsable des dommages directs ou indirects résultant de
							l'utilisation ou de l'impossibilité d'utiliser IAI-Togo, y compris en cas de piratage ou de perte de données."
						/>
						<x-cgu
							title="Modifications des CGU"
							content="L'administrateur se réserve le droit de modifier les présentes CGU à tout moment. Les
							utilisateurs seront informés de toute modification et devront accepter les nouvelles CGU pour continuer à
							utiliser IAI-Togo."
						/>
						<x-cgu
							title="Droit Applicable et Juridiction"
							content="Les présentes CGU sont soumises au droit français. En cas de litige, les tribunaux compétents
							seront ceux du ressort du siège de l'administrateur de IAI-Togo."
						/>
						<x-cgu
							title="Contact"
							content="Pour toute question concernant les présentes CGU, les utilisateurs peuvent contacter
								l'administrateur à l'adresse suivante : iaitogo@iai-togo.tg."
						/>

					</div>
				</div>
			</div>
		</div>
		<section class="background-radial-gradient">
			<style>
				.background-radial-gradient {
					background-color: hsl(218, 41%, 15%);
					background-image: radial-gradient(
							650px circle at 0% 0%,
							hsl(218, 41%, 35%) 15%,
							hsl(218, 41%, 30%) 35%,
							hsl(218, 41%, 20%) 75%,
							hsl(218, 41%, 19%) 80%,
							transparent 100%
					),
					radial-gradient(
							1250px circle at 100% 100%,
							hsl(218, 41%, 45%) 15%,
							hsl(218, 41%, 30%) 35%,
							hsl(218, 41%, 20%) 75%,
							hsl(218, 41%, 19%) 80%,
							transparent 100%
					);
				}
			</style>
		</section>
	</div>
@endsection