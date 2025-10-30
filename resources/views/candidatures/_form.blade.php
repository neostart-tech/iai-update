<form method="post" action="{{ $action }}" enctype="multipart/form-data">
	@csrf
	@isset($edit)
		@method('put')
	@endisset

	<div class="form-group">
		<label class="form-label" for="nom">Nom de la période
			<x-forms.required-field/>
		</label>
		<input type="text" class="form-control" id="nom" name="nom" aria-describedby="nom"
					 placeholder="Nom de la période" value="{{ old('nom', $periode->nom) }}">
		{!! errorAlert($errors->first('nom'), 'nom') !!}
	</div>

	<div class="form-group">
		<label class="form-label" for="description">Description de la période
			<x-forms.required-field/>
		</label>
		<textarea class="form-control" id="description" name="description"
							rows="3">{{ old('description', $periode->description) }}</textarea>
		{!! errorAlert($errors->first('description')) !!}
	</div>

	<div class="row">
		<div class="form-group col-12 col-md-6">
			<label class="form-label" for="debut">Date de début</label>
			<input class="form-control" type="date"
						 value="{{ old('debut', $periode->debut ? $periode->debut->format('Y-m-d') : null) }}"
						 onchange="console.log(this.value)" name="debut" id="debut">
			{!! errorAlert($errors->first('debut')) !!}
		</div>

		<div class="form-group col-12 col-md-6">
			<label class="form-label" for="fin">Date de fin</label>
			<input class="form-control" type="date"
						 value="{{ old('fin', $periode->fin ? $periode->fin->format('Y-m-d') : null) }}" name="fin"
						 id="fin">
			{!! errorAlert($errors->first('fin')) !!}
		</div>
	</div>

	<button type="submit" class="btn btn-primary mb-4">Soumettre</button>
</form>
