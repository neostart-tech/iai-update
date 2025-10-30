@extends('base', [
	'title' => 'Ajouter une filière',
	'page_name' => 'Ajouter une filière',
	'breadcrumbs' => ['Administration', 'Filières', 'Ajouter une filière']
])

@section('content')
	<div class="card">
		<div class="card-body">
			<div class="row">
				<div class="col-12">
					@include('admin.filieres._form', [
					'action' => route('admin.filieres.store')
				])
				</div>
			</div>
		</div>
@endsection
