@extends('base', [
	'title' => 'Ajouter une UE',
	'page_name' => 'Ajouter une UE',
	'breadcrumbs' => ['Administration', ['url' => route('admin.ues.index'), 'text' => 'Unité d\'enseignement'], 'Ajouter une UE']
])

@section('content')
	<div class="card">
		<div class="card-body">
			<div class="row">
				<div class="col-12">
					@include('admin.ues._form', [
					'action' => route('admin.ues.store')
				])
				</div>
			</div>
		</div>
@endsection
