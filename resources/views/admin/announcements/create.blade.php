@extends('base', [
	'title' => 'Ajouter une opportunité',
	'page_name' => 'Ajouter une opportunité',
	'breadcrumbs' => [
		'Opportunités',
		'Ajouter une opportunité'
	]
])

@section('content')
	@include('admin.announcements._form', [
	'route' => route('admin.announcements.store')
])
@endsection
