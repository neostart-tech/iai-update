<div class="step-panel" id="auth-4">
	<div class="panel-card">
		<div class="panel-head">
			<p class="panel-kicker">Étape 3 / 4</p>
			<h2 class="panel-title">Vos pièces à fournir</h2>
			<p class="panel-sub">Ce concours d'admission concerne la Licence 1 : voici la liste des pièces à joindre à votre dossier.</p>
		</div>

		<input type="hidden" name="niveau_id" id="niveau_id" value="{{ old('niveau_id', $niveauCandidatureId) }}">
		<input type="hidden" name="filiere_id" id="filiere_id" value="{{ old('filiere_id', $filiereCandidatureId) }}">
		{!! errorAlert($errors->first('niveau_id'), 'niveau_id') !!}
		{!! errorAlert($errors->first('filiere_id'), 'filiere_id') !!}

		<div id="documents-requis-info" class="depot-info-box d-none">
			<span aria-hidden="true" class="note-icon">
				<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 256 256"><path d="M216,40H136V24a8,8,0,0,0-16,0V40H40A16,16,0,0,0,24,56V200a16,16,0,0,0,16,16H216a16,16,0,0,0,16-16V56A16,16,0,0,0,216,40ZM40,56h80V72a8,8,0,0,0,16,0V56h80V200H40ZM176,112a8,8,0,0,1-8,8H88a8,8,0,0,1,0-16h80A8,8,0,0,1,176,112Zm0,48a8,8,0,0,1-8,8H88a8,8,0,0,1,0-16h80A8,8,0,0,1,176,160Z"></path></svg>
			</span>
			<div>
				<strong>Pièces à fournir pour ce niveau</strong>
				<ul id="documents-requis-liste"></ul>
			</div>
		</div>

		<div id="documents-fields-empty" class="help-text">Aucune pièce à fournir n'est configurée pour le moment.</div>

		<div id="documents-fields-container" class="field-grid"></div>

		<div class="step-actions">
			<button class="btn-refined btn-refined--ghost" type="button" onclick="change_tab('#auth-3')">← Retour</button>
			<button class="btn-refined btn-refined--primary" type="button" onclick="change_tab('#auth-5')">Continuer →</button>
		</div>
	</div>
</div>

<script>
	// Aucun champ de document n'est codé en dur : la liste vient entièrement de la
	// configuration faite dans Paramètres > Niveaux (table document_requirements),
	// exactement comme pour CandidatureController::updateOrCreateAlbum côté validation.
	const documentRequirements = @json($documentRequirements ?? []);
	const documentServerErrors = @json($errors->messages() ?? []);

	// Reflète le mapping $mapKeyForUpload de CandidatureController::updateOrCreateAlbum.
	// Toute clé absente de cette liste (ex. un nouveau type ajouté par l'école) utilise
	// directement sa clé comme nom de champ — aucune modification de code n'est requise.
	const docKeyFieldOverrides = {
		lettre: 'lettre_file',
		naissance: 'naissance_file',
		diplome: 'diplome_file',
		nationalite: 'nationalite_file',
		photo: 'photo_identite_file',
		certificat_medical: 'certificat_medical_file',
		cv: 'cv_file',
		coupon: 'coupon_file',
		releve_bac1_path: 'releve_bac1',
		releve_bac2_path: 'releve_bac2',
	};

	const acceptByFormat = {
		image: 'image/png,image/jpeg,image/jpg',
		pdf: '.pdf',
		all: '.pdf,image/png,image/jpeg,image/jpg',
	};

	function fieldNameFor(req) {
		const base = docKeyFieldOverrides[req.document_key] || req.document_key;
		return req.is_multiple ? `${base}[]` : base;
	}

	function buildDocumentField(req) {
		const fieldName = fieldNameFor(req);
		const inputId = fieldName.replace('[]', '');
		const accept = acceptByFormat[req.accepted_formats] || acceptByFormat.all;

		const wrapper = document.createElement('div');
		wrapper.className = 'field';
		wrapper.dataset.docKey = req.document_key;

		const label = document.createElement('label');
		label.setAttribute('for', inputId);
		label.textContent = req.nom_affichage + ' ';

		const marker = document.createElement('span');
		if (req.is_obligatoire) {
			marker.innerHTML = '<span class="text-danger">*</span>';
		} else {
			marker.className = 'help-text';
			marker.style.display = 'inline';
			marker.textContent = '(optionnel)';
		}
		label.appendChild(marker);

		wrapper.appendChild(label);

		if (req.description) {
			const desc = document.createElement('p');
			desc.className = 'help-text';
			desc.textContent = req.description;
			wrapper.appendChild(desc);
		}

		const input = document.createElement('input');
		input.type = 'file';
		input.id = inputId;
		input.name = fieldName;
		input.accept = accept;
		input.className = 'file-dropzone-input';
		if (req.is_multiple) input.multiple = true;
		if (req.is_obligatoire) input.required = true;

		const dropzone = document.createElement('div');
		dropzone.className = 'file-dropzone';

		const visual = document.createElement('div');
		visual.className = 'file-dropzone-visual';
		visual.innerHTML = `
			<span class="file-dropzone-icon">
				<svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 7.5L12 3m0 0L7.5 7.5M12 3v13.5" /></svg>
			</span>
			<span class="file-dropzone-text"><strong>Glissez un fichier ici</strong> ou cliquez pour parcourir</span>
			<span class="file-dropzone-hint">${req.is_multiple ? 'Plusieurs fichiers acceptés' : 'Un seul fichier'}</span>
		`;

		dropzone.appendChild(input);
		dropzone.appendChild(visual);

		const chipList = document.createElement('div');
		chipList.className = 'file-chip-list';

		function renderChips() {
			chipList.innerHTML = '';
			const files = Array.from(input.files || []);
			dropzone.classList.toggle('has-files', files.length > 0);

			files.forEach((file, index) => {
				const chip = document.createElement('span');
				chip.className = 'file-chip';

				const chipIcon = document.createElement('span');
				chipIcon.className = 'file-chip-icon';
				chipIcon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m5.231 13.481L15 17.25m-1.519-3.481L12 15.25m6-3.75V19.5a2.25 2.25 0 01-2.25 2.25H8.25A2.25 2.25 0 016 19.5V4.5A2.25 2.25 0 018.25 2.25h5.379a1.5 1.5 0 011.06.44l2.122 2.121a1.5 1.5 0 01.44 1.061z" /></svg>';

				const chipName = document.createElement('span');
				chipName.className = 'file-chip-name';
				chipName.textContent = file.name;
				chipName.title = file.name;

				const chipRemove = document.createElement('button');
				chipRemove.type = 'button';
				chipRemove.className = 'file-chip-remove';
				chipRemove.setAttribute('aria-label', 'Retirer ce fichier');
				chipRemove.innerHTML = '&times;';
				chipRemove.addEventListener('click', function (e) {
					e.preventDefault();
					e.stopPropagation();
					const dt = new DataTransfer();
					Array.from(input.files).forEach((f, i) => {
						if (i !== index) dt.items.add(f);
					});
					input.files = dt.files;
					renderChips();
				});

				chip.appendChild(chipIcon);
				chip.appendChild(chipName);
				chip.appendChild(chipRemove);
				chipList.appendChild(chip);
			});
		}

		input.addEventListener('change', renderChips);

		input.addEventListener('dragover', function (e) {
			e.preventDefault();
			dropzone.classList.add('is-dragover');
		});
		input.addEventListener('dragleave', function () {
			dropzone.classList.remove('is-dragover');
		});
		input.addEventListener('drop', function (e) {
			e.preventDefault();
			dropzone.classList.remove('is-dragover');

			const droppedFiles = Array.from(e.dataTransfer.files || []);
			if (!droppedFiles.length) return;

			const dt = new DataTransfer();
			if (req.is_multiple) {
				Array.from(input.files || []).forEach(f => dt.items.add(f));
				droppedFiles.forEach(f => dt.items.add(f));
			} else {
				dt.items.add(droppedFiles[0]);
			}
			input.files = dt.files;
			renderChips();
		});

		wrapper.appendChild(dropzone);
		wrapper.appendChild(chipList);

		// Quand le fichier est totalement absent, Laravel retombe sur le document_key brut
		// (ex. "photo") plutôt que sur le nom de champ mappé (ex. "photo_identite_file") :
		// on vérifie donc les deux formes pour ne jamais perdre le message d'erreur.
		const errorKey = Object.keys(documentServerErrors).find(k =>
			k === inputId || k === `${inputId}.*` || k === req.document_key || k === `${req.document_key}.*`
		);
		if (errorKey) {
			const err = document.createElement('small');
			err.className = 'text-danger';
			err.textContent = documentServerErrors[errorKey][0];
			wrapper.appendChild(err);
		}

		return wrapper;
	}

	function updateDocumentsRequis() {
		const niveauId = document.getElementById('niveau_id').value;
		const filiereId = document.getElementById('filiere_id').value;

		const infoBox = document.getElementById('documents-requis-info');
		const liste = document.getElementById('documents-requis-liste');
		const container = document.getElementById('documents-fields-container');
		const emptyState = document.getElementById('documents-fields-empty');

		container.innerHTML = '';
		liste.innerHTML = '';

		if (!niveauId) {
			infoBox.classList.add('d-none');
			emptyState.classList.remove('d-none');
			return;
		}

		const applicables = documentRequirements.filter(req =>
			String(req.niveau_id) === String(niveauId) &&
			(req.filiere_id === null || String(req.filiere_id) === String(filiereId))
		);

		applicables.forEach(req => {
			container.appendChild(buildDocumentField(req));
			const li = document.createElement('li');
			li.textContent = req.nom_affichage + (req.is_obligatoire ? '' : ' (optionnel)');
			liste.appendChild(li);
		});

		emptyState.classList.toggle('d-none', applicables.length > 0);
		infoBox.classList.toggle('d-none', applicables.length === 0);

		// Les champs fichiers viennent d'être (re)construits : le bouton de soumission
		// doit être réévalué (ex. document obligatoire pas encore choisi).
		window.updateCandidatureSubmitState?.();
	}

	// Le niveau et la filière sont déterminés automatiquement par le backend (concours
	// ouvert uniquement en Licence 1) : la liste des pièces s'affiche dès le chargement.
	document.addEventListener('DOMContentLoaded', updateDocumentsRequis);
</script>
