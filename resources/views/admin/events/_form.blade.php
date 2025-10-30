<form action="{{ $route }}" method="post" enctype="multipart/form-data">
	<div class="card">
		<div class="card-header text-end">
			<button type="submit" class="btn btn-primary">Soumettre</button>
		</div>
		<div class="card-body">
			@isset($method)
				@method('PUT')
			@endisset
			@csrf
			<div class="row">
				<div class="form-group">
					<x-forms.label for="nom" content="Titre de la publication"/>
					<input class="form-control" type="text" id="titnomle" name="nom"
								 value="{{ old('nom', $event->nom) }}">
					{!! errorAlert($errors->first('nom'), 'nom') !!}
				</div>

				<div class="form-group">
					<x-forms.label for="file_path" content="Date de début l'évènement"/>
					<input class="form-control" type="date" id="start_date" name="start_date"
								 value="{{ old('start_date', $event->start_date->format('Y-m-d')) }}">
					{!! errorAlert($errors->first('start_date'), 'start_date') !!}
				</div>

				<div class="form-group">
					<x-forms.label for="file_path" content="Date de fin de l'évènement" required="0"/>
					<input class="form-control" type="date" id="end_date" name="end_date"
								 value="{{ old('end_date', $event->end_date?->format('Y-m-d')) }}">
					{!! errorAlert($errors->first('end_date'), 'end_date') !!}
				</div>

				<div class="form-group">
					<x-forms.label for="details" content="Contenu de la publication"/>
					<textarea id="details" name="details" class="tox-target">
						{{ old('details', $event->details) }}
					</textarea>
				</div>
			</div>
		</div>
	</div>
</form>

@section('other-js')
	<script src="{{ asset('admin/assets/js/plugins/tinymce/tinymce.min.js') }}"></script>
	<script>
		tinymce.init({
			selector: '#details',
			height: '400',
			content_style: 'body { font-family: "Inter", sans-serif; }',
			plugins: 'advlist autolink link end_date lists charmap print preview',

		});
	</script>
@endsection
