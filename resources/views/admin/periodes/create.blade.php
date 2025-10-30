@extends('base', [
	'title' => 'Ajouter une période',
	'page_name' => 'Ajouter une période',
	'breadcrumbs' => ['Administration', ['url' => route('admin.periodes.index'), 'text' =>'Périodes'], 'Ajouter une période']
])

@section('content')
	<div class="card">
		<div class="card-body">
			<div class="row">
				<div class="col-12">
					@include('admin.periodes._form', [
					'action' => route('admin.periodes.store')
				])
				</div>
			</div>
		</div>
@endsection
