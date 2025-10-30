@extends('base', [
	'title' => 'Modifier un partenaire',
	'page_name' => 'Modifier un partenaire',
	'breadcrumbs' => [
		[
			'text' => 'Partenaires',
			'url' => route('admin.advertisers.index')
		],
		$advertiser->nom,
		'Modifier'
	]
])

@section('content')
	@include('advertisers._form', [
	'route' => route('admin.advertisers.update', $advertiser),
	'method' => 'ok'
])
@endsection
