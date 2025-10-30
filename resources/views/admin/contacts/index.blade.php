@extends('base',[
	'title' => 'Liste des Messages',
	'page_name' => 'Liste des Messages',
	'breadcrumbs' => [
		'Messages',
		'Liste'
	]
])

@section('content')
	<div class="card">
		<div class="card-body">
			@if($contacts->isNotEmpty())
				<div class="dt-responsive table-responsive">
					<table id="dom-jquery" class="table table-hover">
						<thead>
						<tr>
							<th scope="col">#</th>
							<th scope="col">Auteur</th>
							<th scope="col">Date de réception</th>
							<th scope="col">Statut</th>
							<th scope="col" class="text-center">Action</th>
						</tr>
						</thead>
						<tbody>
						@foreach($contacts as $key => $contact)
							<tr>
								<th scope="row">{{ $key+=1 }}</th>
								<td>{{ $contact->nom }}</td>
								<td>{{ $contact->created_at->translatedFormat('d F Y') }}</td>
								<td>{{ $contact->status ? 'Lu' : 'Non lu'}}</td>
								<td class="text-center">
									<ul class="list-inline me-auto mb-0">
										<li class="list-inline-item align-bottom" data-bs-toggle="tooltip" title="Détails">
											<a href="#" onclick="handleShowModal(@js($contact))"
												 class="avtar avtar-xs btn-link-secondary btn-pc-default"
												 data-pc-animate="blur" data-bs-toggle="modal"
												 data-bs-target="#contactShowModal"
											>
												<i class="fa fa-eye f-18"></i>
											</a>
										</li>

										
										< li class="list-inline-item align-bottom" data-bs-toggle="tooltip" title="Marquer comme lu" >
											<a href="#"
										      
												 onclick="handleReadClick('{{ route('admin.messages.read', $contact) }}')"
												 class="avtar avtar-xs btn-link-secondary btn-pc-default"
											>
												<i class="material-icons-two-tone"> check_box</i>
											</a>
										</>

										<li class="list-inline-item align-bottom" data-bs-toggle="tooltip" title="Détails">
											<a href="#"
												 onclick="showDeleteAlert(
													 '{{ route('admin.messages.delete', $contact) }}',
													 'Voulez-vous supprimer ce message ?',
													 null,
													 'Cette action supprimera ce message',
													 );"
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
							<th scope="col">Status</th>
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

	@include('admin.contacts._show')
@endsection

@section('other-js')
	@include('layouts.admin._dt-scripts')

	<script>
		function handleShowModal(contact) {
			document.getElementById('nom').value = contact.nom;
			document.getElementById('email').value = contact.email;
			document.getElementById('tel').value = contact.tel;
			document.getElementById('message').innerHTML = contact.message;
			document.getElementById('date').value = contact.createdAt;
			document.getElementById('status').value = contact.status ? 'Lu' : 'Non lu';
		}

		function handleReadClick(url) {
			showDeleteAlert(
				'{{ route('admin.messages.read', $contact) }}',
				'Voulez-vous supprimer cet message ?',
				() => document.location.href = url,
				null,
				'Oui, marquer comme lu'
			)
		}
	</script>
@endsection

@section('other-css')
	<link rel="stylesheet" href="{{ asset('admin/assets/css/plugins/dataTables.bootstrap5.min.css') }}">
@endsection
