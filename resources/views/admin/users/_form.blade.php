@php use App\Enums\GenreEnum; @endphp
<form method="post" action="{{ $action }}" enctype="multipart/form-data">
	@csrf
	@isset($edit)
		@method('put')
	@endisset
	<div class="form-group">
		<label class="form-label" for="nom">Nom
			<x-forms.required-field/>
		</label>
		<input type="text" class="form-control" id="nom" name="nom" aria-describedby="nom"
					 placeholder="Nom" value="{{ old('nom', $user->nom) }}">
		{!! errorAlert($errors->first('nom'), 'nom') !!}
	</div>

	<div class="form-group">
		<label class="form-label" for="prenom">Prénom.s
			<x-forms.required-field/>
		</label>
		<input type="text" class="form-control" id="prenom" name="prenom" aria-describedby="prenom"
					 placeholder="Prénom" value="{{ old('prenom', $user->prenom) }}">
		{!! errorAlert($errors->first('prenom'), 'prenom') !!}
	</div>

	<div class="form-group">
		<label class="form-label" for="email">Email
			<x-forms.required-field/>
		</label>
		<input type="text" class="form-control" id="email" name="email" aria-describedby="email"
					 placeholder="Adresse mail" value="{{ old('email', $user->email) }}">
		{!! errorAlert($errors->first('email'), 'email') !!}
	</div>

	<div class="form-group">
		<label class="form-label" for="tel">Numéro de téléphone
			<x-forms.required-field/>
		</label>
		<input type="text" class="form-control" id="tel" name="tel" aria-describedby="tel"
					 placeholder="Adresse mail" value="{{ old('tel', $user->tel) }}">
		{!! errorAlert($errors->first('tel'), 'tel') !!}
	</div>

	<div class="form-group">
		<label class="form-label" for="genre">Genre
			<x-forms.required-field/>
		</label>
		<select class="mb-3 form-select form-select-lg" name="genre" id="genre">
			@foreach(App\Enums\GenreEnum::values() as $genre)
				<option value="{{ $genre }}">{{ $genre }}</option>
			@endforeach
		</select>
		{!! errorAlert($errors->first('genre'), 'genre') !!}
	</div>

	<div class="form-group">
		<label class="form-label" for="supervisor_type">Type de Surveillant</label>
		<select class="mb-3 form-select form-select-lg" name="supervisor_type" id="supervisor_type">
			<option value="non_surveillant" @selected(old('supervisor_type', $user->supervisor_type ?? 'non_surveillant') === 'non_surveillant')>Non surveillant</option>
			<option value="interne" @selected(old('supervisor_type', $user->supervisor_type) === 'interne')>Surveillant Interne</option>
			<option value="externe" @selected(old('supervisor_type', $user->supervisor_type) === 'externe')>Surveillant Externe</option>
		</select>
		{!! errorAlert($errors->first('supervisor_type'), 'supervisor_type') !!}
	</div>

	<div class="form-group" id="supervisor-notes-group" style="{{ old('supervisor_type', $user->supervisor_type ?? 'non_surveillant') === 'non_surveillant' ? 'display: none;' : '' }}">
		<label class="form-label" for="supervisor_notes">Notes sur la surveillance</label>
		<textarea class="form-control" id="supervisor_notes" name="supervisor_notes" rows="3" placeholder="Notes concernant les aptitudes de surveillance, disponibilités, etc.">{{ old('supervisor_notes', $user->supervisor_notes) }}</textarea>
		{!! errorAlert($errors->first('supervisor_notes'), 'supervisor_notes') !!}
	</div>

	<div class="form-group">
		<label class="form-label" for="pc-tinymce-2">Biographie</label>
		<textarea id="pc-tinymce-2" name="biographie" class="tox-target">{{ old('biographie', $user->biographie) }}</textarea>
	</div>

	@if(auth()->user()->hasRoles(14))
		<div class="form-group">
			<label class="form-label" for="choices-multiple-default">Rôles
				<x-forms.required-field/>
			</label>
			<select class="form-control" data-trigger name="roles[]" id="choices-multiple-default" multiple>
				@foreach($roles as $role)
					<option
						value="{{ $role->id }}" @selected(collect(old('roles') ?? $user->roles->pluck('id'))->contains($role->id))>{{ $role->nom }}
					</option>
				@endforeach
			</select>
			{!! errorAlert($errors->first('roles'), 'roles') !!}
		</div>
	@endif


	{{--<div class="form-group">
		<label for="image" class="form-label">Image d'illustration </label>
		<input class="form-control" type="file" id="image" name="image">
		{!! errorAlert($errors->first('image')) !!}
	</div>--}}

	<button type="submit" class="btn btn-primary mb-4">Soumettre</button>
</form>

@section('other-js')

	<script src="{{ asset('admin/assets/js/plugins/tinymce/tinymce.min.js') }}"></script>

	<script>
		tinymce.init({
			selector: '#pc-tinymce-2',
			height: '400',
			content_style: 'body { font-family: "Inter", sans-serif; }'
		});

		// Gestion de l'affichage conditionnel des notes de surveillance
		document.getElementById('supervisor_type').addEventListener('change', function() {
			const notesGroup = document.getElementById('supervisor-notes-group');
			if (this.value === 'non_surveillant') {
				notesGroup.style.display = 'none';
			} else {
				notesGroup.style.display = 'block';
			}
		});
	</script>
	@include('layouts._select-search-script')
@endsection
