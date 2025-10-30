@extends('base', [
	'title' => 'Modifier une filière',
	'page_name' => 'Modifier une filière',
	'breadcrumbs' => ['Administration', ['url' => route('admin.ues.index'), 'text' => 'UE'], $ue->nom]
])

@section('content')
	<div class="card">
		<div class="card-body">
			<div class="row">
				<div class="col-12">
					@include('admin.ues._form', [
						'action' => route('admin.ues.update', [$ue]),
						'edit' => 'ok'
					])
				</div>
			</div>
		</div>
	</div>
@endsection