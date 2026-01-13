@extends('base', [
	'title' => 'Modifier un parcours',
	'page_name' => 'Modifier un parcours',
	'breadcrumbs' => ['Administration', 'Parcours', $filiere->nom]
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