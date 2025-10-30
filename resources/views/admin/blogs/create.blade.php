@extends('base', [
	'title' => 'Ajouter une publication',
	'page_name' => 'Ajouter une publication',
	'breadcrumbs' => [
		'Publications',
		'Ajouter une publication'
	]
])

@section('content')
	@include('admin.blogs._form', [
	'route' => route('admin.blogs.store')
])
@endsection
