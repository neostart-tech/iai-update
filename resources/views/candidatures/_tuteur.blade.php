<div class="tab-pane" id="auth-6" role="tabpanel" aria-labelledby="auth-tab-6">
	<div class="text-center">
		<h3 class="text-center mb-3">Identité du parent ou du tuteur</h3>
		<button class="btn btn-light" type="button" onclick="replicate();" style="background-color: #fdf296; width: 100%">Identique au responsable</button>
	</div>
	<div class="row my-4">
		<div class="col-12 col-md-6">
			<div class="form-group">
				<label class="form-label" for="nom_tuteur">Nom
					<x-forms.required-field/>
				</label>
				<input type="text" class="form-control" id="nom_tuteur" value="{{ old('nom_tuteur') }}" name="nom_tuteur" placeholder="Nom"/>
				{!! errorAlert($errors->first('nom_tuteur')) !!}
			</div>
		</div>

		<div class="col-12 col-md-6">
			<div class="form-group">
				<label class="form-label" for="prenom_tuteur">Prénom
					<x-forms.required-field/>
				</label>
				<input type="text" class="form-control" placeholder="Prénom" value="{{ old('prenom_tuteur') }}" id="prenom_tuteur" name="prenom_tuteur"/>
				{!! errorAlert($errors->first('prenom_tuteur')) !!}
			</div>
		</div>

		<div class="col-12 col-md-6">
			<div class="form-group">
				<label class="form-label" for="profession_tuteur">Profession
					<x-forms.required-field/>
				</label>
				<input type="text" class="form-control" placeholder="Profession" value="{{ old('profession_tuteur') }}" name="profession_tuteur"
							 id="profession_tuteur"/>
				{!! errorAlert($errors->first('profession_tuteur')) !!}
			</div>
		</div>

		<div class="col-12 col-md-6">
			<div class="form-group">
				<label class="form-label" for="employeur_tuteur">Nom de l'employeur
					<x-forms.required-field/>
				</label>
				<input type="text" class="form-control" placeholder="Nom de l'employeur" value="{{ old('employeur_tuteur') }}" name="employeur_tuteur"
							 id="employeur_tuteur"/>
				{!! errorAlert($errors->first('employeur_tuteur')) !!}
			</div>
		</div>

		<div class="col-12 col-md-6">
			<div class="form-group">
				<label class="form-label" for="email_tuteur">Email
					<x-forms.required-field/>
				</label>
				<input type="email" class="form-control" placeholder="Email" name="email_tuteur" value="{{ old('email_tuteur') }}" id="email_tuteur"/>
				{!! errorAlert($errors->first('email_tuteur')) !!}
			</div>
		</div>

		<div class="col-12 col-md-6">
			<div class="form-group">
				<label class="form-label" for="tel_tuteur">Téléphone
					<x-forms.required-field/>
				</label>
				<input type="text" class="form-control" placeholder="Téléphone" name="tel_tuteur" value="{{ old('tel_tuteur') }}" id="tel_tuteur"/>
				{!! errorAlert($errors->first('tel_tuteur')) !!}
			</div>
		</div>

		<div class="col-12 col-md-6">
			<div class="form-group">
				<label class="form-label" for="adresse_tuteur">Adresse
					<x-forms.required-field/>
				</label>
				<input type="text" class="form-control" placeholder="Adresse" name="adresse_tuteur" value="{{ old('adresse_tuteur') }}" id="adresse_tuteur"/>
				{!! errorAlert($errors->first('adresse_tuteur')) !!}
			</div>
		</div>

		<div class="col-12 col-md-6">
			<div class="form-group">
				<label class="form-label" for="fax_tuteur">Fax</label>
				<input type="text" class="form-control" placeholder="Fax" name="fax_tuteur" value="{{ old('fax_tuteur') }}" id="fax_tuteur"/>
				{!! errorAlert($errors->first('fax_tuteur')) !!}
			</div>
		</div>

		<div class="col-12 col-md-6">
			<div class="form-group">
				<label class="form-label" for="bp_tuteur">BP</label>
				<input type="text" class="form-control" placeholder="BP" name="bp_tuteur" value="{{ old('bp_tuteur') }}" id="bp_tuteur"/>
				{!! errorAlert($errors->first('bp_tuteur')) !!}
			</div>
		</div>

	</div>
	<div class="form-check my-3">
		<input class="form-check-input" type="checkbox" value="1" id="accept_cgu" name="accept_cgu" {{ old('accept_cgu') ? 'checked' : '' }}>
		<label class="form-check-label" for="accept_cgu">
			J'ai lu et j'accepte les conditions générales d'utilisation
			<x-forms.required-field/>
		</label>
		{!! errorAlert($errors->first('accept_cgu'), 'accept_cgu') !!}
	</div>
	<div class="row g-3">
		<div class="col-sm-6">
			<div class="d-grid">
				<button class="btn btn-outline-secondary" type="button" onClick="change_tab('#auth-5')">Retour</button>
			</div>
		</div>
		<div class="col-sm-6">
			<div class="d-grid">
				<button class="btn btn-light auth-conf" type="button" style="background-color: #166534; color: white">
					Soumettre
				</button>
			</div>
		</div>
	</div>
</div>