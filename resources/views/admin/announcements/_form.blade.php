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
				<div class="form-group col-md-6">
					<x-forms.label for="advertiser" content="Annonceur"/>
					<select class="form-control" data-trigger name="advertiser_id" id="advertiser">
						@foreach($advertisers as $advertiser)
							<option value="{{ $advertiser->slug }}"
								@selected(old('contracts', $announcement->advertiser_id)=== $advertiser->slug)>
								{{ $advertiser->nom }}
							</option>
						@endforeach
					</select>
					{!! errorAlert($errors->first('type'), 'type') !!}
				</div>

				<div class="form-group col-md-6 col-lg-3">
					<x-forms.label for="type-annonce" content="Type d'opportunité"/>
					<select class="form-control" name="type_annonce" id="type-annonce">
						@foreach($typeAnnonces as $typeAnnonce)
							<option
								value="{{ $typeAnnonce->value }}" @selected(old('type_annonce', $announcement->type_annonce) === $typeAnnonce)>{{ $typeAnnonce->value }}</option>
						@endforeach
					</select>
					{!! errorAlert($errors->first('type_annonce'), 'type_annonce') !!}
				</div>

				<div class="form-group col-md-6 col-lg-3">
					<x-forms.label for="type-contrat" content="Type de contrat"/>
					<select class="form-control" name="type_contrat" id="type-contrat">
						@foreach($typeContrats as $typeContrat)
							<option
								value="{{ $typeContrat->value }}" @selected(old('type_contrat', $announcement->type_contrat) === $typeContrat)>{{ $typeContrat->value }}</option>
						@endforeach
					</select>
					{!! errorAlert($errors->first('type_contrat'), 'type_contrat') !!}
				</div>

				<div class="form-group col-md-6">
					<x-forms.label for="ville" content="Quartier, Ville, Pays" required="0"/>
					<input class="form-control" type="text" id="ville" name="ville" placeholder="Quartier, Ville, Pays"
								 value="{{ old('ville', $announcement->ville ?? $advertisers->first()?->ville) }}">
					{!! errorAlert($errors->first('ville'), 'ville') !!}
				</div>

				<div class="form-group col-md-6">
					<x-forms.label for="duration" content="Durée (6 mois / 90 jours)" required="0"/>
					<input class="form-control" type="text" id="duration" name="duration" placeholder="6 mois / 90 jours"
								 value="{{ old('duration', $announcement->duration) }}">
					{!! errorAlert($errors->first('ville'), 'ville') !!}
				</div>

				<div class="form-group">
					<x-forms.label for="title" content="Titre"/>
					<input class="form-control" type="text" id="title" name="title"
								 value="{{ old('title', $announcement->title) }}">
					{!! errorAlert($errors->first('title'), 'title') !!}
				</div>

				<div class="form-group">
					<x-forms.label for="file_path" content="Document officiel (en .PDF)" required="0"/>
					<input class="form-control" type="file" id="file_path" accept=".pdf" name="file_path"
								 value="{{ old('file_path', $announcement->file_path) }}">
					{!! errorAlert($errors->first('file_path'), 'file_path') !!}
				</div>

				<div class="form-group">
					<x-forms.label for="details" content="Contenu de l'annonce"/>
					<textarea id="content" name="content"
										class="tox-target">{{ old('content', $announcement->content) }}</textarea>
				</div>
			</div>
		</div>
	</div>
</form>

@section('other-js')
	<script src="{{ asset('admin/assets/js/plugins/tinymce/tinymce.min.js') }}"></script>
	@include('layouts._select-search-script')
	<script>
		document.getElementById('advertiser').addEventListener('change', event => {
			document.getElementById('ville').setAttribute('value', event.target.value);
		});
	</script>

	<script>
		tinymce.init({
			selector: '#content',
			height: '400',
			content_style: 'body { font-family: "Inter", sans-serif; }',
		});
	</script>
@endsection
