<form method="post" action="{{ $action }}" enctype="multipart/form-data">
	@csrf
	@isset($edit)
		@method('put')
	@endisset

	<div class="form-group">
		<label class="form-label" for="nom">Nom de la filière <x-forms.required-field/> </label>
		<input type="text" class="form-control" id="nom" name="nom" aria-describedby="nom"
					 placeholder="Nom de la filière" value="{{ old('nom', $filiere->nom) }}">
		{!! errorAlert($errors->first('nom'), 'nom') !!}
	</div>

	<div class="form-group">
		<label class="form-label" for="code">Code de la filière <x-forms.required-field/> </label>
		<input type="text" class="form-control" id="code" name="code" aria-describedby="code"
					 placeholder="Code de la filière" value="{{ old('code', $filiere->code) }}">
		{!! errorAlert($errors->first('code'), 'code') !!}
	</div>

	<div class="form-group">
		<label class="form-label" for="description">Description de la filière </label>
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
