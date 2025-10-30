<form method="post" action="{{ $action }}" enctype="multipart/form-data">
	@csrf
	@isset($edit)
		@method('put')
	@endisset

	<div class="form-group">
		<label class="form-label" for="nom">Nom de l'UE
			<x-forms.required-field/>
		</label>
		<input type="text" class="form-control" id="nom" name="nom" aria-describedby="nom"
					 placeholder="Nom de l'UE" value="{{ old('nom', $ue->nom) }}">
		{!! errorAlert($errors->first('nom'), 'nom') !!}
	</div>

	<div class="form-group">
		<label class="form-label" for="code">Code de l'UE
			<x-forms.required-field/>
		</label>
		<input type="text" class="form-control" id="code" name="code" aria-describedby="code"
					 placeholder="Code de l'UE" value="{{ old('code', $ue->code) }}">
		{!! errorAlert($errors->first('code'), 'code') !!}
	</div>

	<div class="form-group">
		<label class="form-label" for="periode_id">Période
			<x-forms.required-field/>
		</label>
		<select class="mb-3 form-select" data-trigger name="periode_id" id="periode_id">
			@foreach($periodes as $periode)
				<option value="{{ $periode->id }}" @selected(old('periode_id', $ue->periode_id) === $periode->id)>{{ $periode->nom . ' - ' . $periode->anneeScolaire->nom }}</option>
			@endforeach
		</select>
		{!! errorAlert($errors->first('periode_id'), 'periode_id') !!}
	</div>

	<div class="form-group">
		<label class="form-label" for="filiere_id">Filière
			<x-forms.required-field/>
		</label>
		<select class="mb-3 form-select" data-trigger name="filiere_id" id="filiere_id">
			@foreach($filieres as $filiere)
				<option value="{{ $filiere->id }}" @selected(old('filiere_id', $ue->filiere_id) === $filiere->id)>{{ $filiere->nom }}</option>
			@endforeach
		</select>
		{!! errorAlert($errors->first('filiere_id'), 'filiere_id') !!}
	</div>


	<div class="form-group">
		<label class="form-label" for="credit">Nombre de crédits
			<x-forms.required-field/>
		</label>
		<input type="number" class="form-control" id="credit" name="credit" aria-describedby="credit"
					 placeholder="Nom de la filière" value="{{ old('credit', $ue->credit) }}">
		{!! errorAlert($errors->first('credit'), 'credit') !!}
	</div>

	<button type="submit" class="btn btn-primary mb-4">Soumettre</button>
</form>

@include('layouts._select-search-script')