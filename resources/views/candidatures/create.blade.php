<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Déposer mon dossier — {{ config('app.name') }}</title>
	@php
		$logoPath = \App\Helpers\ConfigHelper::getAppLogo();
		$fallbackLogo = 'https://www.iai-togo.tg/wp-content/uploads/2017/06/logo.jpeg';
		$logoUrl = $logoPath && Storage::disk('public')->exists($logoPath) ? Storage::url($logoPath) : $fallbackLogo;
			$publicFrontendUrl = rtrim((string) config('app.public_frontend_url', 'http://localhost:3000'), '/');
@endphp
	<link rel="icon" href="{{ $logoUrl }}" type="image/x-icon">
	@include('candidatures._styles')
	<link rel="stylesheet" href="{{ asset('tel/build/css/intlTelInput.css') }}">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.0.0/css/flag-icons.min.css">
</head>
<body class="depot-body">

<div class="depot-page-shell">
	<header class="depot-topbar">
		<a href="{{ $publicFrontendUrl }}" class="depot-brand" aria-label="Retour à l'accueil">
			<img src="{{ $logoUrl }}" class="depot-logo" alt="Logo IAI-Togo">
			<span>
				<strong>IAI-TOGO</strong>
				<small>Institut Africain d'Informatique</small>
			</span>
		</a>

		<div class="depot-topbar-actions">
			<a href="{{ $publicFrontendUrl }}" class="depot-topbar-link">Accueil</a>
			<span class="depot-topbar-badge">Dépôt de candidature</span>
		</div>
	</header>

	<div class="split-layout">
		<aside class="split-left">
			<div class="left-content">
				<p class="depot-hero-kicker">Dossier de candidature</p>
				<h1 class="depot-hero-title">
					Votre avenir en informatique <span>commence ici</span>
				</h1>
				<p class="depot-hero-lede">
					Complétez votre dossier en quatre étapes. Munissez-vous de vos informations personnelles, de vos contacts, des pièces justificatives et des coordonnées de votre parent ou tuteur.
				</p>

				<div class="depot-hero-checklist" aria-label="Informations utiles avant de commencer">
					<div>
						<span>1</span>
						<p>Identité et informations académiques</p>
					</div>
					<div>
						<span>2</span>
						<p>Contacts du candidat</p>
					</div>
					<div>
						<span>3</span>
						<p>Pièces justificatives demandées</p>
					</div>
					<div>
						<span>4</span>
						<p>Parent ou tuteur responsable</p>
					</div>
				</div>

				<p class="left-footer">
					Les informations transmises sont utilisées uniquement pour le traitement du dossier de candidature.
				</p>
			</div>
		</aside>

		<main class="split-right" id="main-content">
			<div class="right-content">
				<div class="depot-hero-note">
					<span aria-hidden="true" class="note-icon">
						<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 256 256"><path d="M128,24A104,104,0,1,0,232,128,104.11,104.11,0,0,0,128,24Zm0,192a88,88,0,1,1,88-88A88.1,88.1,0,0,1,128,216Zm-8-80V80a8,8,0,0,1,16,0v56a8,8,0,0,1-16,0Zm20,36a12,12,0,1,1-12-12A12,12,0,0,1,140,172Z"></path></svg>
					</span>
					<span><strong>Important :</strong> seules les séries <strong>C</strong>, <strong>D</strong>, <strong>E</strong> et <strong>F2</strong> sont acceptées pour cette procédure.</span>
				</div>

				@if(count($errors->all()) > 0)
					<div class="depot-alert depot-alert--error">
						<strong>Des informations sont manquantes ou incorrectes.</strong>
						<span>Parcourez les étapes ci-dessous pour corriger les champs signalés.</span>
					</div>
				@endif

				<form action="{{ route('candidatures.store') }}" method="post" id="candidature-form" enctype="multipart/form-data" class="depot-form">
					@csrf

					<div class="stepper" id="depot-stepper" aria-label="Progression du dépôt de candidature">
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
</div>

<script src="{{ asset('admin/assets/js/plugins/sweetalert2.all.min.js') }}"></script>
<script src="{{ asset('admin/assets/js/plugins/choices.min.js') }}"></script>
<script src="{{ asset('tel/build/js/intlTelInput.js') }}"></script>

<script>
	function initPhoneInput(inputEl, indicatifEl) {
		if (!inputEl || !indicatifEl || !window.intlTelInput) return;

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
                if (!showMissingRequirements()) {
                        return;
                }

                Swal.fire({
                        title: '<strong>À votre attention</strong>',
                        icon: 'info',
                        html: 'Confirmez-vous le dépôt de votre dossier de candidature à IAI-Togo ?',
                        showCloseButton: true,
                        showCancelButton: true,
                        focusConfirm: false,
                        confirmButtonColor: '#0f6f3d',
                        confirmButtonText: "Oui, je suis d'accord",
                        cancelButtonText: 'Non, ne pas valider',
                }).then((result) => {
                        if (result.isConfirmed) {
                                document.getElementById('candidature-form').submit();
                                Swal.fire('Dépôt de la candidature en cours...', '', 'info');
                        }
                });
        });

        const submitButton = document.querySelector('.auth-conf');
        const cguCheckbox = document.getElementById('accept_cgu');
        const candidatureForm = document.getElementById('candidature-form');
        const submitHelp = document.getElementById('submit-help');

        function isVisibleField(field) {
                return Boolean(field.offsetParent || field.type === 'hidden' || field.type === 'file');
        }

        function fieldIsFilled(field) {
                if (field.disabled) {
                        return true;
                }

                if (field.type === 'checkbox') {
                        return field.checked;
                }

                if (field.type === 'file') {
                        return Boolean(field.files && field.files.length > 0);
                }

                if (field.type === 'radio') {
                        const group = candidatureForm.querySelectorAll(`[name="${field.name}"]`);
                        return Array.from(group).some(item => item.checked);
                }

                return Boolean(field.value && field.value.trim());
        }

        function firstMissingRequiredField() {
                const requiredFields = candidatureForm.querySelectorAll('[required]');

                for (const field of requiredFields) {
                        if (!isVisibleField(field)) {
                                continue;
                        }

                        if (!fieldIsFilled(field)) {
                                return field;
                        }
                }

                return null;
        }

        function updateFieldVisualState() {
                const requiredFields = candidatureForm.querySelectorAll('[required]');

                requiredFields.forEach(field => {
                        const invalid = isVisibleField(field) && !fieldIsFilled(field);
                        field.toggleAttribute('aria-invalid', invalid);
                        field.classList.toggle('is-invalid-lite', invalid);
                });
        }

        function allRequiredFieldsFilled() {
                return firstMissingRequiredField() === null;
        }

        function updateSubmitButtonState() {
                const ready = Boolean(cguCheckbox.checked && allRequiredFieldsFilled());

                submitButton.classList.toggle('is-disabled', !ready);
                submitButton.setAttribute('aria-disabled', ready ? 'false' : 'true');

                if (submitHelp) {
                        submitHelp.textContent = ready
                                ? 'Votre dossier semble complet. Vous pouvez le soumettre.'
                                : 'Complétez tous les champs obligatoires, ajoutez les pièces demandées et acceptez les conditions pour soumettre le dossier.';
                        submitHelp.classList.toggle('is-ready', ready);
                }

                updateFieldVisualState();
        }

        function showMissingRequirements() {
                const missingField = firstMissingRequiredField();

                if (missingField) {
                        const panel = missingField.closest('.step-panel');
                        const stepSelector = panel ? `#${panel.id}` : null;

                        if (stepSelector && typeof change_tab === 'function') {
                                change_tab(stepSelector);
                        }

                        setTimeout(() => {
                                missingField.focus?.({ preventScroll: true });
                                missingField.scrollIntoView?.({ behavior: 'smooth', block: 'center' });
                        }, 180);

                        Swal.fire({
                                title: 'Dossier incomplet',
                                icon: 'warning',
                                html: 'Veuillez compléter le premier champ obligatoire manquant avant de soumettre votre candidature.',
                                confirmButtonColor: '#0f6f3d',
                                confirmButtonText: 'Compris',
                        });

                        updateSubmitButtonState();
                        return false;
                }

                if (!cguCheckbox.checked) {
                        cguCheckbox.focus();

                        Swal.fire({
                                title: 'Conditions à accepter',
                                icon: 'warning',
                                html: "Veuillez accepter les conditions générales d'utilisation avant de soumettre votre candidature.",
                                confirmButtonColor: '#0f6f3d',
                                confirmButtonText: 'Compris',
                        });

                        updateSubmitButtonState();
                        return false;
                }

                return true;
        }

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
