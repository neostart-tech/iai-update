@extends('base', [
	'title' => 'Ajouter un partenaire',
	'page_name' => 'Ajouter un partenaire',
	'breadcrumbs' => [
		'Partenaires',
		'Ajouter un partenaire'
	]
])

@section('content')
	@include('advertisers._form', [
	'route' => route('admin.advertisers.store')
])
@endsection
