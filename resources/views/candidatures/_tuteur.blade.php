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
				J'ai lu et j'accepte les <a href="{{ route('cgu') }}" target="_blank" rel="noopener noreferrer" style="color:var(--brand-700); text-decoration:underline;">conditions générales d'utilisation</a> <x-forms.required-field/>
			</label>
		</div>
		{!! errorAlert($errors->first('accept_cgu'), 'accept_cgu') !!}

		<p id="submit-help" class="submit-help" role="status" aria-live="polite">
                        Complétez tous les champs obligatoires, ajoutez les pièces demandées et acceptez les conditions pour soumettre le dossier.
                </p>

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

			<div class="field">
				<label>Profession <x-forms.required-field/></label>
				<input type="text" class="input-refined field-profession" placeholder="Profession" required/>
			</div>

			<div class="field">
				<label>Nom de l'employeur <span class="help-text" style="display:inline">(optionnel)</span></label>
				<input type="text" class="input-refined field-employeur" placeholder="Nom de l'employeur"/>
			</div>

			<div class="field">
				<label>Email <span class="help-text" style="display:inline">(optionnel)</span></label>
				<input type="email" class="input-refined field-email" placeholder="Email"/>
			</div>

			<div class="field">
				<label>Téléphone <x-forms.required-field/></label>
				<input type="tel" class="input-refined field-tel" placeholder="" required/>
				<input type="hidden" class="field-indicatif">
				<p class="help-text">Numéro togolais ou étranger.</p>
			</div>

			<div class="field field--full">
				<label>Adresse / Quartier <x-forms.required-field/></label>
				<input type="text" class="input-refined field-adresse" placeholder="Adresse / Quartier" required/>
			</div>

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

			card.querySelector('.tuteur-card-title').textContent = index === 0 ? 'Tuteur / Parent 1' : `Tuteur / Parent ${index + 1}`;
			card.querySelector('.field-nom').name = fieldName(index, 'nom');
			card.querySelector('.field-prenom').name = fieldName(index, 'prenom');
			card.querySelector('.field-profession').name = fieldName(index, 'profession');
			card.querySelector('.field-employeur').name = fieldName(index, 'employeur');
			card.querySelector('.field-email').name = fieldName(index, 'email');
			card.querySelector('.field-tel').name = fieldName(index, 'tel');
			card.querySelector('.field-indicatif').name = fieldName(index, 'indicatif');
			card.querySelector('.field-adresse').name = fieldName(index, 'adresse');
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
