<form action="{{ $route }}" method="post">
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
				<div class="form-group col-md-6">
					<x-forms.label for="nom" content="Nom"/>
					<input class="form-control" type="text" id="nom" name="nom" value="{{ old('nom', $advertiser->nom) }}">
					{!! errorAlert($errors->first('nom'), 'nom') !!}
				</div>

				<div class="form-group col-md-6">
					<x-forms.label for="email" content="Email"/>
					<input class="form-control" type="email" id="email" name="email" value="{{ old('email', $advertiser->email) }}">
					{!! errorAlert($errors->first('email'), 'email') !!}
				</div>

				<div class="form-group col-md-6">
					<x-forms.label for="site" content="Site" required="0"/>
					<input class="form-control" type="url" id="site" name="site" value="{{ old('site', $advertiser->site) }}">
					{!! errorAlert($errors->first('site'), 'site') !!}
				</div>

				<div class="form-group col-md-6">
					<x-forms.label for="ville" content="Quartier, Ville, Pays "/>
					<input class="form-control" type="text" id="ville" placeholder="Quartier, Ville, Pays" name="ville" value="{{ old('ville', $advertiser->ville) }}">
					{!! errorAlert($errors->first('ville'), 'ville') !!}
				</div>

				<div class="form-group">
					<x-forms.label for="details" content="Biographie"/>
					<textarea id="details" name="details"
										class="tox-target">{{ old('details', $advertiser->details) }}</textarea>
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
		});
	</script>
@endsection
