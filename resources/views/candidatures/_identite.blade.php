@php use App\Enums\GenreEnum; @endphp

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

			@if(isset($champsConfig['nom_jeune_fille']))
				<div class="field">
					<label for="nom_jeune_fille">{{ $champsConfig['nom_jeune_fille']->label }}
						@if($champsConfig['nom_jeune_fille']->obligatoire) <x-forms.required-field/> @else <span class="help-text" style="display:inline">(si mariée)</span> @endif
					</label>
					<input type="text" class="input-refined" name="nom_jeune_fille" id="nom_jeune_fille" placeholder="{{ $champsConfig['nom_jeune_fille']->label }}" value="{{ old('nom_jeune_fille') }}" @if($champsConfig['nom_jeune_fille']->obligatoire) required @endif/>
					{!! errorAlert($errors->first('nom_jeune_fille'), 'nom_jeune_fille') !!}
				</div>
			@endif

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

			@if(isset($champsConfig['numero_bordereau']))
				<div class="field">
					<label for="numero_bordereau">{{ $champsConfig['numero_bordereau']->label }}
						@if($champsConfig['numero_bordereau']->obligatoire) <x-forms.required-field/> @else <span class="help-text" style="display:inline">(optionnel)</span> @endif
					</label>
					<input type="text" class="input-refined" name="numero_bordereau" id="numero_bordereau" placeholder="{{ $champsConfig['numero_bordereau']->label }}" value="{{ old('numero_bordereau') }}" @if($champsConfig['numero_bordereau']->obligatoire) required @endif/>
					{!! errorAlert($errors->first('numero_bordereau'), 'numero_bordereau') !!}
				</div>
			@endif

			<div class="field field--full">
				<label for="choices-single-default">Nationalité <x-forms.required-field/></label>
				<select class="select-refined" name="nationalite" data-trigger id="choices-single-default" required>
					@foreach($countries as $code => $name)
						<option value="{{ $code }}" @selected(old('nationalite', 'TG') === $code)>{{ $name }}</option>
					@endforeach
				</select>
				{!! errorAlert($errors->first('nationalite'), 'nationalite') !!}
			</div>

			@if(isset($champsConfig['comment_connu_ecole']))
				<div class="field field--full">
					<label for="moyen_connaissance_id">Comment avez-vous connu {{ $sigleEtablissement ?: "notre établissement" }} ?
						@if($champsConfig['comment_connu_ecole']->obligatoire) <x-forms.required-field/> @else <span class="help-text" style="display:inline">(optionnel)</span> @endif
					</label>
					<select class="select-refined" name="moyen_connaissance_id" id="moyen_connaissance_id" @if($champsConfig['comment_connu_ecole']->obligatoire) required @endif>
						<option value="">-- Choisir --</option>
						@foreach($moyensConnaissance as $moyen)
							<option value="{{ $moyen->id }}" @selected((string) old('moyen_connaissance_id') === (string) $moyen->id)>{{ $moyen->libelle }}</option>
						@endforeach
					</select>
					{!! errorAlert($errors->first('moyen_connaissance_id'), 'moyen_connaissance_id') !!}
				</div>
			@endif

			<div class="field">
				<label for="type_diplome_id">Type du dernier diplôme <x-forms.required-field/></label>
				<select class="select-refined" name="type_diplome_id" id="type_diplome_id" required>
					<option value="">-- Choisir --</option>
					@foreach($typesDiplome as $type)
						<option value="{{ $type->id }}" @selected(old('type_diplome_id') ? (string) old('type_diplome_id') === (string) $type->id : $loop->first)>{{ $type->nom }}</option>
					@endforeach
				</select>
				{!! errorAlert($errors->first('type_diplome_id'), 'type_diplome_id') !!}
			</div>

			<div class="field" data-champ-key="numero_table" style="display:none">
				<label for="numero_table">Numéro de table <x-forms.required-field/></label>
				<input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="7" class="input-refined" name="numero_table" id="numero_table" placeholder="Numéro de table" value="{{ old('numero_table') }}"/>
				<small id="numero_table-live-error" class="text-danger" style="display:none;"></small>
				{!! errorAlert($errors->first('numero_table'), 'numero_table') !!}
			</div>

			<div class="field" data-champ-key="annee_bac" style="display:none">
				<label for="annee_bac">Année d'obtention du BAC <x-forms.required-field/></label>
				<input type="number" class="input-refined" name="annee_bac" id="annee_bac" placeholder="{{ date('Y') }}" min="1990" max="{{ date('Y') }}" value="{{ old('annee_bac') }}"/>
				<small id="annee_bac-live-error" class="text-danger" style="display:none;"></small>
				{!! errorAlert($errors->first('annee_bac'), 'annee_bac') !!}
			</div>

			<div class="field" data-champ-key="serie" style="display:none">
				<label for="serie">Série du BAC <x-forms.required-field/></label>
				<select class="select-refined" name="serie" id="serie">
					<option value="">-- Choisir --</option>
					<option value="C" @selected(old('serie')==='C')>C</option>
					<option value="D" @selected(old('serie')==='D')>D</option>
					<option value="E" @selected(old('serie')==='E')>E</option>
					<option value="F2" @selected(old('serie')==='F2')>F2</option>
				</select>
				{!! errorAlert($errors->first('serie'), 'serie') !!}
			</div>

			<div class="field" data-champ-key="mention_bac" style="display:none">
				<label for="mention_bac">Mention au BAC <x-forms.required-field/></label>
				<select class="select-refined" name="mention_bac" id="mention_bac">
					<option value="">-- Choisir --</option>
					<option value="Passable" @selected(old('mention_bac')==='Passable')>Passable</option>
					<option value="Assez Bien" @selected(old('mention_bac')==='Assez Bien')>Assez Bien</option>
					<option value="Bien" @selected(old('mention_bac')==='Bien')>Bien</option>
					<option value="Très Bien" @selected(old('mention_bac')==='Très Bien')>Très Bien</option>
				</select>
				{!! errorAlert($errors->first('mention_bac'), 'mention_bac') !!}
			</div>

			<div class="field" data-champ-key="etablissement_diplome" style="display:none">
				<label for="etablissement_diplome">Dernier établissement fréquenté <x-forms.required-field/></label>
				<input type="text" class="input-refined" name="etablissement_diplome" id="etablissement_diplome" placeholder="Établissement" value="{{ old('etablissement_diplome') }}"/>
				{!! errorAlert($errors->first('etablissement_diplome'), 'etablissement_diplome') !!}
			</div>

		</div>

		<script>
			// Les champs du parcours scolaire dépendent du type de diplôme choisi : la liste
			// des types (et, pour chacun, quels champs afficher/exiger) est configurée dans
			// Paramètres > Types de diplôme, pas codée en dur (voir TypeDiplomeChamp).
			const typesDiplome = @json($typesDiplome);

			(function () {
				const select = document.getElementById('type_diplome_id');
				const champKeys = ['numero_table', 'annee_bac', 'serie', 'mention_bac', 'etablissement_diplome'];

				function updateChampsParcours() {
					const typeId = select.value;
					const type = typesDiplome.find(t => String(t.id) === String(typeId));
					const champsDuType = {};
					(type?.champs || []).forEach(c => { champsDuType[c.champ_key] = !!c.obligatoire; });

					champKeys.forEach(key => {
						const wrapper = document.querySelector(`[data-champ-key="${key}"]`);
						if (!wrapper) return;
						const input = wrapper.querySelector('input, select');
						const marker = wrapper.querySelector('label .text-danger');
						const affiche = key in champsDuType;

						wrapper.style.display = affiche ? '' : 'none';
						input.required = affiche && champsDuType[key];
						if (!affiche) input.value = '';
						if (marker) marker.style.display = (affiche && champsDuType[key]) ? '' : 'none';
					});

					window.updateCandidatureSubmitState?.();
				}

				select.addEventListener('change', updateChampsParcours);
				document.addEventListener('DOMContentLoaded', updateChampsParcours);
			})();
		</script>

		<div class="step-actions">
			<button class="btn-refined btn-refined--ghost" type="button" onclick="document.getElementById('depot-hero').scrollIntoView({behavior:'smooth'})">← Retour à l'accueil</button>
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

			@if(isset($champsConfig['tel2']))
				<div class="field">
					<label for="tel2">{{ $champsConfig['tel2']->label }}
						@if($champsConfig['tel2']->obligatoire) <x-forms.required-field/> @else <span class="help-text" style="display:inline">(optionnel)</span> @endif
					</label>
					<input type="tel" class="input-refined" id="tel2" name="tel2" value="{{ old('tel2') }}" @if($champsConfig['tel2']->obligatoire) required @endif/>
					<input type="hidden" name="indicatif2" id="indicatif2">
					{!! errorAlert($errors->first('tel2'), 'tel2') !!}
				</div>
			@endif

			@if(isset($champsConfig['tel3']))
				<div class="field">
					<label for="tel3">{{ $champsConfig['tel3']->label }}
						@if($champsConfig['tel3']->obligatoire) <x-forms.required-field/> @else <span class="help-text" style="display:inline">(optionnel)</span> @endif
					</label>
					<input type="tel" class="input-refined" id="tel3" name="tel3" value="{{ old('tel3') }}" @if($champsConfig['tel3']->obligatoire) required @endif/>
					<input type="hidden" name="indicatif3" id="indicatif3">
					{!! errorAlert($errors->first('tel3'), 'tel3') !!}
				</div>
			@endif

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

		const numeroTableInput = document.getElementById('numero_table');
		const numeroTableError = document.getElementById('numero_table-live-error');
		const NUMERO_TABLE_MAX_LENGTH = 7;

		function checkNumeroTable() {
			// On retire tout caractère non numérique au fur et à mesure de la saisie,
			// et on tronque à 7 chiffres maximum.
			const cleaned = numeroTableInput.value.replace(/[^0-9]/g, '').slice(0, NUMERO_TABLE_MAX_LENGTH);
			if (cleaned !== numeroTableInput.value) {
				numeroTableInput.value = cleaned;
			}

			if (cleaned === '') {
				numeroTableInput.style.borderColor = '';
				numeroTableError.style.display = 'none';
				return;
			}

			numeroTableInput.style.borderColor = '';
			numeroTableError.style.display = 'none';
		}

		numeroTableInput.addEventListener('input', checkNumeroTable);
		numeroTableInput.addEventListener('blur', checkNumeroTable);
	})();
</script>
