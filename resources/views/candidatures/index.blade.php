@extends("base", [
	'title' => 'Liste des périodes',
	'breadcrumbs' => ['Administration', ['url' => route('admin.candidatures.index'), 'text' =>'Candidatures'], 'Liste'],
	'page_name' => 'Liste des périodes'
])

@section('content')
	<div class="card">
		<div class="card-header text-end">
			<a href="{{ route('admin.candidatures.create') }}" class="btn btn-primary">
				<i class="ti ti-plus f-18"></i> Ajouter une période
			</a>
		</div>
		<div class="card-body">
			@if($candidatures->isNotEmpty())
				<div class="dt-responsive">
					<table id="dom-jquery" class="table table-hover">
						<thead>
						<tr>
							<th scope="col">#</th>
							<th scope="col">Nom</th>
							<th scope="col">Debut</th>
							<th scope="col">Fin</th>
							<th scope="col">Actions</th>
						</tr>
						</thead>
						<tbody>
						@foreach($candidatures as $key => $candidature)
							<tr>
								<th scope="row">{{ $key+=1 }}</th>
								<td>{{ $candidature->nom  }}</td>
								<td>{{ $candidature->debut->translatedFormat('d F Y') }}</td>
								<td>{{ $candidature->fin->translatedFormat('d F Y') }}</td>
								<td class="text-center">
									<ul class="list-inline me-auto mb-0">
										<li class="list-inline-item align-bottom" data-bs-toggle="tooltip" title="Modifier">
											<a href="{{ route('admin.candidatures.edit', [$candidature]) }}"
												 class="avtar avtar-xs btn-link-success btn-pc-default">
												<i class="ti ti-edit-circle f-18"></i>
											</a>
										</li>
										<li class="list-inline-item align-bottom" data-bs-toggle="tooltip" title="Supprimer">
											<a href="#" class="avtar avtar-xs btn-link-danger btn-pc-default">
												<i class="ti ti-trash f-18"></i>
											</a>
										</li>
									</ul>
								</td>
							</tr>
						@endforeach
						</tbody>
						<tfoot>
						<tr>
							<th scope="col">#</th>
							<th scope="col">Nom</th>
							<th scope="col">Code</th>
							<th scope="col">Description</th>
							<th scope="col">Actions</th>
						</tr>
						</tfoot>
					</table>
				</div>
			@else
				<x-empty-table/>
			@endif
		</div>
	</div>
@endsection

@section('other-js')
	@include('layouts.admin._dt-scripts')
@endsection

@section('other-css')
	<link rel="stylesheet" href="{{ asset('admin/assets/css/plugins/dataTables.bootstrap5.min.css') }}">
@endsection