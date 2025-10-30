@extends('base', [
	'title' => 'Modifier une période',
	'page_name' => 'Modifier une période',
	'breadcrumbs' => ['Administration', ['url' => route('admin.periodes.index'), 'text' => 'Périodes'], $periode->nom]
])

@section('content')
	<div class="card">
		<div class="card-body">
			<div class="row">
				<div class="col-12">
					@include('admin.periodes._form', [
						'action' => route('admin.periodes.update', [$periode]),
						'edit' => 'ok'
					])
				</div>
			</div>
		</div>
	</div>
@endsection