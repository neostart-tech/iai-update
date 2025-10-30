@php use App\Enums\GenreEnum; @endphp
<div class="tab-pane" id="auth-2" role="tabpanel" aria-labelledby="auth-tab-2">
	<div class="text-center">
		<h3 class="text-center mb-3">Renseignez votre identité</h3>
	</div>

	<div class="form-group">
		<label class="form-label" for="numero_table">Numéro de table
			<x-forms.required-field/>
		</label>
		<input type="text" class="form-control" name="numero_table" id="numero_table" placeholder="Numéro de table" value="{{ old('numero_table') }}"/>
		{!! errorAlert($errors->first('numero_table'), 'numero_table') !!}
	</div>

	<div class="row">
		<div class="col-12 col-md-6">
			<div class="form-group">
				<label class="form-label" for="annee_bac">Année du BAC
					<x-forms.required-field/>
				</label>
				<input type="number" class="form-control" name="annee_bac" id="annee_bac" placeholder="2023" value="{{ old('annee_bac') }}"/>
				{!! errorAlert($errors->first('annee_bac'), 'annee_bac') !!}
			</div>
		</div>
		<div class="col-12 col-md-6">
			<div class="form-group">
				<label class="form-label" for="serie">Série
					<x-forms.required-field/>
				</label>
				<select class="form-select" name="serie" id="serie">
					<option value="">-- Choisir --</option>
					<option value="C" @selected(old('serie')==='C')>C</option>
					<option value="D" @selected(old('serie')==='D')>D</option>
				</select>
				{!! errorAlert($errors->first('serie'), 'serie') !!}
			</div>
		</div>
	</div>

	<div class="form-group">
		<label class="form-label" for="lettre_motivation">Lettre de motivation
			<x-forms.required-field/>
		</label>
		<textarea class="form-control" name="lettre_motivation" id="lettre_motivation" rows="4" placeholder="Expliquez votre motivation">{{ old('lettre_motivation') }}</textarea>
		{!! errorAlert($errors->first('lettre_motivation'), 'lettre_motivation') !!}
	</div>
	<div class="form-group">
		<label class="form-label" for="nom">Nom
			<x-forms.required-field/>
		</label>
		<input type="text" class="form-control" id="nom" name="nom" placeholder="Nom" value="{{ old('nom') }}"/>
		{!! errorAlert($errors->first('nom'), 'nom') !!}
	</div>

	<div class="form-group">
		<label class="form-label" for="prenom">Prénoms
			<x-forms.required-field/>
		</label>
		<input type="text" class="form-control" name="prenom" id="prenom" placeholder="Prénoms"
					 value="{{ old('prenom') }}"/>
		{!! errorAlert($errors->first('prenom'), 'prenom') !!}
	</div>

	<div class="form-group">
		<label class="form-label" for="prenom">Nom de jeune fille
		</label>
		<i class="fas fa-info-circle" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="top"
			 data-bs-content="Uniquement si vous êtes mariée"> </i>
		<input type="text" class="form-control" name="nom_jeune_fille" id="nom_jeune_fille"
					 placeholder="Nom de jeune fille" value="{{ old('nom_jeune_fille') }}"/>
		{!! errorAlert($errors->first('nom_jeune_fille'), 'nom_jeune_fille') !!}
	</div>

	<div class="form-group">
		<label class="form-label" for="genre">Genre
			<x-forms.required-field/>
		</label>
		<select class="mb-3 form-select form-select-lg" name="genre" id="genre">
			@foreach(GenreEnum::cases() as $genre)
				<option value="{{ $genre->value }}" @selected(old('genre') === $genre->value)>{{ $genre->value }}</option>
			@endforeach
		</select>
		{!! errorAlert($errors->first('genre'), 'genre') !!}
	</div>

	<div class="form-group">
		<label class="form-label" for="date_naissance">Date de naissance
			<x-forms.required-field/>
		</label>
		<input type="date" class="form-control" name="date_naissance" id="date_naissance"
					 placeholder="Lieu de naissance" value="{{ old('date_naissance') }}"/>
		{!! errorAlert($errors->first('date_naissance'), 'date_naissance') !!}
	</div>


	<div class="form-group">
		<label class="form-label" for="lieu_naissance">Lieu de naissance
			<x-forms.required-field/>
		</label>
		<input type="text" class="form-control" name="lieu_naissance" id="lieu_naissance"
					 placeholder="Lieu de naissance" value="{{ old('lieu_naissance') }}"/>
		{!! errorAlert($errors->first('lieu_naissance'), 'lieu_naissance') !!}
	</div>

	<div class="row g-3">
		<div class="col-sm-6">
			<div class="d-grid">
				<button class="btn btn-outline-secondary" type="button" onClick="change_tab('#auth-1')">Retour</button>
			</div>
		</div>
		<div class="col-sm-6">
			<div class="d-grid">
				<button class="btn btn-light" type="button" style="background-color: #fdf296;" onClick="change_tab('#auth-3')">
					Poursuivre
				</button>
			</div>
		</div>
	</div>
</div>
<div class="tab-pane" id="auth-3" role="tabpanel" aria-labelledby="auth-tab-3">
	<div class="text-center">
		<h3 class="text-center mb-3">Renseignez votre identité</h3>
	</div>
	<div class="form-group">
		<x-forms.label for="tel" content="Téléphone 1"/>
		<input type="tel" style="max-width: 165%;" class="form-control phone-input" id="tel-input" name="tel" value="{{ old('tel') }}" placeholder=""/>
		{!! errorAlert($errors->first('tel'), 'tel') !!}
	</div>

	<div class="form-group">
		<label class="form-label" for="tel2">Téléphone 2</label>
		<input type="text" class="form-control" id="tel2" name="tel2" value="{{ old('tel2') }}" placeholder=""/>
		<small class="text-muted">Minimum deux numéros dont un obligatoire: fournissez le Téléphone 1 et au moins l’un de Téléphone 2 ou 3.</small>
		{!! errorAlert($errors->first('tel2'), 'tel2') !!}
	</div>

	<div class="form-group">
		<label class="form-label" for="tel3">Téléphone 3 (optionnel)</label>
		<input type="text" class="form-control" id="tel3" name="tel3" value="{{ old('tel3') }}" placeholder=""/>
		{!! errorAlert($errors->first('tel3'), 'tel3') !!}
	</div>

	<input type="hidden" name="indicatif" id="indicatif">

	<div class="form-group">
		<label class="form-label" for="email">Adresse mail (optionnelle)
		</label>
		<input type="email" class="form-control" name="email" value="{{ old('email') }}" id="email"
					 placeholder="mon.adresse@domain.com"/>
		{!! errorAlert($errors->first('email'), 'email') !!}
	</div>

	<div class="form-group">
		<label class="form-label" for="choices-single-default">Nationalité
			<x-forms.required-field/>
		</label>
		<select class="form-control" name="nationalite" data-trigger name="choices-single-default"
						id="choices-single-default">
			@foreach($countries as $code => $name)
				<option value="{{ $code }}" @selected(old('nationalite', 'TG') === $code)>{{ $name }}</option>
			@endforeach
		</select>
		{{--		<input type="text" class="form-control" name="nationalite" id="nationalite"--}}
		{{--					 placeholder="Lieu de naissance"/>--}}
		{!! errorAlert($errors->first('nationalite'), 'nationalite') !!}
	</div>

	<div class="form-group">

		<label class="form-label" for="hobbit">Centre d'intérêt
			<x-forms.required-field/>
		</label>
		<textarea class="form-control" name="hobbit" id="hobbit" rows="3">{{ old('hobbit') }}</textarea>
		{!! errorAlert($errors->first('hobbit'), 'hobbit') !!}
	</div>

	<div class="form-group">
		<label class="form-label" for="prenom">Boîte postale
		</label>
		<input type="text" class="form-control" name="bp" value="{{ old('bp') }}" id="bp"
					 placeholder="Boîte postale"/>
		{!! errorAlert($errors->first('bp'), 'bp') !!}
	</div>

	<div class="form-group">
		<label class="form-label" for="fax">Fax
		</label>
		<input type="text" class="form-control" name="fax" value="{{ old('fax') }}" id="fax"
					 placeholder="fax"/>
		{!! errorAlert($errors->first('fax'), 'fax') !!}
	</div>

	<div class="row g-3">
		<div class="col-sm-6">
			<div class="d-grid">
				<button class="btn btn-outline-secondary" type="button" onClick="change_tab('#auth-2')">Retour</button>
			</div>
		</div>
		<div class="col-sm-6">
			<div class="d-grid">
				<button class="btn btn-light" type="button" style="background-color: #fdf296;" onClick="change_tab('#auth-4')">
					Poursuivre
				</button>
			</div>
		</div>
	</div>
</div>

