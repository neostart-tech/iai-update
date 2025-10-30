@extends("layouts.master")

@section("head")

@endsection

@section('content')

	{{--     <div class="w-full  bg-center bg-cover" style="background-image: url(https://images.unsplash.com/photo-1521587760476-6c12a4b040da?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1170&q=80)">--}}
	{{--        <div class=" w-full flex items-center justify-center">--}}
	{{--            <div class="w-full h-full text-white bg-gray-700 bg-opacity-80 flex flex-col py-12 lg:py-0 gap-8 justify-center items-center">--}}

	{{--                <div class="md:grid md:grid-cols-2 px-8 py-8 w-full lg:w-1/2">--}}
	{{--                    <div class="mt-5 md:col-span-2 md:mt-0">--}}
	{{--                    <form action="#" method="POST">--}}
	{{--                        <div class="overflow-hidden shadow sm:rounded-md">--}}
	{{--                        <div class="bg-gray-400 px-4 py-5 sm:p-6">--}}
	{{--                            <div class=" flex flex-col">--}}
	{{--                                <h1 class="text-2xl font-bold mb-8 flex justify-center">--}}
	{{--                                    Formulaire d'inscription étudiants--}}
	{{--                                </h1>--}}
	{{--                                <hr>--}}
	{{--                                <h1 class="text-xl  mt-8">--}}
	{{--                                    Remplissez soigneusement le formulaire d'inscription--}}
	{{--                                </h1>--}}
	{{--                            </div>--}}

	{{--                                <div class="flex flex-wrap -mx-3 mb-6 mt-8">--}}
	{{--                                <div class="w-full md:w-1/3 px-3 mb-6 md:mb-0">--}}
	{{--                                    <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-first-name">--}}
	{{--                                    Prenom--}}
	{{--                                    </label>--}}
	{{--                                    <input class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-500 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white" id="prenom" type="text" placeholder="Prenom">--}}

	{{--                                </div>--}}
	{{--                                <div class="w-full md:w-1/3 px-3">--}}
	{{--                                    <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-last-name">--}}
	{{--                                    Deuxieme prénom--}}
	{{--                                    </label>--}}
	{{--                                    <input class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500" id="deuxieme-prenom" type="text" placeholder="Deuxieme prenom">--}}
	{{--                                </div>--}}
	{{--                                <div class="w-full md:w-1/3 px-3">--}}
	{{--                                    <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-last-name">--}}
	{{--                                    Nom de famille--}}
	{{--                                    </label>--}}
	{{--                                    <input class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500" id="nom-de-famille" type="text" placeholder="Nom de famille">--}}
	{{--                                </div>--}}
	{{--                                </div>--}}

	{{--                                <div class="flex flex-wrap -mx-3 mb-6">--}}
	{{--                                    <div class="w-full md:w-1/2 px-3 mb-6 md:mb-0">--}}
	{{--                                        <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-state">--}}
	{{--                                        SEXE--}}
	{{--                                        </label>--}}
	{{--                                        <div class="relative">--}}
	{{--                                        <select class="block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500" id="grid-state">--}}
	{{--                                            <option>MASCULIN</option>--}}
	{{--                                            <option>FEMININ</option>--}}
	{{--                                        </select>--}}
	{{--                                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">--}}
	{{--                                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>--}}
	{{--                                        </div>--}}
	{{--                                        </div>--}}
	{{--                                    </div>--}}

	{{--                                    <div class="w-full md:w-1/6 px-3 mb-6 md:mb-0">--}}
	{{--                                        <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-state">--}}
	{{--                                        MOIS--}}
	{{--                                        </label>--}}
	{{--                                        <div class="relative">--}}
	{{--                                        <select class="block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500" id="grid-state">--}}
	{{--                                            <option>janvier</option>--}}
	{{--                                            <option>fevrier</option>--}}
	{{--                                            <option>mars</option>--}}
	{{--                                        </select>--}}
	{{--                                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">--}}
	{{--                                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>--}}
	{{--                                        </div>--}}
	{{--                                        </div>--}}
	{{--                                    </div>--}}
	{{--                                    <div class="w-full md:w-1/6 px-3 mb-6 md:mb-0">--}}
	{{--                                        <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-state">--}}
	{{--                                        ANNEE--}}
	{{--                                        </label>--}}
	{{--                                        <div class="relative">--}}
	{{--                                        <select class="block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500" id="grid-state">--}}
	{{--                                            <option>2000</option>--}}
	{{--                                            <option>2001</option>--}}
	{{--                                            <option>2002</option>--}}
	{{--                                        </select>--}}
	{{--                                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">--}}
	{{--                                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>--}}
	{{--                                        </div>--}}
	{{--                                        </div>--}}
	{{--                                    </div>--}}
	{{--                                    <div class="w-full md:w-1/6 px-3 mb-6 md:mb-0">--}}
	{{--                                        <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-state">--}}
	{{--                                        JOUR--}}
	{{--                                        </label>--}}
	{{--                                        <div class="relative">--}}
	{{--                                        <select class="block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500" id="grid-state">--}}
	{{--                                            <option>1</option>--}}
	{{--                                            <option>2</option>--}}
	{{--                                            <option>3</option>--}}
	{{--                                        </select>--}}
	{{--                                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">--}}
	{{--                                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>--}}
	{{--                                        </div>--}}
	{{--                                        </div>--}}
	{{--                                    </div>--}}
	{{--                                </div>--}}

	{{--                                <div class="flex flex-wrap -mx-3 mb-6">--}}
	{{--                                <div class="w-full px-3">--}}
	{{--                                    <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-password">--}}
	{{--                                    Adresse--}}
	{{--                                    </label>--}}
	{{--                                    <input class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-500" id="adresse" type="text" placeholder="Adresse">--}}

	{{--                                </div>--}}
	{{--                                </div>--}}

	{{--                                <div class="flex flex-wrap -mx-3 mb-6">--}}
	{{--                                    <div class="w-full md:w-1/2 px-3 mb-6 md:mb-0">--}}
	{{--                                        <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-first-name">--}}
	{{--                                            Ville--}}
	{{--                                        </label>--}}
	{{--                                        <input class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-500 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white" id="prenom" type="text" placeholder="Ville">--}}

	{{--                                    </div>--}}

	{{--                                    <div class="w-full md:w-1/2 px-3">--}}
	{{--                                        <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-last-name">--}}
	{{--                                            Departement--}}
	{{--                                        </label>--}}
	{{--                                        <input class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500" id="nom-de-famille" type="text" placeholder="Departement">--}}
	{{--                                    </div>--}}
	{{--                                </div>--}}

	{{--                                <div class="flex flex-wrap -mx-3 mb-6">--}}
	{{--                                    <div class="w-full px-3">--}}
	{{--                                        <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-password">--}}
	{{--                                            Code postal--}}
	{{--                                        </label>--}}
	{{--                                        <input class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white focus:border-gray-500" id="adresse" type="text" placeholder="Code postal">--}}

	{{--                                    </div>--}}
	{{--                                </div>--}}

	{{--                                <div class="flex flex-wrap -mx-3 mb-6">--}}
	{{--                                    <div class="w-full md:w-1/2 px-3 mb-6 md:mb-0">--}}
	{{--                                        <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="e-mail">--}}
	{{--                                            Email--}}
	{{--                                        </label>--}}
	{{--                                        <input class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-500 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white" id="e-mail" type="e-mail" placeholder="E-mail">--}}

	{{--                                    </div>--}}

	{{--                                    <div class="w-full md:w-1/2 px-3">--}}
	{{--                                        <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="numero">--}}
	{{--                                            Numéro de téléphone--}}
	{{--                                        </label>--}}
	{{--                                        <input class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500" id="numero" type="numero" placeholder="Numéro de téléphone">--}}
	{{--                                    </div>--}}
	{{--                                </div>--}}

	{{--                        </div>--}}

	{{--                        <div class="bg-gray-50 px-4 py-3 text-right sm:px-6">--}}
	{{--                            <button type="submit" class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Soumettre l'inscription</button>--}}
	{{--                        </div>--}}
	{{--                        </div>--}}
	{{--                    </form>--}}


	{{--                    </div>--}}
	{{--                </div>--}}

	{{--            </div>--}}

	</div>
</div>
<div class="h-screen bg-gray-400">
	<div x-data="app()" x-cloak>
		{{--        </div>--}}
		{{--    </div> --}}

		<div class="bg-gray-400 mb-20">
			<div x-data="app()" x-cloak class="">
				<div class="max-w-3xl mx-auto px-4 py-10 ">
					<div x-show.transition="step === 'complete'">
						<div class="bg-gray-900 rounded-lg p-10 flex items-center shadow justify-between">
							<div>
								<svg class="mb-4 h-20 w-20 text-green-500 mx-auto" viewBox="0 0 20 20" fill="currentColor">
									<path fill-rule="evenodd"
												d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
												clip-rule="evenodd"/>
								</svg>

								<h2 class="text-2xl mb-4 text-gray-800 text-center font-bold">Registration Success</h2>

								<div class="text-gray-600 mb-8">
									Thank you. We have sent you an email to demo@demo.test. Please click the link in the message to
									activate your account.
								</div>

								<button
									@click="step = 1"
									class="w-40 block mx-auto focus:outline-none py-2 px-5 rounded-lg shadow-sm text-center text-gray-600 bg-white hover:bg-gray-100 font-medium border"
								>Back to home
								</button>
							</div>
						</div>
					</div>

					<div x-show.transition="step != 'complete'">
						<!-- Top Navigation -->
						<div class="border-b-2 py-4">
							<div class="uppercase tracking-wide text-xs font-bold text-gray-500 mb-1 leading-tight"
									 x-text="`Page: ${step} sur 4`"></div>
							<div class="flex flex-col md:flex-row md:items-center md:justify-between">
								<div class="flex-1">
									<div x-show="step === 1">
										<div class="text-lg font-bold text-gray-700 leading-tight">Renseignements personnels</div>
									</div>

									<div x-show="step === 2">
										<div class="text-lg font-bold text-gray-700 leading-tight">Coordonnées</div>
									</div>

									<div x-show="step === 3">
										<div class="text-lg font-bold text-gray-700 leading-tight">Renseignement sur les parents</div>
									</div>

									<div x-show="step === 4">
										<div class="text-lg font-bold text-gray-700 leading-tight">détail des études</div>
									</div>
								</div>

								<div class="flex items-center md:w-64">
									<div class="w-full bg-white rounded-full mr-2">
										<div class="rounded-full bg-green-500 text-xs leading-none h-2 text-center text-white"
												 :style="'width: '+ parseInt(step / 3 * 100) +'%'"></div>
									</div>
									<div class="text-xs w-10 text-gray-600" x-text="parseInt(step / 4 * 100) +'%'"></div>
								</div>
							</div>
						</div>
						<!-- /Top Navigation -->

						<!-- Step Content -->
						<div class="py-10">
							<div x-show.transition.in="step === 1">
								<div class="mb-5 text-center">
									<div class="mx-auto w-32 h-32 mb-2 border rounded-full relative bg-gray-100 mb-4 shadow-inset">
										<img id="image" class="object-cover w-full h-32 rounded-full" :src="image"/>
									</div>

									<label
										for="fileInput"
										type="button"
										class="cursor-pointer inine-flex justify-between mb-4 items-center focus:outline-none border py-2 px-4 rounded-lg shadow-sm text-left text-gray-600 bg-white hover:bg-gray-100 font-medium"
									>
										<svg xmlns="http://www.w3.org/2000/svg" class="inline-flex flex-shrink-0 w-6 h-6 -mt-1 mr-1"
												 viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round"
												 stroke-linejoin="round">
											<rect x="0" y="0" width="24" height="24" stroke="none"></rect>
											<path
												d="M5 7h1a2 2 0 0 0 2 -2a1 1 0 0 1 1 -1h6a1 1 0 0 1 1 1a2 2 0 0 0 2 2h1a2 2 0 0 1 2 2v9a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-9a2 2 0 0 1 2 -2"/>
											<circle cx="12" cy="13" r="3"/>
										</svg>
										Parcourir les photos
									</label>

									<div class="mx-auto w-48 text-gray-500 text-md mt-4 text-center mt-1">Cliquez pour ajouter une photo
										de profil
									</div>

									<input name="photo" id="fileInput" accept="image/*" class="hidden" type="file" @change="let file = document.getElementById('fileInput').files[0];
								var reader = new FileReader();
								reader.onload = (e) => image = e.target.result;
								reader.readAsDataURL(file);">
								</div>
								<div class="flex flex-wrap -mx-3 mb-6 mt-8">

									<div class="w-full md:w-1/3 px-3">
										<label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2"
													 for="grid-last-name">
											Nom de famille
										</label>
										<input
											class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
											id="nom-de-famille" type="text" placeholder="Nom de famille">
									</div>
									<div class="w-full md:w-1/3 px-3 mb-6 md:mb-0">
										<label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2"
													 for="grid-first-name">
											Prenoms
										</label>
										<input
											class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-500 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white"
											id="prenom" type="text" placeholder="Prenom">

									</div>
									<div class="w-full md:w-1/3 px-3">
										<label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="adresse">
											Adresse
										</label>
										<input
											class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
											id="adresse" type="text" placeholder="Adresse">
									</div>

								</div>
								<div class="flex flex-wrap -mx-3 mb-6">
									<div class="w-full md:w-1/2 px-3 mb-6 md:mb-0">
										<label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-state">
											SEXE
										</label>
										<div class="relative">
											<select
												class="block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
												id="grid-state">
												<option>MASCULIN</option>
												<option>FEMININ</option>
											</select>
											<div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
												<svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
													<path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/>
												</svg>
											</div>
										</div>
									</div>
									<div class="w-full md:w-1/6 px-3 mb-6 md:mb-0">
										<label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-state">
											MOIS
										</label>
										<div class="relative">
											<select
												class="block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
												id="grid-state">
												<option>janvier</option>
												<option>fevrier</option>
												<option>mars</option>
											</select>
											<div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
												<svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
													<path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/>
												</svg>
											</div>
										</div>
									</div>
									<div class="w-full md:w-1/6 px-3 mb-6 md:mb-0">
										<label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-state">
											ANNEE
										</label>
										<div class="relative">
											<select
												class="block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
												id="grid-state">
												<option>2000</option>
												<option>2001</option>
												<option>2002</option>
											</select>
											<div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
												<svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
													<path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/>
												</svg>
											</div>
										</div>
									</div>
									<div class="w-full md:w-1/6 px-3 mb-6 md:mb-0">
										<label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-state">
											JOUR
										</label>
										<div class="relative">
											<select
												class="block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
												id="grid-state">
												<option>1</option>
												<option>2</option>
												<option>3</option>
											</select>
											<div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
												<svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
													<path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/>
												</svg>
											</div>
										</div>
									</div>
								</div>

								<div class="flex flex-wrap -mx-3 mb-6 mt-8">

									<div class="w-full md:w-1/3 px-3">
										<label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="pays">
											Pays de naissance
										</label>
										<input
											class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
											id="pays" type="text" placeholder="pays">
									</div>
									<div class="w-full md:w-1/3 px-3 mb-6 md:mb-0">
										<label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="langue">
											Langue maternelle
										</label>
										<div class="relative">
											<select
												class="block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
												id="langue">
												<option>Ewe</option>
												<option>Kabyè</option>
												<option>minan</option>
												<option>Kotokoli</option>
												<option>Autre</option>
											</select>
											<div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
												<svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
													<path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/>
												</svg>
											</div>
										</div>

									</div>
									<div class="w-full md:w-1/3 px-3">
										<label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="langue">
											Langue d'usage
										</label>
										<div class="relative">
											<select
												class="block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
												id="langue">
												<option>Français</option>
												<option>Anglais</option>
												<option>Autre</option>
											</select>
											<div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
												<svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
													<path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/>
												</svg>
											</div>
										</div>
									</div>

								</div>

								<div class="flex flex-wrap -mx-3 mb-6">
									<div class="w-full md:w-1/2 px-3 mb-6 md:mb-0">
										<label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-state">
											nationalité
										</label>
										<input
											class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
											id="nom-de-famille" type="text" placeholder="Nationalite">
									</div>
									<div class="w-full md:w-1/2 px-3 mb-6 md:mb-0">
										<label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2"
													 for="lieu-naissance">
											ville de naissance
										</label>
										<input
											class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
											id="lieu-naissance" type="text" placeholder="Lieu de naissance">
									</div>
								</div>


							</div>
							<div x-show.transition.in="step === 2">

								<div class="flex flex-wrap -mx-3 mb-6 mt-8">
									<div class="w-full md:w-1/3 px-3 mb-6 md:mb-0">
										<label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="e-mail">
											E-mail
										</label>
										<input
											class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-500 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white"
											id="e-mail" type="mail" placeholder="monnom@exemple.com">

									</div>
									<div class="w-full md:w-1/3 px-3">
										<label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="numero">
											numéro de téléphone
										</label>
										<input
											class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
											id="numero" type="text" placeholder="numéro de téléphone">
									</div>
									<div class="w-full md:w-1/3 px-3">
										<label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="lieu">
											lieu d'habitation
										</label>
										<input
											class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
											id="lieu" type="text" placeholder="lieu">
									</div>
								</div>
								<div class="flex flex-wrap -mx-3 mb-6">
									<div class="w-full md:w-1/2 px-3 mb-6 md:mb-0">
										<label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-state">
											Statut
										</label>
										<div class="relative">
											<select
												class="block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
												id="grid-state">
												<option>MARIE</option>
												<option>CELIBATAIRE</option>
											</select>
											<div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
												<svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
													<path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/>
												</svg>
											</div>
										</div>
									</div>
									<div class="w-full md:w-1/6 px-3 mb-6 md:mb-0">
										<label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-state">
											MOIS
										</label>
										<div class="relative">
											<select
												class="block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
												id="grid-state">
												<option>janvier</option>
												<option>fevrier</option>
												<option>mars</option>
											</select>
											<div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
												<svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
													<path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/>
												</svg>
											</div>
										</div>
									</div>
									<div class="w-full md:w-1/6 px-3 mb-6 md:mb-0">
										<label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-state">
											ANNEE
										</label>
										<div class="relative">
											<select
												class="block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
												id="grid-state">
												<option>2000</option>
												<option>2001</option>
												<option>2002</option>
											</select>
											<div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
												<svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
													<path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/>
												</svg>
											</div>
										</div>
									</div>
									<div class="w-full md:w-1/6 px-3 mb-6 md:mb-0">
										<label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-state">
											JOUR
										</label>
										<div class="relative">
											<select
												class="block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
												id="grid-state">
												<option>1</option>
												<option>2</option>
												<option>3</option>
											</select>
											<div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
												<svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
													<path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/>
												</svg>
											</div>
										</div>
									</div>
								</div>
								<div class="flex flex-wrap -mx-3 mb-6">
									<div class="w-full md:w-1/2 px-3 mb-6 md:mb-0">
										<label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-state">
											Numéro en cas d'urgence
										</label>
										<input
											class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
											id="nom-de-famille" type="text" placeholder="Nationalite">
									</div>
									<div class="w-full md:w-1/2 px-3 mb-6 md:mb-0">
										<label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-state">
											Lieu de naissance
										</label>
										<input
											class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
											id="nom-de-famille" type="text" placeholder="Lieu de naissance">
									</div>
								</div>
							</div>

							<div x-show.transition.in="step === 3">
								<div class="mb-6">

									<div class="flex flex-wrap -mx-3 mb-6 mt-4">

										<div class="w-full md:w-1/3 px-3">
											<label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2"
														 for="grid-last-name">
												Nom de famille
											</label>
											<input
												class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
												id="nom-de-famille" type="text" placeholder="Nom de famille">
										</div>
										<div class="w-full md:w-1/3 px-3 mb-6 md:mb-0">
											<label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2"
														 for="grid-first-name">
												Prenoms
											</label>
											<input
												class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-500 rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white"
												id="prenom" type="text" placeholder="Prenom">

										</div>
										<div class="w-full md:w-1/3 px-3">
											<label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="adresse">
												Adresse
											</label>
											<input
												class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
												id="adresse" type="text" placeholder="Adresse">
										</div>

									</div>

									<div class="flex flex-wrap -mx-3 mb-6">
										<div class="w-full md:w-1/2 px-3 mb-6 md:mb-0">
											<label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="e-mail">
												E-mail
											</label>
											<input
												class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
												id="e-mail" type="mail" placeholder="e-mail">
										</div>
										<div class="w-full md:w-1/2 px-3 mb-6 md:mb-0">
											<label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2"
														 for="numero-telephone">
												Numéro de téléphone
											</label>
											<input
												class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
												id="numero-telephone" type="text" placeholder="numéro de téléphone">
										</div>
									</div>

									<label for="email" class="font-bold mb-1 text-gray-700 block">Parent</label>
									<div class="flex">
										<label
											class="flex justify-start items-center text-truncate rounded-lg bg-white pl-4 pr-6 py-3 shadow-sm mr-4">
											<div class="text-teal-600 mr-3">
												<input type="radio" x-model="gender" value="Masculin"
															 class="form-radio focus:outline-none focus:shadow-outline"/>
											</div>
											<div class="select-none text-gray-700">Père</div>
										</label>

										<label
											class="flex justify-start items-center text-truncate rounded-lg bg-white pl-4 pr-6 py-3 shadow-sm">
											<div class="text-teal-600 mr-3">
												<input type="radio" x-model="gender" value="Feminin"
															 class="form-radio focus:outline-none focus:shadow-outline"/>
											</div>
											<div class="select-none text-gray-700">Mère</div>
										</label>
									</div>
								</div>

								<div class="mb-5">
									<label for="profession" class="font-bold mb-1 text-gray-700 block">Profession</label>
									<input type="profession"
												 class="w-full px-4 py-3 rounded-lg shadow-sm focus:outline-none focus:shadow-outline text-gray-600 font-medium"
												 placeholder="profession">
								</div>
							</div>

							<div x-show.transition.in="step === 4">
								<div class="mb-5">
									<label for="email" class="font-bold mb-1 text-gray-700 block">Gender</label>

									<div class="flex">
										<label
											class="flex justify-start items-center text-truncate rounded-lg bg-white pl-4 pr-6 py-3 shadow-sm mr-4">
											<div class="text-teal-600 mr-3">
												<input type="radio" x-model="gender" value="Male"
															 class="form-radio focus:outline-none focus:shadow-outline"/>
											</div>
											<div class="select-none text-gray-700">Male</div>
										</label>

										<label
											class="flex justify-start items-center text-truncate rounded-lg bg-white pl-4 pr-6 py-3 shadow-sm">
											<div class="text-teal-600 mr-3">
												<input type="radio" x-model="gender" value="Female"
															 class="form-radio focus:outline-none focus:shadow-outline"/>
											</div>
											<div class="select-none text-gray-700">Female</div>
										</label>
									</div>
								</div>

								<div class="mb-5">
									<label for="profession" class="font-bold mb-1 text-gray-700 block">Profession</label>
									<input type="profession"
												 class="w-full px-4 py-3 rounded-lg shadow-sm focus:outline-none focus:shadow-outline text-gray-600 font-medium"
												 placeholder="eg. Web Developer">
								</div>
							</div>


							<div class=" bottom-0 left-0 right-0 py-5 bg-gray-200" x-show="step != 'complete'">
								<div class="max-w-3xl mx-auto px-4">
									<div class="flex justify-between">
										<div class="w-1/2">
											<button
												x-show="step > 1"
												@click="step--"
												class="w-32 focus:outline-none py-2 px-5 rounded-lg shadow-sm text-center text-gray-600 bg-white hover:bg-gray-100 font-medium border"
											>Précedent
											</button>
										</div>

										<div class="w-1/2 text-right">
											<button
												x-show="step < 4"
												@click="step++"
												class="w-32 focus:outline-none border border-transparent py-2 px-5 rounded-lg shadow-sm text-center text-white bg-blue-500 hover:bg-blue-600 font-medium"
											>Suivant
											</button>

											<button
												@click="step = 'complete'"
												x-show="step === 4"
												class="w-32 focus:outline-none border border-transparent py-2 px-5 rounded-lg shadow-sm text-center text-white bg-blue-500 hover:bg-blue-600 font-medium"
											>Envoyer
											</button>
										</div>
									</div>
								</div>
							</div>


						</div>
						<!-- / Step Content -->
					</div>
				</div>

				<!-- Bottom Navigation -->

				<!-- / Bottom Navigation https://placehold.co/300x300/e2e8f0/cccccc -->
			</div>
		</div>

		@endsection

		@push('stepForm')
			<script>
				function app() {
					return {
						step: 1,
						passwordStrengthText: '',
						togglePassword: false,

						image: 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAAAAAAAD/4QBCRXhpZgAATU0AKgAAAAgAAYdpAAQAAAABAAAAGgAAAAAAAkAAAAMAAAABAAAAAEABAAEAAAABAAAAAAAAAAAAAP/bAEMACwkJBwkJBwkJCQkLCQkJCQkJCwkLCwwLCwsMDRAMEQ4NDgwSGRIlGh0lHRkfHCkpFiU3NTYaKjI+LSkwGTshE//bAEMBBwgICwkLFQsLFSwdGR0sLCwsLCwsLCwsLCwsLCwsLCwsLCwsLCwsLCwsLCwsLCwsLCwsLCwsLCwsLCwsLCwsLP/AABEIAdoB2gMBIgACEQEDEQH/xAAfAAABBQEBAQEBAQAAAAAAAAAAAQIDBAUGBwgJCgv/xAC1EAACAQMDAgQDBQUEBAAAAX0BAgMABBEFEiExQQYTUWEHInEUMoGRoQgjQrHBFVLR8CQzYnKCCQoWFxgZGiUmJygpKjQ1Njc4OTpDREVGR0hJSlNUVVZXWFlaY2RlZmdoaWpzdHV2d3h5eoOEhYaHiImKkpOUlZaXmJmaoqOkpaanqKmqsrO0tba3uLm6wsPExcbHyMnK0tPU1dbX2Nna4eLj5OXm5+jp6vHy8/T19vf4+fr/xAAfAQADAQEBAQEBAQEBAAAAAAAAAQIDBAUGBwgJCgv/xAC1EQACAQIEBAMEBwUEBAABAncAAQIDEQQFITEGEkFRB2FxEyIygQgUQpGhscEJIzNS8BVictEKFiQ04SXxFxgZGiYnKCkqNTY3ODk6Q0RFRkdISUpTVFVWV1hZWmNkZWZnaGlqc3R1dnd4eXqCg4SFhoeIiYqSk5SVlpeYmZqio6Slpqeoqaqys7S1tre4ubrCw8TFxsfIycrS09TV1tfY2dri4+Tl5ufo6ery8/T19vf4+fr/2gAMAwEAAhEDEQA/APTmZsnmk3N60N1NJTELub1o3N60lFAC7m9aNzetJRQAu5vWjc3rSUUALub1o3N60lFAC7m9aNzetJRQAu5vWjc3rSUUALub1o3N60lFAC7m9aNzetJRQAu5vWjc3rSUUALub1o3N60lFAC7m9aNzetJRQAu5vWjc3rSUUALub1o3N60lFAC7m9aNzetJRQAu5vWjc3rSUUALub1o3N60lFAC7m9aNzetJRQAu5vWjc3rSUUALub1o3N60lFAC7m9aNzetJRQAu5vWjc3rSUUALub1o3N60lJQA7c3rSbm9aSigBdzetG4+tJRQAZPrRuPrSUUALub1/lRub1pKSgBdzUbm9aSigBdzetG5vX+VJSUALub1/lUu5qhqXj1oAG6mkpW6mkoAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooASiiigAooooAKSiigAooo+lACUZoooAKKKSgAo/rRSUALUlRVJz60AObqaSlbqaSgAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACkoooAKKKKACiikoAKSlooASiiigA+lHpRQaACkoooATmilpPegBP/ANdS5HrUdSfL7UAObqaSlbqaSgAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKSiigAooooAKKKKAEooooASij60UAFFFHpQAUmaKPxoAKSlpPWgA/wAmk/pS/Sk47dqADpUvPvUXrUn4H8qAHt1NJSt1NJQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFISFBJIAHUk4FAC0VTlv4EyEBc+3C/nVSS9uX6MEHonX8zQBrEqvLEAe5A/nUTXVqvWVfwyf5VjFmY5Ykn3JP86SmBrG/tB3c/RTTf7QtvST8hWXRQBqi/te+8f8AAc09by0b/loB/vAiseigDeV43+66t9CDTq5/p04+lTJdXMfSQkej/MP1oA2qKoR6gpwJUK/7Scj8utXEkjkG5GDD2P8AMUgH0UUUAFFFJQAUUUUAFFFJQAtJRRQAUlFFABR2oo+lAB1pKKP60AFFFFACUHjNH/66KAEpaSj/APVQAc0/I9KZUufpQA5uppKVuppKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACimsyopZiAo5JNZlxePLlI8rH0J/ib60AWp72KLKph3/wDHR9TWdLNNMcuxPoOij6Co6KYBRRRQAUUUUAFFFFABRRRQAUUUUAFKruhDIxUjuDikooA0IL/os4/4Gv8AUVfBVgCpBB6Ecg1gVLBcSwH5eUP3lPQ/SgDaoqOKaOZdyH/eB6qfepKQBRRRQAlFFFABSUUUAFFFFABRRSf5NABxR6e1FJQAcUUUnP6UALSf5/GjvRz+FAB06d6KT6UGgA96kyf8mo//ANdP59P1oAlbqaSlbqaSgAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACmu6RqzucKvJNKSACScADJJ7Csi6uDO2BkRqflHr7mgBLi5edu4QH5V/qagoopgFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFACUUUUAPjkkiYOhwR+RHoa14J0nTI4YffX0NYtPileJ1dDyOoPQj0NAG7SUyKVJkDr36juD6U+kAUhoooAKKKKACij/JpKACj/PNFFABScUelFACUdqP8mj+dABn9KMjij60d+tACf5FH5Uf59qOOlACfhUn40zmn4oAlbqaSlbqaSgAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKhuJhDEz/xfdQerGgCpfXGT5CHgf6w+/8AdqhQSSSScknJPqTRTAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACkoooAKKKKACiiigCe2nMEnP+rbhx6e9bHoQevT3zXP1p2M+9DE33k5X/AHf/AK1AF2koNFIAoopKAFpKKPSgApPX0pf8mkoAKKTPP1paAE+lFFIT/ntQAelHAoz0oz/hQAd6T155oooAKk2+wqLPt/8AWqTj1P5GgCZuppKVuppKACiiigAooooAKKKKACiiigAooooAKKKKACiiigArJvpd8uwH5Y+P+BHrWnK4jjkc/wAKkj69qwiSSSepJJ+ppgFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABSUUUAFFFFABRRSUAFFFFABT4pDFIkg/hPPuO4plFAG8CGAYchgCD7HmlqpYy74dp6xnH4HkVapALSUUUAH+NFFJQAc0f5+tHFJQAUUUepoAP/r0nP/1sUH1ozQAUnOaPwo9OlAAcd6T60tJQAHn+lSZPotR/55qTJ/yKAJm6mkpW6mkoAKKKKACiiigAooooAKKKKACiiigAooooAKKKKAKWoPiNE/vtk/RazKt6g2Zgv9xB+Z5qpTAKKKKACiiigAooooAKKKKA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',
						password: '',
						gender: 'Male',

						checkPasswordStrength() {
							var strongRegex = new RegExp("^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^&\*])(?=.{8,})");
							var mediumRegex = new RegExp("^(((?=.*[a-z])(?=.*[A-Z]))|((?=.*[a-z])(?=.*[0-9]))|((?=.*[A-Z])(?=.*[0-9])))(?=.{6,})");

							let value = this.password;

							if (strongRegex.test(value)) {
								this.passwordStrengthText = "Strong password";
							} else if (mediumRegex.test(value)) {
								this.passwordStrengthText = "Could be stronger";
							} else {
								this.passwordStrengthText = "Too weak";
							}
						}
					}
				}
			</script>
@endpush
