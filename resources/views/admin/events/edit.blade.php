@extends('base', [
	'title' => 'Modifier une publication',
	'page_name' => 'Modifier une publication',
	'breadcrumbs' => [
		[
			'text' => 'Évènements',
			'url' => route('admin.events.index')
		],
		$event->nom,
		'Modifier'
	]
])

@section('content')
	@include('admin.events._form', [
	'route' => route('admin.events.update', $event),
	'method' => 'ok'
])
@endsection
