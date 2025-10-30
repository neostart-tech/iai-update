@extends('base', [
	'title' => 'Modifier une filière',
	'page_name' => 'Modifier une filière',
	'breadcrumbs' => ['Administration', 'Filières', $filiere->nom]
])

@section('content')
	<div class="card">
		<div class="card-body">
			<div class="row">
				<div class="col-12">
					@include('admin.filieres._form', [
						'action' => route('admin.filieres.update', [$filiere]),
						'edit' => 'ok'
					])
				</div>
			</div>
		</div>
	</div>
@endsection