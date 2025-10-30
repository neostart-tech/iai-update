@extends('base', [
	'title' => 'Ajouter un évènement',
	'page_name' => 'Ajouter un évènement',
	'breadcrumbs' => [
		[
			'text' => 'Évènements',
			'url' => route('admin.events.index')
		],
		'Ajouter un évènements'
	]
])

@section('content')
	@include('admin.events._form', [
	'route' => route('admin.events.store')
])
@endsection
