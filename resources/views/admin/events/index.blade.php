@extends('base',[
	'title' => 'Liste des évènements',
	'page_name' => 'Liste des évènements',
	'breadcrumbs' => [
		'Évènements',
		'Liste'
	]
])

@section('content')
	<div class="card">
		<div class="card-header text-end">
			<a href="{{ route('admin.events.create') }}" class="btn btn-primary">Ajouter un évènement</a>
		</div>
		<div class="card-body">
			@if($events->isNotEmpty())
				<div class="dt-responsive table-responsive">
					<table id="dom-jquery" class="table table-hover">
						<thead>
						<tr>
							<th scope="col">#</th>
							<th scope="col">Titre</th>
							<th scope="col">Date de publication</th>
							<th scope="col">Dates</th>
							<th scope="col" class="text-center">Action</th>
						</tr>
						</thead>
						<tbody>
						@foreach($events as $key => $event)
							<tr>
								<th scope="row">{{ $key+=1 }}</th>
								<td>{{ $event->nom }}</td>
								<td>{{ $event->createdAt }}</td>
								<td class="text-capitalize">
									@if($event->_end_date)
										{{ $event->_start_date }}
										- {{ $event->_end_date }}
									@else
										{{ $event->_start_date }}
									@endif
								</td>
								<td class="text-center">
									<ul class="list-inline me-auto mb-0">
										<li class="list-inline-item align-bottom" data-bs-toggle="tooltip" title="Détails">
											<a href="#" onclick="handleShowModal(@js($event))"
												 class="avtar avtar-xs btn-link-secondary btn-pc-default"
												 data-pc-animate="blur" data-bs-toggle="modal"
												 data-bs-target="#eventShowModal"
											>
												<i data-feather="eye" class="f-18"></i>
											</a>
										</li>

										<li class="list-inline-item align-bottom" data-bs-toggle="tooltip" title="Modifier">
											<a href="{{ route('admin.events.edit', $event) }}"
												 class="avtar avtar-xs btn-link-secondary btn-pc-default">
												<i class="fa fa-edit f-18"></i>
											</a>
										</li>

										<li class="list-inline-item align-bottom" data-bs-toggle="tooltip" title="Détails">
											<a href="#"
												 onclick="showDeleteAlert('{{ route('admin.events.destroy', $event) }}', 'Voulez-vous supprimer cet évènement ?')"
												 class="avtar avtar-xs btn-link-secondary btn-pc-default">
												<i class="fa fa-trash f-18"></i>
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
							<th scope="col">Titre</th>
							<th scope="col">Date de publication</th>
							<th scope="col">Dates</th>
							<th scope="col" class="text-center">Action</th>
						</tr>
						</tfoot>
					</table>
				</div>
			@else
				<x-empty-table/>
			@endif
		</div>
	</div>

	@include('admin.events._show')
@endsection

@section('other-js')
	@include('layouts.admin._dt-scripts')

	<script>

		function handleShowModal(event) {
			document.getElementById('nom').value = event.nom;
			document.getElementById('start_date').value = event._start_date;
			document.getElementById('end_date').value = event._end_date ?? event._start_date;
			document.getElementById('create-date').value = event.createdAt;
			document.getElementById('details').innerHTML = event.details;
		}
	</script>
@endsection

@section('other-css')
	<link rel="stylesheet" href="{{ asset('admin/assets/css/plugins/dataTables.bootstrap5.min.css') }}">
@endsection
