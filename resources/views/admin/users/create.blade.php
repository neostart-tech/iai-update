@extends('base', [
	'title' => 'Ajouter un utilisateur',
	'page_name' => 'Ajouter un utilisateur',
	'breadcrumbs' => ['Administration', ['url' => route('admin.users.index'), 'text' => 'Membres du personnel'], 'Ajouter un utilisateur']
])

@section('content')
	<div class="card">
		<div class="card-body">
			<div class="row">
				<div class="col-12">
					@include('admin.users._form', [
					'action' => route('admin.users.store')
				])
				</div>
			</div>
		</div>
@endsection
