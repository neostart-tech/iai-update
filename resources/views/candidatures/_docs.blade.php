@php use App\Enums\TypeDiplomeEnum; @endphp
<div class="tab-pane" id="auth-4" role="tabpanel" aria-labelledby="auth-tab-4">
	<div class="text-center">
		<h3 class="text-center mb-3">Fournissez les pièces requises pour votre dossier</h3>
	</div>

	<hr/>

	<div class="row">
		<div class="col-12">
			<h5 class="mb-3">Bulletins scolaires (minimum 2 fichiers par niveau)</h5>
		</div>
		<div class="col-12 col-md-6">
			<div class="form-group">
				<label class="form-label" for="bulletins_seconde">Bulletins de Seconde
					<x-forms.required-field/>
				</label>
				<input class="form-control" type="file" id="bulletins_seconde" name="bulletins_seconde[]" multiple accept=".pdf,image/png,image/jpeg,image/jpg">
				<small class="text-muted">Téléchargez au moins 2 fichiers</small>
				{!! errorAlert($errors->first('bulletins_seconde'), 'bulletins_seconde') !!}
				{!! errorAlert($errors->first('bulletins_seconde.*'), 'bulletins_seconde') !!}
			</div>
		</div>
		<div class="col-12 col-md-6">
			<div class="form-group">
				<label class="form-label" for="bulletins_premiere">Bulletins de Première
					<x-forms.required-field/>
				</label>
				<input class="form-control" type="file" id="bulletins_premiere" name="bulletins_premiere[]" multiple accept=".pdf,image/png,image/jpeg,image/jpg">
				<small class="text-muted">Téléchargez au moins 2 fichiers</small>
				{!! errorAlert($errors->first('bulletins_premiere'), 'bulletins_premiere') !!}
				{!! errorAlert($errors->first('bulletins_premiere.*'), 'bulletins_premiere') !!}
			</div>
		</div>
		<div class="col-12 col-md-6">
			<div class="form-group">
				<label class="form-label" for="bulletins_terminale">Bulletins de Terminale
					<x-forms.required-field/>
				</label>
				<input class="form-control" type="file" id="bulletins_terminale" name="bulletins_terminale[]" multiple accept=".pdf,image/png,image/jpeg,image/jpg">
				<small class="text-muted">Téléchargez au moins 2 fichiers</small>
				{!! errorAlert($errors->first('bulletins_terminale'), 'bulletins_terminale') !!}
				{!! errorAlert($errors->first('bulletins_terminale.*'), 'bulletins_terminale') !!}
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-12 col-md-6">
			<div class="form-group">
				<label class="form-label" for="releve_bac1">Relevés BAC 1
					<x-forms.required-field/>
				</label>
				<input class="form-control" type="file" id="releve_bac1" name="releve_bac1[]" multiple accept=".pdf,image/png,image/jpeg,image/jpg">
				{!! errorAlert($errors->first('releve_bac1'), 'releve_bac1') !!}
				{!! errorAlert($errors->first('releve_bac1.*'), 'releve_bac1') !!}
			</div>
		</div>
		<div class="col-12 col-md-6">
			<div class="form-group">
				<label class="form-label" for="releve_bac2">Relevés BAC 2
					<x-forms.required-field/>
				</label>
				<input class="form-control" type="file" id="releve_bac2" name="releve_bac2[]" multiple accept=".pdf,image/png,image/jpeg,image/jpg">
				{!! errorAlert($errors->first('releve_bac2'), 'releve_bac2') !!}
				{!! errorAlert($errors->first('releve_bac2.*'), 'releve_bac2') !!}
			</div>
		</div>
	</div>
	<div class="row mb-4">

		<div class="col-12">
			<div class="form-group">
				<label for="lettre_file" class="form-label">Demande manuscrite
					<x-forms.required-field/>
				</label>
				<input class="form-control" type="file" accept=".pdf" id="lettre_file" name="lettre_file">
				{!! errorAlert($errors->first('lettre_file'), 'lettre_file') !!}
			</div>
		</div>

		{{--		<div class="col-12 col-md-2 mb-md-4 my-0 my-md-auto">--}}
		{{--			<button class="btn btn-primary"><i class="fa fa-eye"><span--}}
		{{--						class="text-sm-visible">&nbsp;Voir la demande manuscrite</span></i></button>--}}
		{{--		</div>--}}


		<div class="col-12">
			<div class="form-group">
				<label for="naissance_file" class="form-label">Copie légalisée de l'extrait de naissance
					<x-forms.required-field/>
				</label>
				<input class="form-control" type="file" accept=".pdf" id="naissance_file" name="naissance_file">
				{!! errorAlert($errors->first('naissance_file')) !!}
			</div>
		</div>

		{{--		<div class="col-12 col-md-2 mb-md-4 my-auto">--}}
		{{--			<button class="btn btn-primary"><i class="fa fa-eye"><span class="text-sm-visible">&nbsp;Afficher la copie légalisée de l'extrait de naissance</span></i>--}}
		{{--			</button>--}}
		{{--		</div>--}}

		<div class="col-12">
			<div class="form-group">
				<label for="nationalite_file" class="form-label">Copie légalisée du certificat de nationalité
					<x-forms.required-field/>
				</label>
				<input class="form-control" type="file" accept=".pdf" id="nationalite_file" name="nationalite_file">
				{!! errorAlert($errors->first('nationalite_file')) !!}
			</div>
		</div>

		{{--		<div class="col-12 col-md-2 mb-md-4 my-auto">--}}
		{{--			<button class="btn btn-primary"><i class="fa fa-eye"><span class="text-sm-visible">&nbsp;Afficher la copie légalisée du certificat de nationalité</span></i>--}}
		{{--			</button>--}}
		{{--		</div>--}}

		<div class="col-12">
			<div class="form-group">
				<label for="diplome_file" class="form-label">Copie légalisée du diplôme requis (BAC 2 / DUT)
					<x-forms.required-field/>
				</label>
				<input class="form-control" type="file" accept=".pdf" id="diplome_file" name="diplome_file">
				{!! errorAlert($errors->first('diplome_file')) !!}
			</div>
		</div>

		{{--		<div class="col-12 col-md-2 mb-md-4 my-auto">--}}
		{{--			<button class="btn btn-primary"><i class="fa fa-eye"><span class="text-sm-visible">&nbsp;Afficher la Copie légalisée du diplôme</span></i>--}}
		{{--			</button>--}}
		{{--		</div>--}}

		<div class="form-group mt-3">
			<label class="form-label" for="type_diplome">Type du Diplôme
				<x-forms.required-field/>
			</label>
			<select class="mb-3 form-select form-select-lg" name="type_diplome" id="type_diplome">
				@foreach( App\Enums\TypeDiplomeEnum::cases() as $type)
					<option value="{{ $type->value }}">{{ $type->value }}</option>
				@endforeach
			</select>
			{!! errorAlert($errors->first('type_diplome'), 'type_diplome') !!}
		</div>


		<div class="form-group mt-3">
			<label class="form-label" for="niveau_id"> Niveau d'études
				<x-forms.required-field/>
			</label>
			<select class="mb-3 form-select form-select-lg" name="niveau_id" id="niveau_id" data-trigger id="choices-single-default">
				@foreach($niveaux as $niveau)
					<option value="{{ $niveau->id }}" @selected(old('niveau_id') == $niveau->id)>{{ $niveau->libelle }}</option>
				@endforeach
			</select>
			{!! errorAlert($errors->first('niveau_id'), 'niveau_id') !!}

			<label class="form-label" for="filiere">Filiere
				<x-forms.required-field/>
			</label>
			<select class="mb-3 form-select form-select-lg" name="filiere_id" id="filiere_id" data-trigger id="choices-single-default">
				@foreach($filieres as $filiere)
					<option value="{{ $filiere->id }}" @selected(old('filiere_id') == $filiere->id)>{{ $filiere->nom }}</option>
				@endforeach
			</select>
			{!! errorAlert($errors->first('filiere_id'), 'filiere_id') !!}
		</div>
	


		<div class="col-12">
			<div class="form-group">
				<label for="photo_identite_file" class="form-label">Photo d'identité
					<x-forms.required-field/>
				</label>
				<input class="form-control" type="file" accept="image/png,image/jpeg,image/jpg" id="photo_identite_file"
							 name="photo_identite_file">
				{!! errorAlert($errors->first('photo_identite_file')) !!}
			</div>
		</div>

		{{--		<div class="col-12 col-md-2 mb-md-4 my-auto">--}}
		{{--			<button class="btn btn-primary"><i class="fa fa-eye"><span--}}
		{{--						class="text-sm-visible">&nbsp;Afficher la photo d'identité</span></i></button>--}}
		{{--		</div>--}}


		<div class="col-12">
			<div class="form-group">
				<label for="certificat_medical_file" class="form-label">Certificat médical
					<span class="text-muted">(optionnel)</span></label>
				&nbsp;
				<i class="fas fa-info-circle" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="top"
					 data-bs-content="Datant de moins de trois (3) mois"> </i>
				<input class="form-control" type="file" accept=".pdf" id="certificat_medical_file"
						 name="certificat_medical_file">
				{!! errorAlert($errors->first('certificat_medical_file')) !!}
			</div>
		</div>

		{{--		<div class="col-12 col-md-2 mb-md-4 my-auto">--}}
		{{--			<button class="btn btn-primary"><i class="fa fa-eye"><span--}}
		{{--						class="text-sm-visible">&nbsp; le certificat médical</span></i></button>--}}
		{{--		</div>--}}

		<div class="col-12">
			<div class="form-group">
				<label for="coupon_file" class="form-label">Coupon réponse</label> &nbsp;
					<i class="fas fa-info-circle" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="top"
						 data-bs-content="Pour les diplômes délivrés à l'étranger uniquement"> </i>
					{{--					<span class="text-danger italic">Pour les diplômes délivrés à l'étranger</span>--}}

				<input class="form-control" type="file" accept=".pdf" id="coupon_file" name="coupon_file">
				{!! errorAlert($errors->first('coupon_file')) !!}
			</div>
		</div>

		{{--		<div class="col-12 col-md-2 my-auto">--}}
		{{--			<button class="btn btn-primary"><i class="fa fa-eye"><span--}}
		{{--						class="text-sm-visible">&nbsp; le fichier chargé</span></i></button>--}}
		{{--		</div>--}}

	</div>
	<div class="row g-3">
		<div class="col-sm-6">
			<div class="d-grid">
				<button class="btn btn-outline-secondary" type="button" onClick="change_tab('#auth-3')">Retour</button>
			</div>
		</div>
		<div class="col-sm-6">
			<div class="d-grid">
				<button class="btn btn-light" type="button" style="background-color: #fdf296;" onClick="change_tab('#auth-5')">
					Poursuivre
				</button>
			</div>
		</div>
	</div>
</div>

{{--
<style>
	/* Customize button styles */
	.button {
		font-size: 16px;
		transition: opacity 0.3s ease;
	}

	.button:hover {
		opacity: 0.7;
	}

	/* Hide text on medium and larger screens */
	@media (min-width: 768px) {
		.text-sm-visible {
			display: none;
		}
	}
</style>--}}
