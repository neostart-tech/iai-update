@extends('base', [
	'title' => 'Nouvelle candidature',
	'breadcrumbs' => [
		'Administration',
		[
			'text' =>'Candidatures',
			'url' => route('admin.candidatures.index')
		], 
		'Nouvelle candidature'
	],
	'page_name' => 'Création d\'une nouvelle candidature'
])

@section('content')
<div class="row">
	<div class="col-12">
		<div class="candidature-form-container">
			<!-- Form Header -->
			<div class="form-header">
				<div class="header-content">
					<div class="header-icon">
						<i class="fas fa-user-plus"></i>
					</div>
					<div class="header-text">
						<h1>Nouvelle Candidature</h1>
						<p class="text-muted">Remplissez toutes les informations pour créer une nouvelle candidature</p>
					</div>
				</div>
				<div class="header-actions">
					<a href="{{ route('admin.candidatures.index') }}" class="btn btn-outline-secondary">
						<i class="fas fa-arrow-left me-2"></i>Retour
					</a>
				</div>
			</div>

			<!-- Form Content -->
			<form id="candidatureForm" method="POST" action="{{ route('admin.candidatures.store-by-admin') }}" enctype="multipart/form-data" class="modern-form">
				@csrf
				
				<!-- Accordion Container -->
				<div class="form-accordion">
					<!-- Section 1: Personal Information -->
					<div class="accordion-section active mt-5">
						<div class="accordion-header">
							<div class="section-number">01</div>
							<div class="section-title">
								<h3><i class="fas fa-user-circle me-2"></i>Informations Personnelles</h3>
								<p class="section-description">Renseignez les informations personnelles du candidat</p>
							</div>
							<div class="section-toggle">
								<i class="fas fa-chevron-down"></i>
							</div>
						</div>
						<div class="accordion-content">
							<div class="section-grid">
								<div class="form-group">
									<label for="nom">Nom <span class="required">*</span></label>
									<div class="input-with-icon">
										<i class="fas fa-signature"></i>
										<input type="text" id="nom" name="nom" value="{{ old('nom') }}" required 
											   placeholder="Entrez le nom" class="form-control">
									</div>
								</div>
								
								<div class="form-group">
									<label for="prenom">Prénom(s) <span class="required">*</span></label>
									<div class="input-with-icon">
										<i class="fas fa-user"></i>
										<input type="text" id="prenom" name="prenom" value="{{ old('prenom') }}" required 
											   placeholder="Entrez le(s) prénom(s)" class="form-control">
									</div>
								</div>
								
								<div class="form-group">
									<label for="nom_jeune_fille">Nom de jeune fille</label>
									<div class="input-with-icon">
										<i class="fas fa-female"></i>
										<input type="text" id="nom_jeune_fille" name="nom_jeune_fille" value="{{ old('nom_jeune_fille') }}" 
											   placeholder="Entrez le nom de jeune fille" class="form-control">
									</div>
								</div>
								
								<div class="form-group">
									<label for="genre">Genre <span class="required">*</span></label>
									<div class="input-with-icon">
										<i class="fas fa-venus-mars"></i>
										<select id="genre" name="genre" required class="form-control">
											<option value="">Sélectionner le genre</option>
											<option value="Masculin" {{ old('genre') == 'Masculin' ? 'selected' : '' }}>Masculin</option>
											<option value="Féminin" {{ old('genre') == 'Féminin' ? 'selected' : '' }}>Féminin</option>
										</select>
									</div>
								</div>
								
								<div class="form-group">
									<label for="date_naissance">Date de naissance <span class="required">*</span></label>
									<div class="input-with-icon">
										<i class="fas fa-calendar-alt"></i>
										<input type="date" id="date_naissance" name="date_naissance" value="{{ old('date_naissance') }}" required 
											   class="form-control">
									</div>
								</div>
								
								<div class="form-group">
									<label for="lieu_naissance">Lieu de naissance <span class="required">*</span></label>
									<div class="input-with-icon">
										<i class="fas fa-map-marker-alt"></i>
										<input type="text" id="lieu_naissance" name="lieu_naissance" value="{{ old('lieu_naissance') }}" required 
											   placeholder="Entrez le lieu de naissance" class="form-control">
									</div>
								</div>
								
								<div class="form-group">
									<label for="email">Email <span class="required">*</span></label>
									<div class="input-with-icon">
										<i class="fas fa-envelope"></i>
										<input type="email" id="email" name="email" value="{{ old('email') }}" required 
											   placeholder="Entrez l'email" class="form-control">
									</div>
								</div>
								
								<div class="form-group">
									<label for="nationalite">Nationalité <span class="required">*</span></label>
									<div class="input-with-icon">
										<i class="fas fa-globe"></i>
										<input type="text" id="nationalite" name="nationalite" value="{{ old('nationalite') }}" required 
											   placeholder="Entrez la nationalité" class="form-control">
									</div>
								</div>
								
								<div class="form-group">
									<label for="tel">Téléphone 1 <span class="required">*</span></label>
									<div class="input-with-icon">
										<i class="fas fa-phone"></i>
										<input type="tel" id="tel" name="tel" value="{{ old('tel') }}" required 
											   placeholder="Entrez le numéro de téléphone" class="form-control">
									</div>
								</div>
								
								<div class="form-group">
									<label for="tel2">Téléphone 2</label>
									<div class="input-with-icon">
										<i class="fas fa-mobile-alt"></i>
										<input type="tel" id="tel2" name="tel2" value="{{ old('tel2') }}" 
											   placeholder="Entrez un second numéro (optionnel)" class="form-control">
									</div>
								</div>
								
								<div class="form-group">
									<label for="tel3">Téléphone 3</label>
									<div class="input-with-icon">
										<i class="fas fa-phone-square"></i>
										<input type="tel" id="tel3" name="tel3" value="{{ old('tel3') }}" 
											   placeholder="Entrez un troisième numéro (optionnel)" class="form-control">
									</div>
								</div>
								
								<div class="form-group">
									<label for="bp">Boîte postale</label>
									<div class="input-with-icon">
										<i class="fas fa-inbox"></i>
										<input type="text" id="bp" name="bp" value="{{ old('bp') }}" 
											   placeholder="Entrez la boîte postale" class="form-control">
									</div>
								</div>
								
								<div class="form-group">
									<label for="fax">Fax</label>
									<div class="input-with-icon">
										<i class="fas fa-fax"></i>
										<input type="text" id="fax" name="fax" value="{{ old('fax') }}" 
											   placeholder="Entrez le numéro de fax" class="form-control">
									</div>
								</div>
								
								<div class="form-group full-width">
									<label for="hobbit">Centres d'intérêt / Hobbit</label>
									<div class="input-with-icon">
										<i class="fas fa-heart"></i>
										<textarea id="hobbit" name="hobbit" rows="3" 
												  placeholder="Décrivez les centres d'intérêt du candidat" class="form-control">{{ old('hobbit') }}</textarea>
									</div>
								</div>
							</div>
						</div>
					</div>

					<!-- Section 2: Academic Information -->
					<div class="accordion-section">
						<div class="accordion-header">
							<div class="section-number">02</div>
							<div class="section-title">
								<h3><i class="fas fa-graduation-cap me-2"></i>Informations Académiques</h3>
								<p class="section-description">Détails académiques et parcours scolaire</p>
							</div>
							<div class="section-toggle">
								<i class="fas fa-chevron-down"></i>
							</div>
						</div>
						<div class="accordion-content">
							<div class="section-grid">
								<div class="form-group">
									<label for="numero_table">Numéro de table</label>
									<div class="input-with-icon">
										<i class="fas fa-hashtag"></i>
										<input type="text" id="numero_table" name="numero_table" value="{{ old('numero_table') }}" 
											   placeholder="Entrez le numéro de table" class="form-control">
									</div>
								</div>
								
								<div class="form-group">
									<label for="annee_bac">Année du BAC</label>
									<div class="input-with-icon">
										<i class="fas fa-calendar"></i>
										<input type="number" id="annee_bac" name="annee_bac" value="{{ old('annee_bac') }}" 
											   min="1900" max="{{ date('Y') }}" placeholder="Année" class="form-control">
									</div>
								</div>
								
								<div class="form-group">
									<label for="serie">Série du BAC</label>
									<div class="input-with-icon">
										<i class="fas fa-book"></i>
										<input type="text" id="serie" name="serie" value="{{ old('serie') }}" 
											   placeholder="Entrez la série du BAC" class="form-control">
									</div>
								</div>
								
								<div class="form-group">
									<label for="filiere_id">Filière <span class="required">*</span></label>
									<div class="input-with-icon">
										<i class="fas fa-code-branch"></i>
										<select id="filiere_id" name="filiere_id" required class="form-control">
											<option value="">Sélectionner une filière</option>
											@foreach ($filieres as $filiere)
												<option value="{{ $filiere->id }}" {{ old('filiere_id') == $filiere->id ? 'selected' : '' }}>
													{{ $filiere->code }} - {{ $filiere->nom }}
												</option>
											@endforeach
										</select>
									</div>
								</div>
								
								<div class="form-group">
									<label for="niveau_id">Niveau <span class="required">*</span></label>
									<div class="input-with-icon">
										<i class="fas fa-layer-group"></i>
										<select id="niveau_id" name="niveau_id" required class="form-control">
											<option value="">Sélectionner un niveau</option>
											@foreach ($niveaux as $niveau)
												<option value="{{ $niveau->id }}" {{ old('niveau_id') == $niveau->id ? 'selected' : '' }}>
													{{ $niveau->libelle }}
												</option>
											@endforeach
										</select>
									</div>
								</div>
								
								<div class="form-group">
									<label for="type_diplome">Type de diplôme <span class="required">*</span></label>
									<div class="input-with-icon">
										<i class="fas fa-certificate"></i>
										<select id="type_diplome" name="type_diplome" required class="form-control">
											<option value="">Sélectionner le type de diplôme</option>
											@foreach (App\Enums\TypeDiplomeEnum::cases() as $type)
												<option value="{{ $type->value }}" {{ old('type_diplome') == $type->value ? 'selected' : '' }}>
													{{ $type->value }}
												</option>
											@endforeach
										</select>
									</div>
								</div>
								
								<div class="form-group full-width">
									<label for="lettre_motivation">Lettre de motivation</label>
									<div class="input-with-icon">
										<i class="fas fa-edit"></i>
										<textarea id="lettre_motivation" name="lettre_motivation" rows="4" 
												  placeholder="Lettre de motivation du candidat" class="form-control">{{ old('lettre_motivation') }}</textarea>
									</div>
								</div>
							</div>
						</div>
					</div>

					<!-- Section 3: Responsible -->
					<div class="accordion-section">
						<div class="accordion-header">
							<div class="section-number">03</div>
							<div class="section-title">
								<h3><i class="fas fa-user-tie me-2"></i>Responsable Légal</h3>
								<p class="section-description">Informations du responsable des frais de scolarité</p>
							</div>
							<div class="section-toggle">
								<i class="fas fa-chevron-down"></i>
							</div>
						</div>
						<div class="accordion-content">
							<div class="section-grid">
								<div class="form-group">
									<label for="nom_resp">Nom <span class="required">*</span></label>
									<div class="input-with-icon">
										<i class="fas fa-user-tie"></i>
										<input type="text" id="nom_resp" name="nom_resp" value="{{ old('nom_resp') }}" required 
											   placeholder="Entrez le nom du responsable" class="form-control">
									</div>
								</div>
								
								<div class="form-group">
									<label for="prenom_resp">Prénom <span class="required">*</span></label>
									<div class="input-with-icon">
										<i class="fas fa-user"></i>
										<input type="text" id="prenom_resp" name="prenom_resp" value="{{ old('prenom_resp') }}" required 
											   placeholder="Entrez le prénom du responsable" class="form-control">
									</div>
								</div>
								
								<div class="form-group">
									<label for="profession_resp">Profession</label>
									<div class="input-with-icon">
										<i class="fas fa-briefcase"></i>
										<input type="text" id="profession_resp" name="profession_resp" value="{{ old('profession_resp') }}" 
											   placeholder="Entrez la profession" class="form-control">
									</div>
								</div>
								
								<div class="form-group">
									<label for="employeur_resp">Employeur</label>
									<div class="input-with-icon">
										<i class="fas fa-building"></i>
										<input type="text" id="employeur_resp" name="employeur_resp" value="{{ old('employeur_resp') }}" 
											   placeholder="Entrez le nom de l'employeur" class="form-control">
									</div>
								</div>
								
								<div class="form-group">
									<label for="email_resp">Email</label>
									<div class="input-with-icon">
										<i class="fas fa-envelope"></i>
										<input type="email" id="email_resp" name="email_resp" value="{{ old('email_resp') }}" 
											   placeholder="Entrez l'email du responsable" class="form-control">
									</div>
								</div>
								
								<div class="form-group">
									<label for="tel_resp">Téléphone <span class="required">*</span></label>
									<div class="input-with-icon">
										<i class="fas fa-phone"></i>
										<input type="tel" id="tel_resp" name="tel_resp" value="{{ old('tel_resp') }}" required 
											   placeholder="Entrez le numéro de téléphone" class="form-control">
									</div>
								</div>
								
								<div class="form-group full-width">
									<label for="adresse_resp">Adresse</label>
									<div class="input-with-icon">
										<i class="fas fa-map-marker-alt"></i>
										<textarea id="adresse_resp" name="adresse_resp" rows="3" 
												  placeholder="Entrez l'adresse du responsable" class="form-control">{{ old('adresse_resp') }}</textarea>
									</div>
								</div>
								
								<div class="form-group">
									<label for="bp_resp">Boîte postale</label>
									<div class="input-with-icon">
										<i class="fas fa-inbox"></i>
										<input type="text" id="bp_resp" name="bp_resp" value="{{ old('bp_resp') }}" 
											   placeholder="Entrez la boîte postale" class="form-control">
									</div>
								</div>
								
								<div class="form-group">
									<label for="fax_resp">Fax</label>
									<div class="input-with-icon">
										<i class="fas fa-fax"></i>
										<input type="text" id="fax_resp" name="fax_resp" value="{{ old('fax_resp') }}" 
											   placeholder="Entrez le numéro de fax" class="form-control">
									</div>
								</div>
							</div>
						</div>
					</div>

					<!-- Section 4: Tutor -->
					<div class="accordion-section">
						<div class="accordion-header">
							<div class="section-number">04</div>
							<div class="section-title">
								<h3><i class="fas fa-user-graduate me-2"></i>Tuteur Académique</h3>
								<p class="section-description">Informations du tuteur (optionnel - si différent du responsable)</p>
							</div>
							<div class="section-toggle">
								<i class="fas fa-chevron-down"></i>
							</div>
						</div>
						<div class="accordion-content">
							<div class="section-grid">
								<div class="form-group">
									<label for="nom_tuteur">Nom</label>
									<div class="input-with-icon">
										<i class="fas fa-user-graduate"></i>
										<input type="text" id="nom_tuteur" name="nom_tuteur" value="{{ old('nom_tuteur') }}" 
											   placeholder="Entrez le nom du tuteur" class="form-control">
									</div>
								</div>
								
								<div class="form-group">
									<label for="prenom_tuteur">Prénom</label>
									<div class="input-with-icon">
										<i class="fas fa-user"></i>
										<input type="text" id="prenom_tuteur" name="prenom_tuteur" value="{{ old('prenom_tuteur') }}" 
											   placeholder="Entrez le prénom du tuteur" class="form-control">
									</div>
								</div>
								
								<div class="form-group">
									<label for="profession_tuteur">Profession</label>
									<div class="input-with-icon">
										<i class="fas fa-briefcase"></i>
										<input type="text" id="profession_tuteur" name="profession_tuteur" value="{{ old('profession_tuteur') }}" 
											   placeholder="Entrez la profession" class="form-control">
									</div>
								</div>
								
								<div class="form-group">
									<label for="employeur_tuteur">Employeur</label>
									<div class="input-with-icon">
										<i class="fas fa-building"></i>
										<input type="text" id="employeur_tuteur" name="employeur_tuteur" value="{{ old('employeur_tuteur') }}" 
											   placeholder="Entrez le nom de l'employeur" class="form-control">
									</div>
								</div>
								
								<div class="form-group">
									<label for="email_tuteur">Email</label>
									<div class="input-with-icon">
										<i class="fas fa-envelope"></i>
										<input type="email" id="email_tuteur" name="email_tuteur" value="{{ old('email_tuteur') }}" 
											   placeholder="Entrez l'email du tuteur" class="form-control">
									</div>
								</div>
								
								<div class="form-group">
									<label for="tel_tuteur">Téléphone</label>
									<div class="input-with-icon">
										<i class="fas fa-phone"></i>
										<input type="tel" id="tel_tuteur" name="tel_tuteur" value="{{ old('tel_tuteur') }}" 
											   placeholder="Entrez le numéro de téléphone" class="form-control">
									</div>
								</div>
								
								<div class="form-group full-width">
									<label for="adresse_tuteur">Adresse</label>
									<div class="input-with-icon">
										<i class="fas fa-map-marker-alt"></i>
										<textarea id="adresse_tuteur" name="adresse_tuteur" rows="3" 
												  placeholder="Entrez l'adresse du tuteur" class="form-control">{{ old('adresse_tuteur') }}</textarea>
									</div>
								</div>
								
								<div class="form-group">
									<label for="bp_tuteur">Boîte postale</label>
									<div class="input-with-icon">
										<i class="fas fa-inbox"></i>
										<input type="text" id="bp_tuteur" name="bp_tuteur" value="{{ old('bp_tuteur') }}" 
											   placeholder="Entrez la boîte postale" class="form-control">
									</div>
								</div>
								
								<div class="form-group">
									<label for="fax_tuteur">Fax</label>
									<div class="input-with-icon">
										<i class="fas fa-fax"></i>
										<input type="text" id="fax_tuteur" name="fax_tuteur" value="{{ old('fax_tuteur') }}" 
											   placeholder="Entrez le numéro de fax" class="form-control">
									</div>
								</div>
							</div>
						</div>
					</div>

					<!-- Section 5: Documents -->
					<div class="accordion-section">
						<div class="accordion-header">
							<div class="section-number">05</div>
							<div class="section-title">
								<h3><i class="fas fa-folder me-2"></i>Documents à Joindre</h3>
								<p class="section-description">Téléchargez les documents requis pour la candidature</p>
							</div>
							<div class="section-toggle">
								<i class="fas fa-chevron-down"></i>
							</div>
						</div>
						<div class="accordion-content">
							<div class="documents-grid">
								<!-- Required Documents -->
								<div class="documents-section">
									<h4 class="documents-title">Documents obligatoires</h4>
									<div class="documents-list">
										@php
											$requiredDocs = [
												['id' => 'photo_identite_file', 'label' => 'Photo d\'identité', 'icon' => 'camera', 'accept' => 'image/*'],
												['id' => 'naissance_file', 'label' => 'Acte de naissance', 'icon' => 'file-pdf', 'accept' => '.pdf'],
											];
										@endphp
										
										@foreach($requiredDocs as $doc)
										<div class="document-card required">
											<label class="document-label" for="{{ $doc['id'] }}">
												<i class="fas fa-{{ $doc['icon'] }} me-2"></i>{{ $doc['label'] }} <span class="required">*</span>
											</label>
											<div class="file-upload-wrapper">
												<input type="file" id="{{ $doc['id'] }}" name="{{ $doc['id'] }}" 
													   accept="{{ $doc['accept'] }}" required class="file-input">
												<div class="file-upload-area">
													<i class="fas fa-cloud-upload-alt upload-icon"></i>
													<p class="upload-text">Cliquez ou glissez pour télécharger</p>
													<small class="upload-hint">Max. {{ $doc['id'] == 'photo_identite_file' ? '2MB' : '5MB' }}</small>
												</div>
												<div class="file-preview"></div>
											</div>
										</div>
										@endforeach
									</div>
								</div>
								
								<!-- Optional Documents -->
								<div class="documents-section">
									<h4 class="documents-title">Documents optionnels</h4>
									<div class="documents-list">
										@php
											$optionalDocs = [
												['id' => 'lettre_file', 'label' => 'Lettre manuscrite', 'icon' => 'file-alt', 'accept' => '.pdf,.doc,.docx'],
												['id' => 'diplome_file', 'label' => 'Diplôme', 'icon' => 'file-certificate', 'accept' => '.pdf'],
												['id' => 'nationalite_file', 'label' => 'Attestation de nationalité', 'icon' => 'passport', 'accept' => '.pdf'],
												['id' => 'certificat_medical_file', 'label' => 'Certificat médical', 'icon' => 'file-medical', 'accept' => '.pdf'],
												['id' => 'coupon_file', 'label' => 'Coupon', 'icon' => 'receipt', 'accept' => '.pdf'],
											];
										@endphp
										
										@foreach($optionalDocs as $doc)
										<div class="document-card optional">
											<label class="document-label" for="{{ $doc['id'] }}">
												<i class="fas fa-{{ $doc['icon'] }} me-2"></i>{{ $doc['label'] }}
											</label>
											<div class="file-upload-wrapper">
												<input type="file" id="{{ $doc['id'] }}" name="{{ $doc['id'] }}" 
													   accept="{{ $doc['accept'] }}" class="file-input">
												<div class="file-upload-area">
													<i class="fas fa-cloud-upload-alt upload-icon"></i>
													<p class="upload-text">Cliquez ou glissez pour télécharger</p>
													<small class="upload-hint">Max. 5MB</small>
												</div>
												<div class="file-preview"></div>
											</div>
										</div>
										@endforeach
									</div>
								</div>
								
								<!-- Multiple Files Sections -->
								<div class="multiple-files-section">
									<div class="section-header">
										<h4><i class="fas fa-copy me-2"></i>Bulletins de notes (optionnel)</h4>
										<span class="section-subtitle">Vous pouvez télécharger plusieurs fichiers pour chaque niveau</span>
									</div>
									<div class="multiple-files-grid">
										@php
											$bulletins = [
												['id' => 'bulletins_seconde', 'label' => 'Seconde', 'icon' => 'file-alt'],
												['id' => 'bulletins_premiere', 'label' => 'Première', 'icon' => 'file-alt'],
												['id' => 'bulletins_terminale', 'label' => 'Terminale', 'icon' => 'file-alt'],
											];
										@endphp
										
										@foreach($bulletins as $bulletin)
										<div class="multiple-file-card">
											<label class="multiple-file-label">
												<i class="fas fa-{{ $bulletin['icon'] }} me-2"></i>{{ $bulletin['label'] }}
											</label>
											<div class="multiple-file-upload">
												<input type="file" id="{{ $bulletin['id'] }}" name="{{ $bulletin['id'] }}[]" 
													   accept=".pdf" multiple class="multiple-file-input">
												<div class="upload-area multiple">
													<i class="fas fa-cloud-upload-alt upload-icon"></i>
													<p class="upload-text">Glissez ou cliquez pour ajouter des fichiers</p>
													<small class="upload-hint">PDF uniquement • Max 5MB par fichier</small>
												</div>
												<div class="files-count">
													<span class="count-text">0 fichier(s) sélectionné(s)</span>
												</div>
											</div>
										</div>
										@endforeach
									</div>
								</div>
								
								<div class="multiple-files-section">
									<div class="section-header">
										<h4><i class="fas fa-file-invoice me-2"></i>Relevés de notes BAC (optionnel)</h4>
										<span class="section-subtitle">Téléchargez les relevés de notes du BAC</span>
									</div>
									<div class="multiple-files-grid">
										<div class="multiple-file-card">
											<label class="multiple-file-label">
												<i class="fas fa-file-signature me-2"></i>Relevé BAC 1
											</label>
											<div class="multiple-file-upload">
												<input type="file" id="releve_bac1" name="releve_bac1[]" 
													   accept=".pdf" multiple class="multiple-file-input">
												<div class="upload-area multiple">
													<i class="fas fa-cloud-upload-alt upload-icon"></i>
													<p class="upload-text">Glissez ou cliquez pour ajouter des fichiers</p>
													<small class="upload-hint">PDF uniquement • Max 5MB par fichier</small>
												</div>
												<div class="files-count">
													<span class="count-text">0 fichier(s) sélectionné(s)</span>
												</div>
											</div>
										</div>
										
										<div class="multiple-file-card">
											<label class="multiple-file-label">
												<i class="fas fa-file-signature me-2"></i>Relevé BAC 2
											</label>
											<div class="multiple-file-upload">
												<input type="file" id="releve_bac2" name="releve_bac2[]" 
													   accept=".pdf" multiple class="multiple-file-input">
												<div class="upload-area multiple">
													<i class="fas fa-cloud-upload-alt upload-icon"></i>
													<p class="upload-text">Glissez ou cliquez pour ajouter des fichiers</p>
													<small class="upload-hint">PDF uniquement • Max 5MB par fichier</small>
												</div>
												<div class="files-count">
													<span class="count-text">0 fichier(s) sélectionné(s)</span>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- Form Footer -->
				<div class="form-footer">
					<div class="footer-content">
						<div class="form-check">
							<input class="form-check-input" type="checkbox" id="confirmTerms" required>
							<label class="form-check-label" for="confirmTerms">
								Je certifie que toutes les informations fournies sont exactes et complètes, et j'accepte les conditions générales de traitement des candidatures.
							</label>
						</div>
						
						<div class="footer-actions">
							<a href="{{ route('admin.candidatures.index') }}" class="btn btn-outline-secondary">
								<i class="fas fa-times me-2"></i>Annuler
							</a>
							<button type="submit" class="btn btn-primary btn-submit">
								<i class="fas fa-paper-plane me-2"></i>Créer la candidature
							</button>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
@endsection

@section('other-css')
<style>
:root {
	--primary-color: #4361ee;
	--primary-light: #eef2ff;
	--secondary-color: #64748b;
	--success-color: #10b981;
	--danger-color: #ef4444;
	--warning-color: #f59e0b;
	--border-color: #e2e8f0;
	--card-bg: #ffffff;
	--background-color: #f8fafc;
	--text-color: #1e293b;
	--text-muted: #64748b;
}

/* Main Container */
.candidature-form-container {
	background: var(--card-bg);
	border-radius: 16px;
	box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
	overflow: hidden;
	margin-bottom: 2rem;
}

/* Form Header */
.form-header {
	background: linear-gradient(135deg, var(--primary-color), #3a56d4);
	color: white;
	padding: 2rem 2.5rem;
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 2rem;
}

.header-content {
	display: flex;
	align-items: center;
	gap: 1.5rem;
	flex: 1;
}

.header-icon {
	width: 64px;
	height: 64px;
	background: rgba(255, 255, 255, 0.2);
	border-radius: 12px;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 1.75rem;
}

.header-text h1 {
	font-size: 1.75rem;
	font-weight: 600;
	margin: 0;
	color: white;
}

.header-text p {
	color: rgba(255, 255, 255, 0.9);
	margin: 0.25rem 0 0 0;
	font-size: 0.95rem;
}

.header-actions .btn {
	background: rgba(255, 255, 255, 0.1);
	border: 1px solid rgba(255, 255, 255, 0.2);
	color: white;
	padding: 0.75rem 1.5rem;
}

.header-actions .btn:hover {
	background: rgba(255, 255, 255, 0.2);
}

/* Form Accordion */
.form-accordion {
	padding: 0 2.5rem;
}

.accordion-section {
	border: 1px solid var(--border-color);
	border-radius: 12px;
	margin-bottom: 1.5rem;
	overflow: hidden;
	transition: all 0.3s ease;
}

.accordion-section.active {
	border-color: var(--primary-color);
	box-shadow: 0 4px 12px rgba(67, 97, 238, 0.1);
}

.accordion-section:not(:first-child) {
	margin-top: 1.5rem;
}

.accordion-header {
	background: var(--background-color);
	padding: 1.5rem 2rem;
	display: flex;
	align-items: center;
	gap: 1.5rem;
	cursor: pointer;
	transition: all 0.3s ease;
}

.accordion-section.active .accordion-header {
	background: var(--primary-light);
	border-bottom: 1px solid var(--border-color);
}

.section-number {
	width: 48px;
	height: 48px;
	background: var(--primary-color);
	color: white;
	border-radius: 10px;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 1.25rem;
	font-weight: 600;
	flex-shrink: 0;
}

.section-title {
	flex: 1;
}

.section-title h3 {
	font-size: 1.25rem;
	font-weight: 600;
	color: var(--text-color);
	margin: 0;
	display: flex;
	align-items: center;
}

.section-description {
	color: var(--text-muted);
	margin: 0.25rem 0 0 0;
	font-size: 0.9rem;
}

.section-toggle {
	color: var(--secondary-color);
	transition: transform 0.3s ease;
}

.accordion-section.active .section-toggle {
	transform: rotate(180deg);
	color: var(--primary-color);
}

.accordion-content {
	padding: 2rem;
	display: none;
	animation: fadeIn 0.3s ease;
}

.accordion-section.active .accordion-content {
	display: block;
}

@keyframes fadeIn {
	from { opacity: 0; }
	to { opacity: 1; }
}

/* Section Grid */
.section-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
	gap: 1.5rem;
}

.form-group {
	margin-bottom: 1rem;
}

.form-group.full-width {
	grid-column: 1 / -1;
}

.form-group label {
	display: block;
	font-weight: 600;
	color: var(--text-color);
	margin-bottom: 0.5rem;
	font-size: 0.95rem;
}

.required {
	color: var(--danger-color);
}

.input-with-icon {
	position: relative;
}

.input-with-icon i {
	position: absolute;
	left: 1rem;
	top: 50%;
	transform: translateY(-50%);
	color: var(--secondary-color);
	z-index: 1;
}

.input-with-icon input,
.input-with-icon select,
.input-with-icon textarea {
	width: 100%;
	padding: 0.875rem 1rem 0.875rem 3rem;
	border: 1px solid var(--border-color);
	border-radius: 8px;
	font-size: 0.95rem;
	background: white;
	transition: all 0.3s ease;
}

.input-with-icon textarea {
	min-height: 100px;
	resize: vertical;
}

.input-with-icon select {
	appearance: none;
	background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%2364748b' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
	background-repeat: no-repeat;
	background-position: right 1rem center;
	background-size: 16px;
}

.input-with-icon input:focus,
.input-with-icon select:focus,
.input-with-icon textarea:focus {
	outline: none;
	border-color: var(--primary-color);
	box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
}

.input-with-icon input:focus ~ i,
.input-with-icon select:focus ~ i,
.input-with-icon textarea:focus ~ i {
	color: var(--primary-color);
}

/* Documents Grid */
.documents-grid {
	display: flex;
	flex-direction: column;
	gap: 2.5rem;
}

.documents-section {
	background: var(--background-color);
	border-radius: 12px;
	padding: 1.5rem;
}

.documents-title {
	font-size: 1.1rem;
	font-weight: 600;
	color: var(--text-color);
	margin-bottom: 1.5rem;
	padding-bottom: 0.75rem;
	border-bottom: 2px solid var(--border-color);
}

.documents-list {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
	gap: 1.5rem;
}

.document-card {
	background: white;
	border: 1px solid var(--border-color);
	border-radius: 10px;
	padding: 1.5rem;
	transition: all 0.3s ease;
}

.document-card.required {
	border-left: 4px solid var(--danger-color);
}

.document-card.optional {
	border-left: 4px solid var(--warning-color);
}

.document-card:hover {
	box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
	transform: translateY(-2px);
}

.document-label {
	display: flex;
	align-items: center;
	font-weight: 600;
	color: var(--text-color);
	margin-bottom: 1rem;
	font-size: 0.95rem;
}

.file-upload-wrapper {
	position: relative;
}

.file-input {
	position: absolute;
	width: 100%;
	height: 100%;
	top: 0;
	left: 0;
	opacity: 0;
	cursor: pointer;
	z-index: 2;
}

.file-upload-area {
	border: 2px dashed var(--border-color);
	border-radius: 8px;
	padding: 2rem;
	text-align: center;
	transition: all 0.3s ease;
	background: white;
	position: relative;
	z-index: 1;
}

.file-upload-area:hover {
	border-color: var(--primary-color);
	background: var(--primary-light);
}

.upload-icon {
	font-size: 2rem;
	color: var(--secondary-color);
	margin-bottom: 1rem;
}

.upload-text {
	font-weight: 500;
	color: var(--text-color);
	margin-bottom: 0.5rem;
}

.upload-hint {
	color: var(--text-muted);
	font-size: 0.85rem;
}

.file-preview {
	margin-top: 1rem;
	display: none;
}

.file-upload-wrapper.has-file .file-upload-area {
	display: none;
}

.file-upload-wrapper.has-file .file-preview {
	display: block;
}

/* Multiple Files Sections */
.multiple-files-section {
	background: white;
	border: 1px solid var(--border-color);
	border-radius: 12px;
	padding: 1.5rem;
}

.section-header {
	margin-bottom: 1.5rem;
}

.section-header h4 {
	font-size: 1.1rem;
	font-weight: 600;
	color: var(--text-color);
	margin: 0 0 0.5rem 0;
	display: flex;
	align-items: center;
}

.section-subtitle {
	color: var(--text-muted);
	font-size: 0.9rem;
}

.multiple-files-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
	gap: 1.5rem;
}

.multiple-file-card {
	background: var(--background-color);
	border-radius: 10px;
	padding: 1.5rem;
	border: 1px solid var(--border-color);
}

.multiple-file-label {
	display: flex;
	align-items: center;
	font-weight: 600;
	color: var(--text-color);
	margin-bottom: 1rem;
	font-size: 0.95rem;
}

.multiple-file-upload {
	position: relative;
}

.multiple-file-input {
	position: absolute;
	width: 100%;
	height: 100%;
	top: 0;
	left: 0;
	opacity: 0;
	cursor: pointer;
	z-index: 2;
}

.upload-area.multiple {
	border: 2px dashed var(--border-color);
	border-radius: 8px;
	padding: 1.5rem;
	text-align: center;
	transition: all 0.3s ease;
	background: white;
	position: relative;
	z-index: 1;
}

.upload-area.multiple:hover {
	border-color: var(--primary-color);
	background: var(--primary-light);
}

.files-count {
	margin-top: 1rem;
	text-align: center;
}

.count-text {
	font-size: 0.9rem;
	color: var(--text-muted);
	font-weight: 500;
}

.multiple-file-upload.has-files .upload-area.multiple {
	display: none;
}

.multiple-file-upload.has-files .files-count {
	display: block;
}

/* Form Footer */
.form-footer {
	background: var(--background-color);
	border-top: 1px solid var(--border-color);
	padding: 2rem 2.5rem;
}

.footer-content {
	display: flex;
	flex-direction: column;
	gap: 2rem;
}

.form-check {
	margin: 0;
}

.form-check-input {
	width: 1.25rem;
	height: 1.25rem;
	margin-right: 0.75rem;
	border: 2px solid var(--border-color);
}

.form-check-input:checked {
	background-color: var(--primary-color);
	border-color: var(--primary-color);
}

.form-check-input:focus {
	border-color: var(--primary-color);
	box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
}

.form-check-label {
	color: var(--text-color);
	font-size: 0.95rem;
}

.footer-actions {
	display: flex;
	justify-content: flex-end;
	gap: 1rem;
}

.btn-submit {
	padding: 0.875rem 2rem;
	font-weight: 600;
	background: var(--primary-color);
	border: none;
	transition: all 0.3s ease;
}

.btn-submit:hover {
	background: #3a56d4;
	transform: translateY(-2px);
	box-shadow: 0 4px 12px rgba(67, 97, 238, 0.2);
}

.btn-submit:disabled {
	opacity: 0.7;
	cursor: not-allowed;
}

.btn-outline-secondary {
	border: 1px solid var(--border-color);
	color: var(--text-color);
	padding: 0.875rem 1.5rem;
}

.btn-outline-secondary:hover {
	background: var(--background-color);
	border-color: var(--border-color);
}

/* Error States */
.input-with-icon input:invalid:not(:focus):not(:placeholder-shown),
.input-with-icon select:invalid:not(:focus),
.input-with-icon textarea:invalid:not(:focus):not(:placeholder-shown) {
	border-color: var(--danger-color);
}

.input-with-icon input:invalid:not(:focus):not(:placeholder-shown) ~ i,
.input-with-icon select:invalid:not(:focus) ~ i {
	color: var(--danger-color);
}

/* Loading State */
.btn-submit.loading {
	position: relative;
	color: transparent;
}

.btn-submit.loading::after {
	content: '';
	position: absolute;
	width: 20px;
	height: 20px;
	border: 2px solid rgba(255, 255, 255, 0.3);
	border-top-color: white;
	border-radius: 50%;
	animation: spin 1s linear infinite;
	top: 50%;
	left: 50%;
	transform: translate(-50%, -50%);
}

@keyframes spin {
	to { transform: translate(-50%, -50%) rotate(360deg); }
}

/* Responsive Design */
@media (max-width: 1200px) {
	.section-grid {
		grid-template-columns: repeat(2, 1fr);
	}
	
	.documents-list {
		grid-template-columns: repeat(2, 1fr);
	}
	
	.multiple-files-grid {
		grid-template-columns: repeat(2, 1fr);
	}
}

@media (max-width: 768px) {
	.form-header {
		flex-direction: column;
		text-align: center;
		padding: 1.5rem;
		gap: 1rem;
	}
	
	.header-content {
		flex-direction: column;
		text-align: center;
	}
	
	.header-icon {
		width: 56px;
		height: 56px;
		font-size: 1.5rem;
	}
	
	.header-text h1 {
		font-size: 1.5rem;
	}
	
	.form-accordion {
		padding: 0 1rem;
	}
	
	.accordion-header {
		padding: 1.25rem;
		gap: 1rem;
	}
	
	.section-number {
		width: 40px;
		height: 40px;
		font-size: 1rem;
	}
	
	.section-title h3 {
		font-size: 1.1rem;
	}
	
	.section-description {
		font-size: 0.85rem;
	}
	
	.accordion-content {
		padding: 1.5rem;
	}
	
	.section-grid {
		grid-template-columns: 1fr;
	}
	
	.documents-list {
		grid-template-columns: 1fr;
	}
	
	.multiple-files-grid {
		grid-template-columns: 1fr;
	}
	
	.form-footer {
		padding: 1.5rem;
	}
	
	.footer-actions {
		flex-direction: column;
	}
	
	.footer-actions .btn {
		width: 100%;
		justify-content: center;
	}
}

@media (max-width: 576px) {
	.form-header {
		padding: 1.25rem;
	}
	
	.header-icon {
		width: 48px;
		height: 48px;
		font-size: 1.25rem;
	}
	
	.header-text h1 {
		font-size: 1.25rem;
	}
	
	.header-actions .btn {
		width: 100%;
		justify-content: center;
	}
	
	.accordion-header {
		flex-direction: column;
		align-items: flex-start;
		gap: 0.75rem;
	}
	
	.section-number {
		align-self: flex-start;
	}
	
	.section-toggle {
		position: absolute;
		right: 1.25rem;
		top: 1.25rem;
	}
	
	.input-with-icon input,
	.input-with-icon select,
	.input-with-icon textarea {
		padding: 0.75rem 1rem 0.75rem 2.5rem;
		font-size: 0.9rem;
	}
	
	.input-with-icon i {
		left: 0.875rem;
	}
	
	.file-upload-area {
		padding: 1.5rem;
	}
	
	.upload-area.multiple {
		padding: 1.25rem;
	}
}
</style>
@endsection

@section('other-js')
<script>
document.addEventListener('DOMContentLoaded', function() {
	const form = document.getElementById('candidatureForm');
	const accordionSections = document.querySelectorAll('.accordion-section');
	const submitBtn = document.querySelector('.btn-submit');
	
	// Initialize all sections as expanded
	accordionSections.forEach(section => {
		section.classList.add('active');
	});
	
	// Toggle accordion sections
	accordionSections.forEach(section => {
		const header = section.querySelector('.accordion-header');
		
		header.addEventListener('click', function() {
			section.classList.toggle('active');
		});
	});
	
	// File upload handling
	document.querySelectorAll('.file-input').forEach(input => {
		const wrapper = input.closest('.file-upload-wrapper');
		const uploadArea = wrapper.querySelector('.file-upload-area');
		const preview = wrapper.querySelector('.file-preview');
		
		input.addEventListener('change', function(e) {
			if (this.files.length > 0) {
				wrapper.classList.add('has-file');
				const file = this.files[0];
				
				preview.innerHTML = `
					<div class="file-info">
						<div class="d-flex align-items-center gap-2">
							<i class="fas fa-file-pdf text-danger fs-4"></i>
							<div>
								<div class="fw-medium">${file.name}</div>
								<small class="text-muted">${formatFileSize(file.size)}</small>
							</div>
						</div>
						<button type="button" class="btn btn-sm btn-outline-danger remove-file">
							<i class="fas fa-times"></i>
						</button>
					</div>
				`;
				
				// Add remove file functionality
				preview.querySelector('.remove-file').addEventListener('click', function() {
					input.value = '';
					wrapper.classList.remove('has-file');
				});
			}
		});
		
		// Drag and drop
		['dragover', 'dragenter'].forEach(event => {
			uploadArea.addEventListener(event, function(e) {
				e.preventDefault();
				this.style.borderColor = 'var(--primary-color)';
				this.style.background = 'var(--primary-light)';
			});
		});
		
		['dragleave', 'dragend', 'drop'].forEach(event => {
			uploadArea.addEventListener(event, function(e) {
				e.preventDefault();
				this.style.borderColor = '';
				this.style.background = '';
			});
		});
	});
	
	// Multiple file upload handling
	document.querySelectorAll('.multiple-file-input').forEach(input => {
		const wrapper = input.closest('.multiple-file-upload');
		const uploadArea = wrapper.querySelector('.upload-area.multiple');
		const countText = wrapper.querySelector('.count-text');
		
		input.addEventListener('change', function(e) {
			if (this.files.length > 0) {
				wrapper.classList.add('has-files');
				countText.textContent = `${this.files.length} fichier(s) sélectionné(s)`;
			} else {
				wrapper.classList.remove('has-files');
				countText.textContent = '0 fichier(s) sélectionné(s)';
			}
		});
		
		// Drag and drop
		['dragover', 'dragenter'].forEach(event => {
			uploadArea.addEventListener(event, function(e) {
				e.preventDefault();
				this.style.borderColor = 'var(--primary-color)';
				this.style.background = 'var(--primary-light)';
			});
		});
		
		['dragleave', 'dragend', 'drop'].forEach(event => {
			uploadArea.addEventListener(event, function(e) {
				e.preventDefault();
				this.style.borderColor = '';
				this.style.background = '';
			});
		});
	});
	
	// Form validation
	function validateForm() {
		let isValid = true;
		const requiredInputs = form.querySelectorAll('[required]');
		
		requiredInputs.forEach(input => {
			if (!input.value.trim()) {
				isValid = false;
				showError(input, 'Ce champ est obligatoire');
			} else {
				clearError(input);
				
				// Email validation
				if (input.type === 'email') {
					const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
					if (!emailRegex.test(input.value)) {
						isValid = false;
						showError(input, 'Email invalide');
					}
				}
				
				// Date validation
				if (input.type === 'date') {
					const selectedDate = new Date(input.value);
					const today = new Date();
					if (selectedDate > today) {
						isValid = false;
						showError(input, 'La date ne peut pas être dans le futur');
					}
				}
			}
		});
		
		// Validate required files
		const requiredFiles = form.querySelectorAll('.file-input[required]');
		requiredFiles.forEach(fileInput => {
			if (!fileInput.files || fileInput.files.length === 0) {
				isValid = false;
				const label = fileInput.closest('.document-card').querySelector('.document-label');
				showError(fileInput, 'Ce fichier est obligatoire');
			}
		});
		
		// Validate confirmation checkbox
		const confirmCheckbox = document.getElementById('confirmTerms');
		if (!confirmCheckbox.checked) {
			isValid = false;
			showError(confirmCheckbox, 'Veuillez accepter les conditions');
		}
		
		return isValid;
	}
	
	// Show error
	function showError(element, message) {
		const inputGroup = element.closest('.form-group') || element.closest('.document-card') || element.closest('.form-check');
		if (inputGroup) {
			// Remove existing error
			const existingError = inputGroup.querySelector('.error-message');
			if (existingError) existingError.remove();
			
			// Add error message
			const errorDiv = document.createElement('div');
			errorDiv.className = 'error-message';
			errorDiv.style.color = 'var(--danger-color)';
			errorDiv.style.fontSize = '0.85rem';
			errorDiv.style.marginTop = '0.25rem';
			errorDiv.textContent = message;
			
			if (element.type === 'checkbox') {
				inputGroup.appendChild(errorDiv);
			} else {
				const inputWrapper = element.closest('.input-with-icon') || element.closest('.file-upload-wrapper');
				if (inputWrapper) {
					inputWrapper.parentNode.appendChild(errorDiv);
				}
			}
			
			// Scroll to error
			if (!element.type === 'checkbox') {
				element.scrollIntoView({ behavior: 'smooth', block: 'center' });
			}
			
			// Highlight section
			const section = inputGroup.closest('.accordion-section');
			if (section) {
				section.classList.add('active');
				section.style.borderColor = 'var(--danger-color)';
				setTimeout(() => {
					section.style.borderColor = '';
				}, 2000);
			}
		}
	}
	
	// Clear error
	function clearError(element) {
		const inputGroup = element.closest('.form-group') || element.closest('.document-card') || element.closest('.form-check');
		if (inputGroup) {
			const errorDiv = inputGroup.querySelector('.error-message');
			if (errorDiv) errorDiv.remove();
		}
	}
	
	// Real-time validation
	form.addEventListener('input', function(e) {
		if (e.target.hasAttribute('required')) {
			if (e.target.value.trim()) {
				clearError(e.target);
			}
		}
	});
	
	form.addEventListener('change', function(e) {
		if (e.target.type === 'checkbox') {
			if (e.target.checked) {
				clearError(e.target);
			}
		}
		if (e.target.type === 'file') {
			if (e.target.files.length > 0) {
				clearError(e.target);
			}
		}
	});
	
	// Helper functions
	function formatFileSize(bytes) {
		if (bytes === 0) return '0 Bytes';
		const k = 1024;
		const sizes = ['Bytes', 'KB', 'MB', 'GB'];
		const i = Math.floor(Math.log(bytes) / Math.log(k));
		return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
	}
	
	// Form submission
	form.addEventListener('submit', function(e) {
		e.preventDefault();
		
		if (validateForm()) {
			// Confirmation dialog
			Swal.fire({
				title: 'Créer la candidature ?',
				html: `
					<div style="text-align: left;">
						<p>Êtes-vous sûr de vouloir créer cette candidature ?</p>
						<div style="background: #e3f2fd; padding: 1rem; border-radius: 8px; margin: 1rem 0;">
							<i class="fas fa-info-circle" style="color: #2196f3;"></i>
							<span style="margin-left: 0.5rem; color: #1565c0;">
								Un email de confirmation avec les identifiants sera envoyé au candidat.
							</span>
						</div>
					</div>
				`,
				icon: 'question',
				showCancelButton: true,
				confirmButtonText: 'Oui, créer',
				cancelButtonText: 'Annuler',
				confirmButtonColor: 'var(--primary-color)',
				cancelButtonColor: 'var(--secondary-color)',
				width: 500
			}).then((result) => {
				if (result.isConfirmed) {
					// Show loading state
					submitBtn.classList.add('loading');
					submitBtn.disabled = true;
					
					// Submit form
					setTimeout(() => {
						form.submit();
					}, 1000);
				}
			});
		} else {
			Swal.fire({
				icon: 'error',
				title: 'Formulaire incomplet',
				text: 'Veuillez remplir tous les champs obligatoires',
				confirmButtonColor: 'var(--danger-color)'
			});
		}
	});
	
	// Auto-expand sections with errors on page load (for old values)
	document.querySelectorAll('.input-with-icon input:invalid, .input-with-icon select:invalid').forEach(input => {
		const section = input.closest('.accordion-section');
		if (section) {
			section.classList.add('active');
		}
	});
});
</script>
@endsection