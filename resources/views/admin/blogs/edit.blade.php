@extends('base', [
	'title' => 'Modifier une publication',
	'page_name' => 'Modifier une publication',
	'breadcrumbs' => [
		[
			'text' => $blog->title,
			'url' => route('admin.blogs.show', $blog)
	],
		'Modifier'
	]
])

@section('content')
	@include('admin.blogs._form', [
	'route' => route('admin.blogs.update', $blog),
	'method' => 'ok'
])
@endsection
