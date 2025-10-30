<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
	@php($title = 'Déposer mon dossier')
	@include('layouts.admin._head')

	{{--	<link rel="stylesheet" href="{{ asset('tel/build/css/demo.css') }}">--}}
	<link rel="stylesheet" href="{{ asset('tel/build/css/intlTelInput.css') }}">
</head>
<!-- [Head] end -->
<!-- [Body] Start -->

<body data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-direction="ltr" data-pc-theme_contrast=""
			data-pc-theme="light">
<!-- [ Pre-loader ] start -->

<!-- [ Pre-loader ] End -->
<form action="{{ route('candidatures.store') }}" method="post" id="candidature-form" enctype="multipart/form-data">
	<div class="auth-main">
		<div class="auth-wrapper v3">
			<div class="auth-form">

				<div class="auth-header row">
					<div class="col my-1 pointer">
						<a href="{{ route('home') }}">
							<img src="https://www.iai-togo.tg/wp-content/uploads/2017/06/logo.jpeg"
									 style="max-height: 40px; max-width: 85px" class="img-fluid logo-lg" alt="logo">
						</a>
					</div>
					<div class="col-auto my-1">
						<h5 class="m-0 text-muted f-w-500">Étape <b class="h5" id="auth-active-slide">1</b> sur 6</h5>
					</div>
				</div>
				<div class="card my-5">
					<div class="card-body">
						<ul class="nav nav-tabs d-none" id="myTab" role="tablist">
							<li class="nav-item">
								<a
									class="nav-link active"
									id="auth-tab-1"
									data-bs-toggle="tab"
									href="#auth-1"
									role="tab"
									data-slide-index="1"
									aria-controls="auth-1"
									aria-selected="true"
								>
								</a>
							</li>
							<li class="nav-item">
								<a
									class="nav-link"
									id="auth-tab-2"
									data-bs-toggle="tab"
									href="#auth-2"
									role="tab"
									data-slide-index="2"
									aria-controls="auth-2"
									aria-selected="true"
								>
								</a>
							</li>
							<li class="nav-item">
								<a
									class="nav-link"
									id="auth-tab-3"
									data-bs-toggle="tab"
									href="#auth-3"
									role="tab"
									data-slide-index="3"
									aria-controls="auth-3"
									aria-selected="true"
								>
								</a>
							</li>
							<li class="nav-item">
								<a
									class="nav-link"
									id="auth-tab-4"
									data-bs-toggle="tab"
									href="#auth-4"
									role="tab"
									data-slide-index="4"
									aria-controls="auth-4"
									aria-selected="true"
								>
								</a>
							</li>
							<li class="nav-item">
								<a
									class="nav-link"
									id="auth-tab-5"
									data-bs-toggle="tab"
									href="#auth-5"
									role="tab"
									data-slide-index="5"
									aria-controls="auth-5"
									aria-selected="true"
								>
								</a>
							</li>
							<li class="nav-item">
								<a
									class="nav-link"
									id="auth-tab-6"
									data-bs-toggle="tab"
									href="#auth-6"
									role="tab"
									data-slide-index="6"
									aria-controls="auth-6"
									aria-selected="true"
								>
								</a>
							</li>
						</ul>
						<div class="tab-content">
							<div class="tab-pane show active" id="auth-1" role="tabpanel" aria-labelledby="auth-tab-1">
								<div class="text-center">
									<h3 class="text-center mb-3">Bienvenue dans la section de dépôt de candidature</h3>
									{{--                                <p class="mb-4">Sign up or login with your work email.</p>--}}
									<div class="alert alert-warning" role="alert" style="background-color:#fff8db;border-color:#ffe58f;color:#613400">
										<strong>Important :</strong> Seules les séries <strong>C</strong> et <strong>D</strong> sont acceptées.
									</div>
									<div class="d-grid my-3">
										<button type="button" class="btn btn-outline-warning mt-2 text-muted"
														style="background-color: #fdf296"
														onClick="change_tab('#auth-2')">
											<span> Cliquez sur ce bouton pour commencer le dépôt de votre candidature</span>
										</button>
									</div>
									@if(count($errors->all()) > 0)
										<div class="alert alert-danger alert-dismissible fade show" role="alert">
											<strong>Oups!</strong> Des erreurs sont survenues lors de la validation de vos données.
											Naviguez dans le formulaire pour voir les dites erreurs
										</div>
									@endif
								</div>
							</div>
							@csrf
							@include('candidatures._identite')
							@include('candidatures._docs')
							@include('candidatures._personne_frais')
							@include('candidatures._tuteur')
						</div>
					</div>
				</div>
				<div class="auth-footer">
					<p class="m-0 w-100 text-center">
						Prenez le temp de remplir les champs du formulaire avec soin!
					</p>
				</div>
			</div>
			@include('candidatures._side')
		</div>
	</div>
</form>

{{--@section('other-css')--}}
{{--	<link rel="stylesheet" href="{{ asset('tel/build/css/demo.css') }}">--}}
{{--	<link rel="stylesheet" href="{{ asset('tel/build/css/intlTelInput.css') }}">--}}
{{--@endsection--}}

<script src="{{ asset('admin/assets/js/plugins/sweetalert2.all.min.js') }}"></script>
<script src="{{ asset('admin/assets/js/plugins/choices.min.js') }}"></script>

<script src="{{ asset('tel/build/js/intlTelInput.js') }}"></script>
<script>
	let indicatif = document.getElementById('indicatif');

	let input = document.getElementById('tel-input');

	let flag = document.getElementsByClassName('iti__flag-container');


	const updateIndicatif = () => {
		let elements = document.getElementsByClassName('iti__country iti__standard iti__active');

		if (elements.length > 0) {
			indicatif.value = elements[0].getAttribute('data-dial-code');
		} else {
			indicatif.value = '228';
		}
	}

	window.intlTelInput(input, {
		utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
		initialCountry: 'auto',
		geoIpLookup: callback => {
			fetch("https://ipapi.co/json")
				.then(res => res.json())
				.then(data => callback(data.country_code))
				.catch(() => callback("us"));
		},
	})

	input.addEventListener('input', updateIndicatif);

	input.addEventListener('paste', () => {
		setTimeout(updateIndicatif);
	});
	input.addEventListener('cut', () => {
		setTimeout(updateIndicatif);
	});

</script>

<script>
	const cguUrl = '{{ route('cgu') }}';
	document.querySelector('.auth-conf').addEventListener('click', function () {

		Swal.fire({
			title: '<strong>À votre attention</strong>',
			icon: 'info',
			html: 'En vous inscrivant, vous confirmez avoir lu ' + '<a href="' + cguUrl + '" target="_blank"> les conditions générales d\'utilisation</a>' + ' de IAI-Togo et accepter les' + ' .',
			// html: 'You can use <b>bold text</b>, ' + '<a href="//sweetalert2.github.io">links</a> ' + 'and other HTML tags',
			showCloseButton: true,
			showCancelButton: true,
			focusConfirm: false,
			confirmButtonText: 'Oui, je susi d\'accord',
			cancelButtonText: 'Non, ne pas valider',
		}).then((result) => {
			if (result.isConfirmed) {
				document.getElementById('candidature-form').submit();
				Swal.fire('Dépôt de la candidature en cours!', '', 'info');
			}
		});
	});

	function change_tab(tab_name) {
		let someTabTriggerEl = document.querySelector('a[href="' + tab_name + '"]');
		document.querySelector('#auth-active-slide').innerHTML = someTabTriggerEl.getAttribute('data-slide-index');
		let actTab = new bootstrap.Tab(someTabTriggerEl);
		actTab.show();
	}
</script>
<script>
	function replicate() {
		document.getElementById('nom_tuteur').value = document.getElementById('nom_resp').value;
		document.getElementById('prenom_tuteur').value = document.getElementById('prenom_resp').value;
		document.getElementById('profession_tuteur').value = document.getElementById('profession_resp').value;
		document.getElementById('employeur_tuteur').value = document.getElementById('employeur_resp').value;
		document.getElementById('email_tuteur').value = document.getElementById('email_resp').value;
		document.getElementById('tel_tuteur').value = document.getElementById('tel_resp').value;
		document.getElementById('adresse_tuteur').value = document.getElementById('adresse_resp').value;
		document.getElementById('bp_tuteur').value = document.getElementById('bp_resp').value;
		document.getElementById('fax_tuteur').value = document.getElementById('fax_resp').value;
	}
</script>

<script>
	document.addEventListener('DOMContentLoaded', function () {
		let genericExamples = document.querySelectorAll('[data-trigger]');
		for (let i = 0; i < genericExamples.length; ++i) {
			let element = genericExamples[i];
			new Choices(element, {
				placeholderValue: 'This is a placeholder set in the config',
				searchPlaceholderValue: 'Saisissez le nom de votre pays d\'origine'
			});
		}
	});
</script>

@include('layouts._scripts')
</body>
<!-- [Body] end -->
</html>

