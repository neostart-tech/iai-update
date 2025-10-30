@extends('base', [
	'title' => 'Modifier une unité de valeur',
	'page_name' => 'Modifier une unité de valeur',
	'breadcrumbs' => ['Administration', ['url' => route('admin.uvs.index'), 'text' => 'unité de valeur'], $uv->nom]
])

@section('content')
	<div class="card">
		<div class="card-body">
			<div class="row">
				<div class="col-12">
					@include('admin.uvs._form', [
					'action' => route('admin.uvs.update', $uv),
					'edit' => 'ok'
				])
				</div>
			</div>
		</div>
	</div>
@endsection
