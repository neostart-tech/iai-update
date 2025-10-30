<div class="tab-pane" id="auth-5" role="tabpanel" aria-labelledby="auth-tab-5">
	<div class="text-center">
		<h3 class="text-center mb-3">Personne responsable des frais de formation</h3>
	</div>
	<div class="row my-4">
		<div class="col-12 col-md-6">
			<div class="form-group">
				<label class="form-label" for="nom_resp">Nom <x-forms.required-field/></label>
				<input type="text" class="form-control" id="nom_resp" name="nom_resp" value="{{ old('nom_resp') }}" placeholder="Nom"/>
				{!! errorAlert($errors->first('nom_resp')) !!}
			</div>
		</div>

		<div class="col-12 col-md-6">
			<div class="form-group">
				<label class="form-label" for="prenom_resp">Prénom <x-forms.required-field/></label>
				<input type="text" class="form-control" placeholder="Prénom" id="prenom_resp" name="prenom_resp" value="{{ old('prenom_resp') }}"/>
				{!! errorAlert($errors->first('prenom_resp')) !!}
			</div>
		</div>

		<div class="col-12 col-md-6">
			<div class="form-group">
				<label class="form-label" for="profession_resp">Profession <x-forms.required-field/></label>
				<input type="text" class="form-control" placeholder="Profession" name="profession_resp" value="{{ old('profession_resp') }}" id="profession_resp"/>
				{!! errorAlert($errors->first('profession_resp')) !!}
			</div>
		</div>

		<div class="col-12 col-md-6">
			<div class="form-group">
				<label class="form-label" for="employeur_resp">Nom de l'employeur <x-forms.required-field/></label>
				<input type="text" class="form-control" placeholder="Nom de l'employeur" name="employeur_resp" value="{{ old('employeur_resp') }}" id="employeur_resp"/>
				{!! errorAlert($errors->first('employeur_resp')) !!}
			</div>
		</div>

		<div class="col-12 col-md-6">
			<div class="form-group">
				<label class="form-label" for="email_resp">Email <x-forms.required-field/></label>
				<input type="email" class="form-control" placeholder="Email" name="email_resp" value="{{ old('email_resp') }}" id="email_resp"/>
				{!! errorAlert($errors->first('email_resp')) !!}
			</div>
		</div>

		<div class="col-12 col-md-6">
			<div class="form-group">
				<label class="form-label" for="tel_resp">Téléphone <x-forms.required-field/></label>
				<input type="text" class="form-control" placeholder="Téléphone" name="tel_resp" value="{{ old('tel_resp') }}" id="tel_resp"/>
				{!! errorAlert($errors->first('tel_resp')) !!}
			</div>
		</div>

		<div class="col-12 col-md-6">
			<div class="form-group">
				<label class="form-label" for="adresse_resp">Adresse <x-forms.required-field/></label>
				<input type="text" class="form-control" placeholder="Adresse" name="adresse_resp" value="{{ old('adresse_resp') }}" id="adresse_resp"/>
				{!! errorAlert($errors->first('adresse_resp')) !!}
			</div>
		</div>

		<div class="col-12 col-md-6">
			<div class="form-group">
				<label class="form-label" for="fax_resp">Fax</label>
				<input type="text" class="form-control" placeholder="Fax" name="fax_resp" value="{{ old('fax_resp') }}" id="fax_resp"/>
				{!! errorAlert($errors->first('fax_resp')) !!}
			</div>
		</div>


		<div class="col-12 col-md-6">
			<div class="form-group">
				<label class="form-label" for="bp_resp">BP</label>
				<input type="text" class="form-control" placeholder="BP" name="bp_resp" value="{{ old('bp_resp') }}" id="bp_resp"/>
				{!! errorAlert($errors->first('bp_resp')) !!}
			</div>
		</div>

	</div>
	<div class="row g-3">
		<div class="col-sm-6">
			<div class="d-grid">
				<button class="btn btn-outline-secondary" type="button" onClick="change_tab('#auth-4')">Retour</button>
			</div>
		</div>
		<div class="col-sm-6">
			<div class="d-grid">
				<button class="btn btn-light" type="button" style="background-color: #fdf296;" onClick="change_tab('#auth-6')">
					Poursuivre
				</button>
			</div>
		</div>
	</div>
</div>