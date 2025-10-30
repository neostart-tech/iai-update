@extends('base', [
	'title' => 'Faire un contrôle de présence',
	'breadcrumbs' => $breadCrumbs,
	'page_name' => 'Faire un contrôle de présence'
])

@section('content')
	<div class="tab-pane" id="profile-3" role="tabpanel" aria-labelledby="profile-tab-3">
		<div class="row">
			<div class="col-12">
				@if($alreadySubmitted = $fiche->submitted)
					<x-empty-table content="Cette fiche de présence a déjà été soumise"/>
				@endif
				<div class="card">
					<div class="card-header text-end">
						<button class="btn btn-primary" {{-- onclick="manageListSubmission()" --}} onclick="document.getElementById('presence-form').submit();" id="submit-button">Soumettre</button>
					</div>
					<div class="card-body">
						<form action="{{ route('admin.fiches.submit', $fiche) }}" method="post" id="presence-form">
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
									@foreach($etudiants as $key => $etudiant)
										<tr>
											<td>{{ $key +=1 }}</td>
											<td>{{ $etudiant->nom }} </td>
											<td>{{ $etudiant->prenom }}</td>
											<td>{{ $etudiant->matricule }}</td>
											<td class="text-center">
												@if($alreadySubmitted)
													Présent
												@else
													<label for="{{ $etudiant->slug }}">Marquer comme absent </label>
													<input type="checkbox" @disabled($fiche->submitted) name="etudiants[]" class="absents"
																 id="{{ $etudiant->slug }}"
																 value="{{ $etudiant->slug }}">
												@endif
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
			</div>
		</div>
	</div>
@endsection

@section('other-css')
	<link rel="stylesheet" href="{{ asset('admin/assets/css/plugins/dataTables.bootstrap5.min.css') }}">
@endsection

@section('other-js')
	{{--<script>
		let absentsList = [];
		const token = '{{ csrf_token() }}'

		function manageListSubmission() {
			const absents = document.getElementsByClassName('absents');
			[...absents].forEach(absent => {
				if (absent.checked)
					absentsList.push(absent.value)
			});
			const length = absentsList.length;
			if (length === 0) {
				notification.success('Aucun étudiant sélectionné');
			} else {
				notification.success(absentsList.length + ' étudiants sélectionnés');
			}

			Swal.fire({
				title: 'Voulez-vous soumettre cette fiche de présence ?',
				text: 'Cette action sera irréversible',
				showDenyButton: true,
				confirmButtonText: `Oui, soumettre`,
				denyButtonText: `Annuler`
			}).then((result) => {
				if (result.isConfirmed) {
					swal.fire(
						'Enregistrement de la fiche de présence en cours',
						'Veuillez patienter et ne pas rafraichir la page'
					);
					$.post('{{ route('admin.fiches.submit', $fiche) }}', {
						absents: absentsList,
						_token: token
					}).then(() => {
						swal.fire('Fiche de présence soumise avec succès');
						document.location.navigate(document.location.href);
						// document.getElementById('submit-button').setAttribute('disabled', 'true');
						// [...absents].forEach(element => element.setAttribute('disabled', 'true'));
					}).catch(error => {
						notification.error('Une erreur est survenue lors de l\'enregistrement');
					});
				} else {
					absentsList = [];
				}
			});
		}
	</script>--}}
	@include('layouts.admin._dt-scripts')
@endsection