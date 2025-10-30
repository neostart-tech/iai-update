@extends('base', [
	'title' => 'Attribution de groupe',
	'breadcrumbs' => [
		'Administration',
		[
			'text' =>'Groupes',
			'url' => route('admin.groups.index')
		],
		$group->getAttribute('nom') .' - '.$group->filiere->getAttribute('code'),
		'Attribution de groupe aux candidats'
	],
	'page_name' => 'Attribution de groupe aux candidats'
])

@section('content')
	<div class="card">
		<div class="card-header text-end">
			<button class="btn btn-primary" onclick="handleSubmit()">Soumettre cette
				sélection
			</button>
		</div>
		<div class="card-body">
			@if($candidatures->isNotEmpty())
				<div class="dt-responsive table-responsive">
					<table id="dom-jquery" class="table table-hover">
						<thead>
						<tr>
							<th scope="col">#</th>
							<th scope="col">Nom</th>
							<th scope="col">Genre</th>
							<th scope="col">Code</th>
							<th scope="col" class="text-center">Action</th>
						</tr>
						</thead>
						<form method="post" id="submit-form">
							@csrf
							<tbody>
							@foreach($candidatures as $key => $candidature)
								<tr>
									<th scope="row">{{ $key + 1 }}</th>
									<td>{{ $candidature->nom . ' ' . $candidature->prenom }}</td>
									<td>{{ $candidature->genre->value }}</td>
									<td>{{ $candidature->code }}</td>
									<td class="text-center">
										<div class="form-group form-check">
											<x-forms.label for="{{ $candidat->slug }}" content="Intégrer au groupe " required="0"/>
											<input type="checkbox" class="form-check-input" name="candidats[]" id="{{ $candidat->slug }}"
														 value="{{ $candidat->slug }}">
										</div>
									</td>
								</tr>
							@endforeach
							</tbody>
						</form>
						<tfoot>
						<tr>
							<th scope="col">#</th>
							<th scope="col">Nom</th>
							<th scope="col">Genre</th>
							<th scope="col">Code</th>
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
@endsection

@section('other-css')
	<link rel="stylesheet" href="{{ asset('admin/assets/css/plugins/dataTables.bootstrap5.min.css') }}">
@endsection

@section('other-js')
	@include('layouts.admin._dt-scripts')
	@include('layouts._select-search-script')
	<script src="{{ asset('admin/assets/js/plugins/sweetalert2.all.min.js') }}"></script>

	<script>
		const url = "{{ route('admin.groups.store-attribution', $group) }}";
		const candidatesList = document.getElementsByName('candidatures[]')

		function handleSubmit() {
			let candidats = [];
			candidatesList.forEach(candidat => candidat.checked && candidats.push(candidat.value));
			Swal.mixin({
				customClass: {
					confirmButton: 'btn btn-success',
					cancelButton: 'btn btn-danger'
				},
				buttonsStyling: false
			}).fire({
				title: 'Êtes-vous sûre?',
				text: "Voulez-vous intégrer ces " + candidats.length + " étudiants à ce groupe ?!",
				icon: 'warning',
				showCancelButton: true,
				confirmButtonText: 'Oui, intégrer',
				cancelButtonText: 'Non, annuler',
				reverseButtons: true
			}).then((result) => {
				if (result.isConfirmed) {
					$.post(url, {
						candidatures: candidats,
						_token: document.getElementsByName('_token')[0].value
					}).then(() => {
						Swal.fire({
							icon: 'success',
							title: 'Décision appliquée avec succès!'
						}).then(() => document.location.reload());
					}).catch((error) => {
						console.log(error)
						Swal.fire({
							icon: 'error',
							title: 'Une erreur est survenue lors du traitement'
						});
					});
				}
			});
		}
	</script>
@endsection
