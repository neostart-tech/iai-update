<form method="post" action="{{ $action }}" enctype="multipart/form-data">
	@csrf
	@isset($edit)
	@method('put')
	@endisset


	<div class="form-group">
		<label class="form-label" for="nom">Nom du parcours <x-forms.required-field /> </label>
		<input type="text" class="form-control" id="nom" name="nom" aria-describedby="nom"
			placeholder="Nom du parcours" value="{{ old('nom', $filiere->nom) }}">
		{!! errorAlert($errors->first('nom'), 'nom') !!}
	</div>

	@if(AppGetters::getAfficherChoixDate())
	<div class="form-group">
		<label class="form-label" for="date_debut">
			Date début <x-forms.required-field />
		</label>

		<input
			type="date"
			class="form-control"
			id="date_debut"
			name="date_debut"
			value="{{ old('date_debut', optional($annee?->pivot)->date_debut) }}">

		{!! errorAlert($errors->first('date_debut'), 'date_debut') !!}
	</div>

	<div class="form-group">
		<label class="form-label" for="date_fin">
			Date fin <x-forms.required-field />
		</label>

		<input
			type="date"
			class="form-control"
			id="date_fin"
			name="date_fin"
			value="{{ old('date_fin', optional($annee?->pivot)->date_fin) }}">

		{!! errorAlert($errors->first('date_fin'), 'date_fin') !!}
	</div>
	@endif


	<div class="form-group">
		<label class="form-label" for="code">Code du parcours <x-forms.required-field /> </label>
		<input type="text" class="form-control" id="code" name="code" aria-describedby="code"
			placeholder="Code du parcours" value="{{ old('code', $filiere->code) }}">
		{!! errorAlert($errors->first('code'), 'code') !!}
	</div>

	<div class="form-group">
		<label class="form-label" for="description">Description du parcours </label>
		<textarea class="form-control" id="description" name="description"
			rows="3">{{ old('description', $filiere->description) }}</textarea>
		{!! errorAlert($errors->first('description')) !!}
	</div>

	<div class="form-group">
		<label for="image" class="form-label">Image d'illustration </label>
		<input class="form-control" type="file" id="image" name="image">
		{!! errorAlert($errors->first('image')) !!}
	</div>

	<button type="submit" class="btn btn-primary mb-4">Soumettre</button>
</form>