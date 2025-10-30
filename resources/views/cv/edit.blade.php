@extends('base', [
	'title' => $text =  'Concevoir mon Cv',
	'page_name' => $text,
	'breadcrumbs' => [$text]
])

@section('content')
	<div class="card">
		<form action="{{ route('etudiants.cv.update') }}" method="post">
			@csrf @method('put')
			<div class="card-header text-end">
				<button type="submit" class="btn btn-primary">
					<i class="fa fa-save"></i>
					Enregistrer
				</button>
			</div>
			<div class="card-body">
			<textarea id="content" name="content" class="tox-target">{{ $content }}</textarea>
			</div>
		</form>
	</div>
@endsection

@section('other-js')
	<script src="{{ asset('admin/assets/js/plugins/tinymce/tinymce.min.js') }}"></script>
	<script>
		tinymce.init({
			height: '700',
			selector: '#content',
			content_style: 'body { font-family: "Inter", sans-serif; }',
			menubar: false,
			toolbar: [
				'styleselect fontselect fontsizeselect',
				'undo redo | cut copy paste | bold italic | link image | alignleft aligncenter alignright alignjustify',
				'bullist numlist | outdent indent | blockquote subscript superscript | advlist | autolink | lists charmap '
			],
			plugins: 'advlist autolink link image lists charmap print preview code'
		});
	</script>
@endsection
