<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Déposer mon dossier — {{ config('app.name') }}</title>
	@php($logoPath = \App\Helpers\ConfigHelper::getAppLogo())
	<link rel="icon" href="{{ $logoPath && Storage::disk('public')->exists($logoPath) ? Storage::url($logoPath) : 'https://www.iai-togo.tg/wp-content/uploads/2017/06/logo.jpeg' }}" type="image/x-icon">
	@include('candidatures._styles')
	<link rel="stylesheet" href="{{ asset('tel/build/css/intlTelInput.css') }}">
	<!-- Choices.js CSS -->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
	<!-- Flag Icons CSS -->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.0.0/css/flag-icons.min.css" />
</head>
<body class="depot-body">

<div class="split-layout">
	<!-- Colonne de gauche : Image et texte d'introduction -->
	<aside class="split-left">
		<div class="left-overlay"></div>
		<div class="left-content">
			<header class="depot-masthead">
				<a href="{{ route('home') }}" class="logo-link">
					<img src="https://www.iai-togo.tg/wp-content/uploads/2017/06/logo.jpeg" class="depot-logo" alt="logo IAI-Togo">
				</a>
				<div class="depot-eyebrow">Institut Africain d'Informatique — Togo</div>
			</header>

			<div class="hero-text-container">
				<p class="depot-hero-kicker">Dossier de candidature</p>
				<h1 class="depot-hero-title">Votre avenir en informatique <br><span>commence ici</span></h1>
				<p class="depot-hero-lede">
					Quatre étapes, une quinzaine de minutes. Munissez-vous de vos bulletins, de votre relevé de BAC et d'une pièce d'identité avant de commencer.
				</p>
			</div>

			<footer class="left-footer">
				Vos informations sont transmises de façon sécurisée et ne servent qu'au traitement de votre dossier de candidature.
			</footer>
		</div>
	</aside>

	<!-- Colonne de droite : Formulaire -->
	<main class="split-right">
		<div class="right-content">
			
			<div class="depot-hero-note">
				<span aria-hidden="true" class="note-icon">
					<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 256 256"><path d="M128,24A104,104,0,1,0,232,128,104.11,104.11,0,0,0,128,24Zm0,192a88,88,0,1,1,88-88A88.1,88.1,0,0,1,128,216Zm-8-80V80a8,8,0,0,1,16,0v56a8,8,0,0,1-16,0Zm20,36a12,12,0,1,1-12-12A12,12,0,0,1,140,172Z"></path></svg>
				</span>
				<span><strong>Important :</strong> seules les séries <strong>C</strong>, <strong>D</strong>, <strong>E</strong> et <strong>F2</strong> sont acceptées pour cette procédure.</span>
			</div>

			@if(count($errors->all()) > 0)
				<div class="depot-alert depot-alert--error">
					<strong>Des informations sont manquantes ou incorrectes.</strong> Parcourez les étapes ci-dessous pour corriger les champs signalés en rouge.
				</div>
			@endif

			<form action="{{ route('candidatures.store') }}" method="post" id="candidature-form" enctype="multipart/form-data" class="depot-form">
				@csrf

				<div class="stepper" id="depot-stepper">
					<div class="stepper-track"><div class="stepper-progress" id="stepper-progress"></div></div>
					<button type="button" class="stepper-item" data-step="0" data-target="#auth-2">
						<span class="stepper-dot">1</span><span class="stepper-label">Identité</span>
					</button>
					<button type="button" class="stepper-item" data-step="1" data-target="#auth-3">
						<span class="stepper-dot">2</span><span class="stepper-label">Contact</span>
					</button>
					<button type="button" class="stepper-item" data-step="2" data-target="#auth-4">
						<span class="stepper-dot">3</span><span class="stepper-label">Documents</span>
					</button>
					<button type="button" class="stepper-item" data-step="3" data-target="#auth-5">
						<span class="stepper-dot">4</span><span class="stepper-label">Tuteur(s)</span>
					</button>
				</div>

				<div class="depot-panels">
					@include('candidatures._identite')
					@include('candidatures._docs')
					@include('candidatures._tuteur')
				</div>
			</form>
		</div>
	</main>
</div>

<script src="{{ asset('admin/assets/js/plugins/sweetalert2.all.min.js') }}"></script>
<script src="{{ asset('admin/assets/js/plugins/choices.min.js') }}"></script>
<script src="{{ asset('tel/build/js/intlTelInput.js') }}"></script>

<script>
	// Initialise un widget d'indicatif téléphonique sur un champ donné. Utilise l'instance
	// intlTelInput elle-même (iti.getSelectedCountryData()) plutôt qu'une recherche globale
	// par classe CSS, qui casserait dès qu'il y a plusieurs widgets sur la même page.
	function initPhoneInput(inputEl, indicatifEl) {
		const iti = window.intlTelInput(inputEl, {
			utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
			initialCountry: 'auto',
			geoIpLookup: callback => {
				fetch("https://ipapi.co/json")
					.then(res => res.json())
					.then(data => callback(data.country_code))
					.catch(() => callback("tg"));
			},
		});

		const updateIndicatif = () => {
			const country = iti.getSelectedCountryData();
			indicatifEl.value = country && country.dialCode ? country.dialCode : '228';
		};
		inputEl.addEventListener('input', updateIndicatif);
		inputEl.addEventListener('paste', () => setTimeout(updateIndicatif));
		inputEl.addEventListener('cut', () => setTimeout(updateIndicatif));
		inputEl.addEventListener('countrychange', updateIndicatif);
		updateIndicatif();
	}

	initPhoneInput(document.getElementById('tel-input'), document.getElementById('indicatif'));
	initPhoneInput(document.getElementById('tel2'), document.getElementById('indicatif2'));
	initPhoneInput(document.getElementById('tel3'), document.getElementById('indicatif3'));
</script>

<script>
	document.querySelector('.auth-conf').addEventListener('click', function () {
		Swal.fire({
			title: '<strong>À votre attention</strong>',
			icon: 'info',
			html: 'Confirmez-vous le dépôt de votre dossier de candidature à IAI-Togo ?',
			showCloseButton: true,
			showCancelButton: true,
			focusConfirm: false,
			confirmButtonColor: '#0f2436',
			confirmButtonText: 'Oui, je suis d\'accord',
			cancelButtonText: 'Non, ne pas valider',
		}).then((result) => {
			if (result.isConfirmed) {
				document.getElementById('candidature-form').submit();
				Swal.fire('Dépôt de la candidature en cours…', '', 'info');
			}
		});
	});

	const submitButton = document.querySelector('.auth-conf');
	const cguCheckbox = document.getElementById('accept_cgu');
	const candidatureForm = document.getElementById('candidature-form');

	// Vérifie que tous les champs marqués "required" (identité, contact, documents
	// obligatoires, tuteurs) sont effectivement remplis, où qu'ils se trouvent dans les
	// 4 étapes — pas seulement dans celle actuellement affichée.
	function allRequiredFieldsFilled() {
		const requiredFields = candidatureForm.querySelectorAll('[required]');
		for (const field of requiredFields) {
			if (field.type === 'checkbox') {
				if (!field.checked) return false;
			} else if (field.type === 'file') {
				if (!field.files || field.files.length === 0) return false;
			} else if (!field.value || !field.value.trim()) {
				return false;
			}
		}
		return true;
	}

	function updateSubmitButtonState() {
		submitButton.disabled = !(cguCheckbox.checked && allRequiredFieldsFilled());
	}

	// Délégation sur le formulaire entier : couvre aussi les tuteurs et les champs
	// documents ajoutés dynamiquement après ce chargement de script.
	candidatureForm.addEventListener('input', updateSubmitButtonState);
	candidatureForm.addEventListener('change', updateSubmitButtonState);
	window.updateCandidatureSubmitState = updateSubmitButtonState;
	updateSubmitButtonState();
</script>

<script>
	const STEP_ORDER = ['#auth-2', '#auth-3', '#auth-4', '#auth-5'];

	function change_tab(targetSelector) {
		const targetId = targetSelector.replace('#', '');

		document.querySelectorAll('.step-panel').forEach(panel => {
			panel.classList.toggle('is-active', panel.id === targetId);
		});

		const idx = STEP_ORDER.indexOf(targetSelector);
		document.querySelectorAll('.stepper-item').forEach((item, i) => {
			item.classList.toggle('is-current', i === idx);
			item.classList.toggle('is-done', idx >= 0 && i < idx);
		});

		const pct = idx >= 0 ? (idx / (STEP_ORDER.length - 1)) * 100 : 0;
		document.getElementById('stepper-progress').style.width = pct + '%';

		document.getElementById('depot-stepper').scrollIntoView({ behavior: 'smooth', block: 'start' });
	}

	function scrollToWizard() {
		document.getElementById('depot-stepper').scrollIntoView({ behavior: 'smooth', block: 'start' });
	}

	document.querySelectorAll('.stepper-item').forEach(item => {
		item.addEventListener('click', () => change_tab(item.dataset.target));
	});

	document.addEventListener('DOMContentLoaded', function () {
		change_tab('#auth-2');

		document.querySelectorAll('[data-trigger]').forEach(el => {
			new Choices(el, {
				placeholderValue: 'Sélectionnez',
				searchPlaceholderValue: "Saisissez le nom de votre pays d'origine",
				shouldSort: false,
				itemSelectText: 'Press to select',
				allowHTML: true,
				callbackOnCreateTemplates: function(template) {
					return {
						item: (classNames, data) => {
							const flagCode = data.value ? data.value.toLowerCase() : '';
							return template(`
								<div class="${classNames.item} ${
								data.highlighted
									? classNames.highlightedState
									: classNames.itemSelectable
								}" data-item data-id="${data.id}" data-value="${data.value}" ${
								data.active ? 'aria-selected="true"' : ''
								} ${data.disabled ? 'aria-disabled="true"' : ''}>
								<span class="fi fi-${flagCode}" style="margin-right:8px; border-radius:2px;"></span>
								${data.label}
								</div>
							`);
						},
						choice: (classNames, data) => {
							const flagCode = data.value ? data.value.toLowerCase() : '';
							return template(`
								<div class="${classNames.item} ${classNames.itemChoice} ${
								data.disabled ? classNames.itemDisabled : classNames.itemSelectable
								}" data-select-text="${this.config.itemSelectText}" data-choice ${
								data.disabled
									? 'data-choice-disabled aria-disabled="true"'
									: 'data-choice-selectable'
								} data-id="${data.id}" data-value="${data.value}" ${
								data.groupId > 0 ? 'role="treeitem"' : 'role="option"'
								}>
								<span class="fi fi-${flagCode}" style="margin-right:8px; border-radius:2px;"></span>
								${data.label}
								</div>
							`);
						},
					};
				}
			});
		});
	});
</script>

</body>
</html>
