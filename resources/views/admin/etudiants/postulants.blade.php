@extends('base',[
	'title' => 'Liste des Postulants',
	'page_name' => 'Liste des Postulants',
	'breadcrumbs' => [
		[
			'text' => 'Annonces',
			'url' => route('admin.announcements.index')
		],
		[
			'text' => $announcement->title,
			'url' => route('admin.announcements.show', $announcement)
		],
		'Postulants'
	]
])

@section('content')
	<div class="card">
		<div class="card-body">
			@if($etudiants->isNotEmpty())
				<div class="dt-responsive table-responsive">
					<table id="dom-jquery" class="table table-hover">
						<thead>
						<tr>
							<th scope="col">#</th>
							<th scope="col">Nom & prénoms</th>
							<th scope="col">Matricule</th>
							<th scope="col">Date</th>
							<th scope="col" class="text-center">Action</th>
						</tr>
						</thead>
						<tbody>
						@foreach($etudiants as $key => $etudiant)
							<tr>
								<th scope="row">{{ $key+=1 }}</th>
								<td>{{ $etudiant->nom . ' ' . $etudiant->prenom }}</td>
								<td>{{ $etudiant->matricule }}</td>
								<td>{{ $etudiant->pivot->created_at->translatedFormat('d F Y') }}</td>
								<td class="text-center">
									<ul class="list-inline me-auto mb-0">
										<li class="list-inline-item align-bottom" data-bs-toggle="tooltip" title="Détails">
											<a href="{{ route('admin.etudiants.show', $etudiant) }}"
												 class="avtar avtar-xs btn-link-secondary btn-pc-default">
												<i data-feather="eye" class="f-18"></i>
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
							<th scope="col">Nom & prénoms</th>
							<th scope="col">Matricule</th>
							<th scope="col">Date</th>
							<th scope="col" class="text-center">Action</th>
						</tr>
						</tfoot>
					</table>
				</div>
			@else
				<x-empty-table content="Aucun étudiant n'a encore postulé à cette annonce"/>
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
