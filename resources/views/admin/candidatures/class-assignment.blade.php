@extends('base', [
	'title' => 'Attribution de groupe',
	'breadcrumbs' => [
		'Administration',
		[
			'text' =>'Candidatures',
			'url' => route('admin.candidatures.index')
		], 
		'Attribution de groupe',
		[
			'text' => $groupName,
			'url' => route('admin.groups.index')
		],
		'Choix des candidats'
	],
	'page_name' => 'Attribution de groupes aux candidats'
])

{{-- {{dd($candidatures)}} --}}

@section('content')
	<div class="row">
		<div class="col-12">
			@if($candidatures->isNotEmpty())
				<x-empty-table content="Ne sélectionnez que les étudiants que vous voulez ajouter au groupe"/>
				<div class="card">
					<div class="card-header text-end gap-3">
						<button id="select-all" class="btn btn-primary">Tout sélectionner</button>
						<button id="reverse-selection" class="btn btn-primary">Inverser la sélection</button>
						<button type="button" class="btn btn-primary" id="class-button">Valider la sélection</button>
					</div>
					<div class="card-body">
						<form action="{{ route('admin.groups.store-attribution', $group) }}" method="post" id="class-form">
							<div class="dt-responsive table-responsive">
								<table id="dom-jquery" class="table table table-hover">
									<thead>
									<tr>
										<th>#</th>
										<th>Nom</th>
										<th>Prénoms</th>
										<th>Code</th>
										<th>Action</th>
									</tr>
									</thead>
									@csrf
									<tbody>
								
									@foreach($candidatures as $key => $candidat)
										<tr>
											<td>{{ $key +=1 }}</td>
											<td>{{ $candidat->nom }} </td>
											<td>{{ $candidat->prenom }}</td>
											<td>{{ $candidat->code }}</td>
											<td class="text-center">
												<x-forms.label for="{{ $candidat->slug }}" content="Intégrer au groupe " required="0"/>
												<input type="checkbox" class="form-check-input" name="candidats[]" id="{{ $candidat->slug }}"
															 value="{{ $candidat->slug }}">
											</td>
										</tr>
									@endforeach
									</tbody>
									<tfoot>
									<tr>
										<th>#</th>
										<th>Nom</th>
										<th>Prénoms</th>
										<th>Code</th>
										<th>Action</th>
									</tr>
									</tfoot>
								</table>
							</div>
						</form>
					</div>
				</div>
			@else
				<x-empty-table content="Aucune candidature a afficher dans cette section"/>
			@endif
		</div>
	</div>
@endsection

@section('other-css')
	<link rel="stylesheet" href="{{ asset('admin/assets/css/plugins/dataTables.bootstrap5.min.css') }}">
@endsection

@section('other-js')
	<script>
		const groupName = '{{ $groupName = $group->nom.	' - '. $group->filiere->code}}';
		const candidats = document.getElementsByName('candidats[]');

		document.getElementById('select-all').addEventListener('click', () => candidats.forEach(element => element.checked = true));

		document.getElementById('reverse-selection').addEventListener('click', () => candidats.forEach(element => element.checked = !element.checked));

		document.getElementById('class-button').addEventListener('click', () => {
			let count = 0;
			candidats.forEach(element => element.checked && count++);
			if (count <= 0)
				notification.error('La sélection ne peut être vide');
			else {
				const slot = count === 1 ? 'l\'étudiant.e' : `les ${count} étudiants`;
				Swal.fire({
					icon: 'info',
					showCancelButton: true,
					confirmButtonText: "Oui, confirmer",
					cancelButtonText: 'Annuler',
					html: `En cliquant sur oui, vous confirmez que ${slot} que vous avez choisi seront affectés au groupe ${groupName}`
				}).then(result => {
					result.isConfirmed && document.getElementById('class-form').submit();
				});
			}
		});
	</script>
	@include('layouts.admin._dt-scripts')
@endsection
