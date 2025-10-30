@extends('base', [
	'title' => 'Ajouter une unité de valeur',
	'page_name' => 'Ajouter une unité de valeur',
	'breadcrumbs' => ['Administration', ['url' => route('admin.uvs.index'), 'text' => 'Unité de valeur'], 'Ajouter une unité de valeur']
])

@section('content')
	<div class="card">
		<div class="card-body">
			<div class="row">
				<div class="col-12">
					@include('admin.uvs._form', [
					'action' => route('admin.uvs.store')
				])
				</div>
			</div>
		</div>
@endsection
