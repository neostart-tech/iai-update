<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
	@php($title = 'Déposer mon dossier')
	@include('layouts.admin._head')

	{{--	<link rel="stylesheet" href="{{ asset('tel/build/css/demo.css') }}">--}}
	<link rel="stylesheet" href="{{ asset('tel/build/css/intlTelInput.css') }}">
	@include('candidatures._public_style')
</head>
<!-- [Head] end -->
<!-- [Body] Start -->

<body data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-direction="ltr" data-pc-theme_contrast=""
			data-pc-theme="light">
<!-- [ Pre-loader ] start -->

<!-- [ Pre-loader ] End -->
<form action="{{ route('candidatures.store') }}" method="post" id="candidature-form" enctype="multipart/form-data">
	@csrf
	<div class="auth-main">
		<div class="auth-wrapper v3">
			<div class="auth-form">

				<div class="iai-candidature-topbar">
					<a href="{{ route('home') }}" class="iai-candidature-brand">
						<img src="https://www.iai-togo.tg/wp-content/uploads/2017/06/logo.jpeg" alt="Logo IAI-Togo">
						<span>
							<strong>IAI-TOGO</strong>
							<span>Institut Africain d’Informatique</span>
						</span>
					</a>

					<div class="iai-candidature-progress" aria-live="polite">
						Étape <b id="auth-active-slide">1</b> sur 6
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
								<div class="iai-welcome">
									<span class="iai-kicker">Candidature en ligne</span>
									<h1>Bienvenue dans l’espace de dépôt de candidature</h1>
									<p>
										Préparez vos informations personnelles, vos documents justificatifs et les coordonnées de votre responsable.
										Le formulaire est organisé en étapes pour faciliter la constitution de votre dossier.
									</p>

									<div class="alert alert-warning" role="alert">
										<strong>Important :</strong> Seules les séries <strong>C</strong> et <strong>D</strong> sont acceptées.
									</div>

									<div class="d-grid my-4">
										<button type="button" class="btn btn-outline-warning mt-2" onClick="change_tab('#auth-2')">
											<span>Commencer le dépôt de ma candidature</span>
										</button>
									</div>

									@if(count($errors->all()) > 0)
										<div class="alert alert-danger alert-dismissible fade show" role="alert">
											<strong>Oups!</strong> Des erreurs sont survenues lors de la validation de vos données.
											Naviguez dans le formulaire pour voir les dites erreurs.
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
						Prenez le temps de remplir les champs du formulaire avec soin.
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
			confirmButtonText: 'Oui, je suis d\'accord',
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


<script>
	/**
	 * Validation UX purement frontend pour le formulaire public de candidature.
	 * La validation Laravel côté serveur reste la source de vérité finale.
	 */
	(function () {
		const ERROR_CLASS = 'iai-field-error';
		const FIELD_INVALID_CLASS = 'iai-field-invalid';
		const SUMMARY_CLASS = 'iai-step-error-summary';
		const MINIMUM_TWO_FILES = new Set([
			'bulletins_seconde',
			'bulletins_premiere',
			'bulletins_terminale',
			'releve_bac1',
			'releve_bac2',
		]);

		const normalizeName = (name) => (name || '').replace(/\[\]$/, '');

		const getActivePane = () => {
			return document.querySelector('.tab-pane.active.show') || document.querySelector('.tab-pane.show.active');
		};

		const getSlideIndex = (tabSelector) => {
			const trigger = document.querySelector('a[href="' + tabSelector + '"]');
			return trigger ? Number(trigger.getAttribute('data-slide-index') || 0) : 0;
		};

		const getCurrentSlideIndex = () => {
			const pane = getActivePane();
			return pane && pane.id ? getSlideIndex('#' + pane.id) : 0;
		};

		const getFieldContainer = (field) => {
			return field.closest('.form-group, .form-check, .col-12, .col-md-6, .col-sm-6') || field.parentElement;
		};

		const getLabel = (field) => {
			const id = field.getAttribute('id');
			const pane = field.closest('.tab-pane');

			if (id && pane) {
				const label = pane.querySelector('label[for="' + CSS.escape(id) + '"]');
				if (label) {
					return label;
				}
			}

			const container = getFieldContainer(field);
			return container ? container.querySelector('label') : null;
		};

		const labelText = (field) => {
			const label = getLabel(field);
			if (!label) {
				return field.getAttribute('placeholder') || field.getAttribute('name') || 'Ce champ';
			}

			return label.textContent.replace('*', '').replace(/\s+/g, ' ').trim() || 'Ce champ';
		};

		const labelLooksRequired = (field) => {
			const label = getLabel(field);

			if (!label) {
				return false;
			}

			return Boolean(label.querySelector('.text-danger')) || label.textContent.includes('*');
		};

		const shouldValidateField = (field) => {
			if (!field || field.disabled) {
				return false;
			}

			if (field.type === 'hidden' || field.type === 'button' || field.type === 'submit') {
				return false;
			}

			if (field.closest('[hidden], .d-none')) {
				return false;
			}

			return Boolean(field.required || labelLooksRequired(field));
		};

		const fieldValueIsEmpty = (field) => {
			const tagName = field.tagName.toLowerCase();
			const type = (field.getAttribute('type') || '').toLowerCase();

			if (type === 'checkbox' || type === 'radio') {
				return !field.checked;
			}

			if (type === 'file') {
				return !field.files || field.files.length === 0;
			}

			if (tagName === 'select') {
				return !field.value;
			}

			return !String(field.value || '').trim();
		};

		const messageForField = (field) => {
			const name = normalizeName(field.getAttribute('name'));
			const label = labelText(field);
			const type = (field.getAttribute('type') || '').toLowerCase();

			if (type === 'file' && MINIMUM_TWO_FILES.has(name)) {
				return label + ' : veuillez ajouter au moins 2 fichiers.';
			}

			if (type === 'file') {
				return label + ' : veuillez ajouter un fichier.';
			}

			if (type === 'checkbox') {
				return 'Veuillez cocher cette confirmation obligatoire.';
			}

			return label + ' est obligatoire.';
		};

		const getOrCreateError = (field) => {
			const container = getFieldContainer(field);
			if (!container) {
				return null;
			}

			let error = container.querySelector(':scope > .' + ERROR_CLASS);

			if (!error) {
				error = document.createElement('div');
				error.className = ERROR_CLASS;
				error.setAttribute('role', 'alert');
				error.setAttribute('aria-live', 'polite');
				container.appendChild(error);
			}

			return error;
		};

		const showFieldError = (field, message) => {
			const error = getOrCreateError(field);
			const container = getFieldContainer(field);

			field.classList.add(FIELD_INVALID_CLASS);
			field.setAttribute('aria-invalid', 'true');

			if (error) {
				error.textContent = message;
			}

			if (container) {
				container.classList.add('iai-has-error');
			}
		};

		const clearFieldError = (field) => {
			const container = getFieldContainer(field);

			field.classList.remove(FIELD_INVALID_CLASS);
			field.removeAttribute('aria-invalid');

			if (container) {
				container.classList.remove('iai-has-error');
				const error = container.querySelector(':scope > .' + ERROR_CLASS);
				if (error) {
					error.remove();
				}
			}
		};

		const clearStepSummary = (pane) => {
			const summary = pane.querySelector(':scope > .' + SUMMARY_CLASS);
			if (summary) {
				summary.remove();
			}
		};

		const showStepSummary = (pane, count) => {
			clearStepSummary(pane);

			const summary = document.createElement('div');
			summary.className = SUMMARY_CLASS;
			summary.setAttribute('role', 'alert');
			summary.textContent = count === 1
				? 'Un champ obligatoire doit être complété avant de poursuivre.'
				: count + ' champs obligatoires doivent être complétés avant de poursuivre.';

			const title = pane.querySelector('h1, h2, h3');
			if (title && title.parentElement) {
				title.parentElement.insertAdjacentElement('afterend', summary);
			} else {
				pane.prepend(summary);
			}
		};

		const validatePhoneGroup = (pane) => {
			const phone1 = pane.querySelector('#tel-input');
			const phone2 = pane.querySelector('#tel2');
			const phone3 = pane.querySelector('#tel3');
			const invalidFields = [];

			if (!phone1) {
				return invalidFields;
			}

			if (!String(phone1.value || '').trim()) {
				showFieldError(phone1, 'Téléphone 1 est obligatoire.');
				invalidFields.push(phone1);
			} else {
				clearFieldError(phone1);
			}

			if (phone2 && phone3) {
				const hasSecondPhone = String(phone2.value || '').trim() || String(phone3.value || '').trim();

				if (!hasSecondPhone) {
					showFieldError(phone2, 'Veuillez renseigner Téléphone 2 ou Téléphone 3.');
					invalidFields.push(phone2);
				} else {
					clearFieldError(phone2);
					clearFieldError(phone3);
				}
			}

			return invalidFields;
		};

		const validateFileMinimums = (field) => {
			const name = normalizeName(field.getAttribute('name'));
			const type = (field.getAttribute('type') || '').toLowerCase();

			if (type !== 'file' || !MINIMUM_TWO_FILES.has(name)) {
				return true;
			}

			return field.files && field.files.length >= 2;
		};

		const validateField = (field) => {
			if (!shouldValidateField(field)) {
				clearFieldError(field);
				return true;
			}

			if (fieldValueIsEmpty(field) || !validateFileMinimums(field)) {
				showFieldError(field, messageForField(field));
				return false;
			}

			clearFieldError(field);
			return true;
		};

		const validatePane = (pane) => {
			if (!pane) {
				return true;
			}

			const fields = Array.from(pane.querySelectorAll('input, select, textarea'));
			const invalidFields = [];

			clearStepSummary(pane);

			fields.forEach((field) => {
				if (!validateField(field)) {
					invalidFields.push(field);
				}
			});

			validatePhoneGroup(pane).forEach((field) => {
				if (!invalidFields.includes(field)) {
					invalidFields.push(field);
				}
			});

			if (invalidFields.length > 0) {
				showStepSummary(pane, invalidFields.length);

				const first = invalidFields[0];
				first.scrollIntoView({ behavior: 'smooth', block: 'center' });
				setTimeout(() => {
					if (typeof first.focus === 'function') {
						first.focus({ preventScroll: true });
					}
				}, 250);

				return false;
			}

			return true;
		};

		const bindLiveValidation = () => {
			const form = document.getElementById('candidature-form');
			if (!form) {
				return;
			}

			form.addEventListener('input', (event) => {
				const field = event.target;
				if (field && field.matches && field.matches('input, select, textarea')) {
					validateField(field);
				}
			});

			form.addEventListener('change', (event) => {
				const field = event.target;
				if (field && field.matches && field.matches('input, select, textarea')) {
					validateField(field);

					const pane = field.closest('.tab-pane');
					if (pane && pane.id === 'auth-3') {
						validatePhoneGroup(pane);
					}
				}
			});
		};

		const originalChangeTab = window.change_tab;

		window.change_tab = function (tabName) {
			const currentIndex = getCurrentSlideIndex();
			const targetIndex = getSlideIndex(tabName);
			const isForwardNavigation = targetIndex > currentIndex;

			if (isForwardNavigation) {
				const pane = getActivePane();

				if (pane && pane.id !== 'auth-1' && !validatePane(pane)) {
					return false;
				}
			}

			return originalChangeTab(tabName);
		};

		document.addEventListener('DOMContentLoaded', bindLiveValidation);
	})();
</script>

@include('layouts._scripts')
</body>
<!-- [Body] end -->
</html>

