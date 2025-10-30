@extends('base', [
	'title' => 'Modifier une opportunité',
	'page_name' => 'Modifier une opportunité',
	'breadcrumbs' => [
		[
			'text' => 'Opportunités',
			'url' => route('admin.announcements.index')
		],
		[
			'text' => $announcement->title,
			'url' => route('admin.announcements.show', $announcement)
	],
		'Modifier'
	]
])

@section('content')
	@include('admin.announcements._form', [
	'route' => route('admin.announcements.update', $announcement),
	'method' => 'ok'
])
@endsection
