@extends('base', [
	'title' => $title ?? 'Liste des candidatures rejetées',
	'page_name' => $page_name ?? 'Liste des candidatures rejetées',
	'breadcrumbs' => $breadcrumbs ?? ['Administration', ['url' => route('admin.candidatures.index'), 'text' => 'Candidatures'], 'Liste des candidatures rejetées']
])

@section('content')
	<div class="card">
		<div class="card-body">
			@if($candidatures->isNotEmpty())
				<div class="dt-responsive table-responsive">
					<table id="dom-jquery" class="table table-hover">
						<thead>
						<tr>
							<th scope="col">#</th>
							<th scope="col">Nom</th>
							<th scope="col">Prénoms</th>
							<th scope="col" class="text-center">Actions</th>
						</tr>
						</thead>
						<tbody>
						@foreach($candidatures as $key => $candidature)
							<tr>
								<th scope="row">{{ $key+=1 }}</th>
								<td>{{ $candidature->nom  }}</td>
								<td>{{ $candidature->prenom  }}</td>
								<td class="text-center">
									<ul class="list-inline me-auto mb-0">
										<li class="list-inline-item align-bottom" data-bs-toggle="tooltip" title="Voir le motif">
											<a href="{{ route('admin.candidatures.show', [$candidature]) }}"
												 class="avtar avtar-xs btn-link-secondary btn-pc-default">
												<i class="ti ti-eye f-18"></i>
											</a>
										</li>
										<li class="list-inline-item align-bottom" data-bs-toggle="tooltip" title="Editer">
											<a href="{{ route('admin.candidatures.show', [$candidature]) }}"
												 class="avtar avtar-xs btn-link-success btn-pc-default">
												<i class="ti ti-edit-circle f-18"></i>
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
							<th scope="col">Prénoms</th>
							<th scope="col" class="text-center">Actions</th>
						</tr>
						</tfoot>
					</table>
				</div>
			@else
				<div class="alert alert-danger">
					<div class="media align-items-center">
						<i class="ti ti-info-circle h2 f-w-400 mb-0"></i>
						<div class="media-body ms-3"> Aucune candidature a afficher dans cette section</div>
					</div>
				</div>
			@endif
		</div>
	</div>

	{{-- Aperçu du motif --}}
	<div class="modal fade modal-animate" id="validationModal" tabindex="-1" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Validation de la candidature</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<label for="motif-textarea">Motif</label>
					<textarea id="motif-textarea"></textarea>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary">OK</button>
				</div>
			</div>
		</div>
	</div>

	@include('layouts.admin._dt-scripts')
@endsection

@section('other-css')
	<link rel="stylesheet" href="{{ asset('admin/assets/css/plugins/dataTables.bootstrap5.min.css') }}">
@endsection
