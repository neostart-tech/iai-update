@extends('base', [
'title' => 'Ajouter un parcours',
'page_name' => 'Ajouter un parcours',
'breadcrumbs' => ['Administration', 'Parcours', 'Ajouter un parcours']
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