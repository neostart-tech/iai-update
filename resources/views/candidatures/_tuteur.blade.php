<div class="step-panel" id="auth-5">
	<div class="panel-card">
		<div class="panel-head">
			<p class="panel-kicker">Étape 4 / 4</p>
			<h2 class="panel-title">Parent(s) ou tuteur(s)</h2>
			<p class="panel-sub">Renseignez au moins un parent ou tuteur. Vous pouvez en ajouter plusieurs si nécessaire.</p>
		</div>

		<div id="tuteurs-container"></div>

		<button type="button" id="add-tuteur-btn" class="btn-refined btn-refined--ghost">+ Ajouter un autre tuteur/parent</button>

		<div class="consent-row" style="margin-top: 30px;">
			<input type="checkbox" value="1" id="accept_cgu" name="accept_cgu" {{ old('accept_cgu') ? 'checked' : '' }}>
			<label for="accept_cgu" style="text-transform:none; font-weight:500; letter-spacing:normal; color:var(--muted);">
				J'ai lu et j'accepte les <a href="{{ route('cgu') }}" target="_blank" style="color:var(--navy-deep); text-decoration:underline;">conditions générales d'utilisation</a> <x-forms.required-field/>
			</label>
		</div>
		{!! errorAlert($errors->first('accept_cgu'), 'accept_cgu') !!}

		<div class="step-actions">
			<button class="btn-refined btn-refined--ghost" type="button" onclick="change_tab('#auth-4')">← Retour</button>
			<button class="btn-refined btn-refined--gold auth-conf" type="button">Soumettre ma candidature</button>
		</div>
	</div>
</div>

<template id="tuteur-card-template">
	<div class="tuteur-card">
		<div class="tuteur-card-head">
			<h3 class="tuteur-card-title"></h3>
			<button type="button" class="remove-tuteur-btn">Retirer</button>
		</div>
		<div class="field-grid">
			<div class="field">
				<label class="tuteur-label-nom">Nom <x-forms.required-field/></label>
				<input type="text" class="input-refined field-nom" placeholder="Nom" required/>
			</div>

			<div class="field">
				<label>Prénom <x-forms.required-field/></label>
				<input type="text" class="input-refined field-prenom" placeholder="Prénom" required/>
			</div>

			@if(isset($champsConfig['tuteur_profession']))
				<div class="field">
					<label>{{ $champsConfig['tuteur_profession']->label }} @if($champsConfig['tuteur_profession']->obligatoire) <x-forms.required-field/> @else <span class="help-text" style="display:inline">(optionnel)</span> @endif</label>
					<input type="text" class="input-refined field-profession" placeholder="{{ $champsConfig['tuteur_profession']->label }}" @if($champsConfig['tuteur_profession']->obligatoire) required @endif/>
				</div>
			@endif

			@if(isset($champsConfig['tuteur_employeur']))
				<div class="field">
					<label>{{ $champsConfig['tuteur_employeur']->label }} @if($champsConfig['tuteur_employeur']->obligatoire) <x-forms.required-field/> @else <span class="help-text" style="display:inline">(optionnel)</span> @endif</label>
					<input type="text" class="input-refined field-employeur" placeholder="{{ $champsConfig['tuteur_employeur']->label }}" @if($champsConfig['tuteur_employeur']->obligatoire) required @endif/>
				</div>
			@endif

			@if(isset($champsConfig['tuteur_email']))
				<div class="field">
					<label>{{ $champsConfig['tuteur_email']->label }} @if($champsConfig['tuteur_email']->obligatoire) <x-forms.required-field/> @else <span class="help-text" style="display:inline">(optionnel)</span> @endif</label>
					<input type="email" class="input-refined field-email" placeholder="{{ $champsConfig['tuteur_email']->label }}" @if($champsConfig['tuteur_email']->obligatoire) required @endif/>
				</div>
			@endif

			@if(isset($champsConfig['tuteur_tel']))
				<div class="field">
					<label>{{ $champsConfig['tuteur_tel']->label }} @if($champsConfig['tuteur_tel']->obligatoire) <x-forms.required-field/> @else <span class="help-text" style="display:inline">(optionnel)</span> @endif</label>
					<input type="tel" class="input-refined field-tel" placeholder="" @if($champsConfig['tuteur_tel']->obligatoire) required @endif/>
					<input type="hidden" class="field-indicatif">
					<p class="help-text">Numéro togolais ou étranger.</p>
				</div>
			@endif

			@if(isset($champsConfig['tuteur_adresse']))
				<div class="field field--full">
					<label>{{ $champsConfig['tuteur_adresse']->label }} @if($champsConfig['tuteur_adresse']->obligatoire) <x-forms.required-field/> @else <span class="help-text" style="display:inline">(optionnel)</span> @endif</label>
					<input type="text" class="input-refined field-adresse" placeholder="{{ $champsConfig['tuteur_adresse']->label }}" @if($champsConfig['tuteur_adresse']->obligatoire) required @endif/>
				</div>
			@endif

			<div class="field field--full">
				<label class="tuteur-responsable-check">
					<input type="checkbox" class="field-responsable-frais" value="1">
					<span>Responsable des frais de scolarité</span>
				</label>
			</div>
		</div>
	</div>
</template>

<script>
	(function () {
		const container = document.getElementById('tuteurs-container');
		const template = document.getElementById('tuteur-card-template');
		const addBtn = document.getElementById('add-tuteur-btn');
		let tuteurCount = 0;

		function fieldName(index, key) {
			return `tuteurs[${index}][${key}]`;
		}

		function addTuteurCard() {
			const index = tuteurCount++;
			const card = template.content.firstElementChild.cloneNode(true);
			card.dataset.index = index;

			// Chaque champ tuteur est configurable (afficher/masquer par école, voir
			// Paramètres > Champs obligatoires) : un champ masqué n'existe tout simplement
			// plus dans ce template, d'où le `?.` défensif sur chaque querySelector.
			card.querySelector('.tuteur-card-title').textContent = index === 0 ? 'Tuteur / Parent 1' : `Tuteur / Parent ${index + 1}`;
			card.querySelector('.field-nom').name = fieldName(index, 'nom');
			card.querySelector('.field-prenom').name = fieldName(index, 'prenom');
			card.querySelector('.field-profession')?.setAttribute('name', fieldName(index, 'profession'));
			card.querySelector('.field-employeur')?.setAttribute('name', fieldName(index, 'employeur'));
			card.querySelector('.field-email')?.setAttribute('name', fieldName(index, 'email'));
			card.querySelector('.field-tel')?.setAttribute('name', fieldName(index, 'tel'));
			card.querySelector('.field-indicatif')?.setAttribute('name', fieldName(index, 'indicatif'));
			card.querySelector('.field-adresse')?.setAttribute('name', fieldName(index, 'adresse'));
			card.querySelector('.field-responsable-frais').name = fieldName(index, 'responsable_des_frais');

			card.querySelector('.remove-tuteur-btn').addEventListener('click', function () {
				card.remove();
				updateRemoveButtonsVisibility();
				window.updateCandidatureSubmitState?.();
			});

			container.appendChild(card);
			updateRemoveButtonsVisibility();
			initPhoneInput(card);
			window.updateCandidatureSubmitState?.();
		}

		function initPhoneInput(card) {
			const telInput = card.querySelector('.field-tel');
			const indicatifInput = card.querySelector('.field-indicatif');
			if (!telInput || !indicatifInput) return; // champ téléphone tuteur masqué pour cette école

			const iti = window.intlTelInput(telInput, {
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
				indicatifInput.value = country && country.dialCode ? country.dialCode : '228';
			};
			telInput.addEventListener('input', updateIndicatif);
			telInput.addEventListener('paste', () => setTimeout(updateIndicatif));
			telInput.addEventListener('cut', () => setTimeout(updateIndicatif));
			telInput.addEventListener('countrychange', updateIndicatif);
			updateIndicatif();
		}

		function updateRemoveButtonsVisibility() {
			const cards = container.querySelectorAll('.tuteur-card');
			cards.forEach((card, i) => {
				card.querySelector('.remove-tuteur-btn').style.display = cards.length > 1 ? 'inline-flex' : 'none';
			});
		}

		addBtn.addEventListener('click', addTuteurCard);

		// Un premier tuteur est toujours présent au chargement de la page. On attend
		// DOMContentLoaded car ce script s'exécute avant que la librairie intlTelInput
		// (chargée plus bas dans le document) ne soit disponible.
		document.addEventListener('DOMContentLoaded', addTuteurCard);
	})();
</script>
