@extends('base', [
	'title' => 'Ajouter une évaluation',
	'breadcrumbs' => ['Administration', ['text' => 'Évaluations', 'url' => route('admin.evaluations.index')], 'Ajouter une évaluation'],
	'page_name' => 'Ajouter une évaluation'
])

@section('content')
	@include('admin.evaluations._form', [
		'action' => route('admin.evaluations.store')
	])
@endsection

