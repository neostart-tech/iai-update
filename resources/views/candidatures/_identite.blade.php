@php use App\Enums\GenreEnum; use App\Enums\TypeDiplomeEnum; @endphp

<div class="step-panel is-active" id="auth-2">
	<div class="panel-card">
		<div class="panel-head">
			<p class="panel-kicker">Étape 1 / 4</p>
			<h2 class="panel-title">Votre identité</h2>
			<p class="panel-sub">Les informations qui figureront sur votre dossier de candidature.</p>
		</div>

		<div class="field-grid">
			<div class="field">
				<label for="nom">Nom <x-forms.required-field/></label>
				<input type="text" class="input-refined" id="nom" name="nom" placeholder="Nom" value="{{ old('nom') }}" required/>
				{!! errorAlert($errors->first('nom'), 'nom') !!}
			</div>

			<div class="field">
				<label for="prenom">Prénoms <x-forms.required-field/></label>
				<input type="text" class="input-refined" name="prenom" id="prenom" placeholder="Prénoms" value="{{ old('prenom') }}" required/>
				{!! errorAlert($errors->first('prenom'), 'prenom') !!}
			</div>

			<div class="field">
				<label for="nom_jeune_fille">Nom de jeune fille <span class="help-text" style="display:inline">(si mariée)</span></label>
				<input type="text" class="input-refined" name="nom_jeune_fille" id="nom_jeune_fille" placeholder="Nom de jeune fille" value="{{ old('nom_jeune_fille') }}"/>
				{!! errorAlert($errors->first('nom_jeune_fille'), 'nom_jeune_fille') !!}
			</div>

			<div class="field">
				<label for="genre">Sexe <x-forms.required-field/></label>
				<select class="select-refined" name="genre" id="genre" required>
					<option value="">-- Choisir --</option>
					@foreach(GenreEnum::cases() as $genre)
						@continue($genre === GenreEnum::T)
						<option value="{{ $genre->value }}" @selected(old('genre') === $genre->value)>{{ $genre->value }}</option>
					@endforeach
				</select>
				{!! errorAlert($errors->first('genre'), 'genre') !!}
			</div>

			<div class="field">
				<label for="date_naissance">Date de naissance <x-forms.required-field/></label>
				<input type="date" class="input-refined" name="date_naissance" id="date_naissance" max="{{ date('Y') - 15 }}-12-31" value="{{ old('date_naissance') }}" required/>
				{!! errorAlert($errors->first('date_naissance'), 'date_naissance') !!}
			</div>

			<div class="field">
				<label for="lieu_naissance">Lieu de naissance <x-forms.required-field/></label>
				<input type="text" class="input-refined" name="lieu_naissance" id="lieu_naissance" placeholder="Lieu de naissance" value="{{ old('lieu_naissance') }}" required/>
				{!! errorAlert($errors->first('lieu_naissance'), 'lieu_naissance') !!}
			</div>

			<div class="field field--full">
				<label for="choices-single-default">Nationalité <x-forms.required-field/></label>
				<select class="select-refined" name="nationalite" data-trigger id="choices-single-default" required>
					@foreach($countries as $code => $name)
						<option value="{{ $code }}" @selected(old('nationalite', 'TG') === $code)>{{ $name }}</option>
					@endforeach
				</select>
				{!! errorAlert($errors->first('nationalite'), 'nationalite') !!}
			</div>

			<div class="field">
				<label for="numero_table">Numéro de table <x-forms.required-field/></label>
				<input type="text" class="input-refined" name="numero_table" id="numero_table" placeholder="Numéro de table" value="{{ old('numero_table') }}" required/>
				{!! errorAlert($errors->first('numero_table'), 'numero_table') !!}
			</div>

			<div class="field">
				<label for="annee_bac">Année d'obtention du BAC <x-forms.required-field/></label>
				<input type="number" class="input-refined" name="annee_bac" id="annee_bac" placeholder="{{ date('Y') }}" min="1990" max="{{ date('Y') }}" value="{{ old('annee_bac') }}" required/>
				<small id="annee_bac-live-error" class="text-danger" style="display:none;"></small>
				{!! errorAlert($errors->first('annee_bac'), 'annee_bac') !!}
			</div>

			<div class="field">
				<label for="serie">Série du BAC <x-forms.required-field/></label>
				<select class="select-refined" name="serie" id="serie" required>
					<option value="">-- Choisir --</option>
					<option value="C" @selected(old('serie')==='C')>C</option>
					<option value="D" @selected(old('serie')==='D')>D</option>
					<option value="E" @selected(old('serie')==='E')>E</option>
					<option value="F2" @selected(old('serie')==='F2')>F2</option>
				</select>
				{!! errorAlert($errors->first('serie'), 'serie') !!}
			</div>

			<div class="field">
				<label for="mention_bac">Mention au BAC <x-forms.required-field/></label>
				<select class="select-refined" name="mention_bac" id="mention_bac" required>
					<option value="">-- Choisir --</option>
					<option value="Passable" @selected(old('mention_bac')==='Passable')>Passable</option>
					<option value="Assez Bien" @selected(old('mention_bac')==='Assez Bien')>Assez Bien</option>
					<option value="Bien" @selected(old('mention_bac')==='Bien')>Bien</option>
					<option value="Très Bien" @selected(old('mention_bac')==='Très Bien')>Très Bien</option>
				</select>
				{!! errorAlert($errors->first('mention_bac'), 'mention_bac') !!}
			</div>

			<div class="field">
				<label for="type_diplome">Type du dernier diplôme <x-forms.required-field/></label>
				<select class="select-refined" name="type_diplome" id="type_diplome" required>
					@foreach(TypeDiplomeEnum::cases() as $type)
						<option value="{{ $type->value }}" @selected(old('type_diplome') === $type->value)>{{ $type->value }}</option>
					@endforeach
				</select>
				{!! errorAlert($errors->first('type_diplome'), 'type_diplome') !!}
			</div>

		</div>

		<div class="step-actions">
			<button class="btn-refined btn-refined--ghost" type="button" onclick="window.location.href='{{ $publicFrontendUrl }}'">← Retour à l'accueil</button>
			<button class="btn-refined btn-refined--primary" type="button" onclick="change_tab('#auth-3')">Continuer →</button>
		</div>
	</div>
</div>

<div class="step-panel" id="auth-3">
	<div class="panel-card">
		<div class="panel-head">
			<p class="panel-kicker">Étape 2 / 4</p>
			<h2 class="panel-title">Informations complémentaires</h2>
			<p class="panel-sub">Pour vous joindre tout au long de la procédure de candidature.</p>
		</div>

		<div class="field-grid">
			<div class="field field--full">
				<label for="tel-input">Numéro de téléphone 1 <x-forms.required-field/></label>
				<input type="tel" class="input-refined phone-input" id="tel-input" name="tel" value="{{ old('tel') }}" placeholder="" required/>
				<p class="help-text">Numéro togolais ou étranger.</p>
				{!! errorAlert($errors->first('tel'), 'tel') !!}
			</div>

			<div class="field">
				<label for="tel2">Numéro de téléphone 2 <span class="help-text" style="display:inline">(optionnel)</span></label>
				<input type="tel" class="input-refined" id="tel2" name="tel2" value="{{ old('tel2') }}"/>
				<input type="hidden" name="indicatif2" id="indicatif2">
				{!! errorAlert($errors->first('tel2'), 'tel2') !!}
			</div>

			<div class="field">
				<label for="tel3">Numéro de téléphone 3 <span class="help-text" style="display:inline">(optionnel)</span></label>
				<input type="tel" class="input-refined" id="tel3" name="tel3" value="{{ old('tel3') }}"/>
				<input type="hidden" name="indicatif3" id="indicatif3">
				{!! errorAlert($errors->first('tel3'), 'tel3') !!}
			</div>

			<input type="hidden" name="indicatif" id="indicatif">

			<div class="field field--full">
				<label for="email">Adresse email <x-forms.required-field/></label>
				<input type="email" class="input-refined" name="email" value="{{ old('email') }}" id="email" placeholder="mon.adresse@domain.com" required/>
				{!! errorAlert($errors->first('email'), 'email') !!}
			</div>

		</div>

		<div class="step-actions">
			<button class="btn-refined btn-refined--ghost" type="button" onclick="change_tab('#auth-2')">← Retour</button>
			<button class="btn-refined btn-refined--primary" type="button" onclick="change_tab('#auth-4')">Continuer →</button>
		</div>
	</div>
</div>

<script>
	(function () {
		const anneeBacInput = document.getElementById('annee_bac');
		const anneeBacError = document.getElementById('annee_bac-live-error');
		const currentYear = new Date().getFullYear();
		const minYear = 1990;

		function checkAnneeBac() {
			const raw = anneeBacInput.value.trim();

			if (raw === '') {
				anneeBacInput.style.borderColor = '';
				anneeBacError.style.display = 'none';
				return;
			}

			const value = Number(raw);
			let message = '';

			if (!Number.isInteger(value)) {
				message = "Veuillez saisir une année valide.";
			} else if (value > currentYear) {
				message = "L'année du BAC ne peut pas être postérieure à " + currentYear + ".";
			} else if (value < minYear) {
				message = "Veuillez saisir une année à partir de " + minYear + ".";
			}

			if (message) {
				anneeBacInput.style.borderColor = 'var(--danger)';
				anneeBacError.textContent = message;
				anneeBacError.style.display = 'block';
			} else {
				anneeBacInput.style.borderColor = '';
				anneeBacError.style.display = 'none';
			}
		}

		anneeBacInput.addEventListener('input', checkAnneeBac);
		anneeBacInput.addEventListener('blur', checkAnneeBac);
		checkAnneeBac();
	})();
</script>
