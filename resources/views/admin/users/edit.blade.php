@extends('base', [
	'title' => 'Modifier un compte',
	'page_name' => 'Modifier un compte',
	'breadcrumbs' => [
		'Administration',['text' => 'Membres de l\'administration', 'url' => route('admin.users.index')], $user->nom .' '. $user->prenom]
])

@section('content')
	<div class="card">
		<div class="card-body">
			<div class="row">
				<div class="col-12">
					@include('admin.users._form', [
						'action' => route('admin.users.update', [$user]),
						'edit' => 'ok'
					])
				</div>
			</div>
		</div>
	</div>
@endsection
