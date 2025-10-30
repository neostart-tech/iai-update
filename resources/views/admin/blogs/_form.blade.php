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
					<x-forms.label for="title" content="Titre de la publication"/>
					<input class="form-control" type="text" id="title" name="title"
								 value="{{ old('title', $blog->title) }}">
					{!! errorAlert($errors->first('title'), 'title') !!}
				</div>

				<div class="form-group">
					<x-forms.label for="author_name" content="Auteur de la publication"/>
					<input class="form-control" type="text" id="author_name" name="author_name"
								 value="{{ old('author_name', $blog->author_name) }}" placeholder="Ex: Service Communication">
					{!! errorAlert($errors->first('author_name'), 'author_name') !!}
				</div>

				<div class="form-group">
					<x-forms.label for="file_path" content="Image du blog"/>
					<input class="form-control" type="file" id="image" accept=".jpg,.png" name="image"
								 value="{{ old('image', $blog->image) }}">
					{!! errorAlert($errors->first('image'), 'image') !!}
				</div>

				<div class="form-group">
					<x-forms.label for="details" content="Contenu de la publication"/>
					<textarea id="content" name="content" class="tox-target">
						{{ old('content', $blog->content) }}
					</textarea>
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
			document.getElementById('title').setAttribute('value', event.target.value);
		});
	</script>

	<script>
		// tinymce.init({
		// 	height: '400',
		// 	selector: '#content',
		// 	content_style: 'body { font-family: "Inter", sans-serif; }',
		// 	toolbar: 'advlist | autolink | link image | lists charmap | print preview',
		// 	plugins: 'advlist autolink link image lists charmap print preview'
		// });
		tinymce.init({
			selector: '#content',
			height: '400',
			content_style: 'body { font-family: "Inter", sans-serif; }',
			plugins: 'advlist autolink link image lists charmap print preview',

		});
	</script>
@endsection
