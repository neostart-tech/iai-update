@php use App\Models\FicheDePresence;use Illuminate\Support\Js; @endphp
@extends('base', [
	'title' => 'Liste des Évaluations',
	'breadcrumbs' => ['Administration', 'Évaluations', 'Liste'],
	'page_name' => 'Liste des Évaluations'
])

@section('content')
	<div class="card">
		<div class="card-header">
			<div class="text-end p-4 pb-sm-2 mb-2">
				<a href="{{ route('admin.evaluations.create') }}" class="btn btn-primary">
					<i class="ti ti-plus f-18"></i> Ajouter une évaluation
				</a>
			</div>
		</div>
		<div class="card-body">
			@if($evaluations->isNotEmpty())
				<div class="dt-responsive table-responsive">
					<table id="dom-jquery" class="table table-hover">
						<thead>
						<tr>
							<th scope="col">#</th>
							<th scope="col">Type</th>
							<th scope="col">Matière</th>
							<th scope="col">Jour</th>
							<th scope="col">Publier</th>
							<th scope="col" class="text-center">Action</th>
						</tr>
						</thead>
						<tbody>
						@foreach($evaluations as $key => $evaluation)
							<tr class="{{ $evaluation->fin->isBefore(now()) ? "table-secondary" : '' }}">
								<th scope="row">{{ $key+=1 }}</th>
								<td>{{ $evaluation->type->value }}</td>
								<td>{{ $evaluation->matiere->nom }}</td>
								<td>{{ $evaluation->dateFormatted }}</td>
								<td>
									<div class="form-check form-switch custom-switch-v1 mb-2">
										<input type="checkbox" class="form-check-input input-success" @checked($evaluation->published)
										@disabled($evaluation->published)
										onchange="handlePublishFromAttrs(this)"
													 value="{{ $evaluation->slug }}" id="{{ $evaluation->slug  }}"
													 data-slug="{{ $evaluation->slug }}"
													 data-group="{{ $evaluation->group->nom }}"
													 data-filiere="{{ $evaluation->group->filiere->code }}">
										<label class="form-check-label" id="label-{{ $evaluation->slug }}" for="{{ $evaluation->slug  }}">
											{{$evaluation->published ? 'Publié' : 'Non publiée'}}
										</label>
									</div>
								</td>
								<td class="text-center">
									<ul class="list-inline me-auto mb-0">
										<li class="list-inline-item align-bottom" data-bs-toggle="tooltip" title="Détails">
								    <a href="#"
									    onclick="event.preventDefault(); displayShowModalFromAttrs(this)"
									    data-bs-toggle="modal" data-bs-target="#show-modal"
									    data-type="{{ $evaluation->type->value }}"
									    data-group="{{ $evaluation->group->nom }}"
									    data-filiere="{{ $evaluation->group->filiere->code }}"
									    data-matiere="{{ $evaluation->matiere->nom }}"
									    data-salle="{{ $evaluation->salle->nom }}"
									    data-date="{{ $evaluation->dateFormatted }}"
									    data-debut="{{ $evaluation->debutFormatted }}"
									    data-fin="{{ $evaluation->finFormatted }}"
									    data-published="{{ $evaluation->published ? '1' : '0' }}"
									    data-passed="{{ $evaluation->fin->isBefore(now()) ? '1' : '0' }}"
												 class="avtar avtar-xs btn-link-success btn-pc-default">
												<i class="ti ti-eye f-18"></i>
											</a>
										</li>
										<li class="list-inline-item align-bottom" data-bs-toggle="tooltip" title="Modifier">
											<a href="{{ route('admin.evaluations.edit', $evaluation) }}"
												 class="avtar avtar-xs btn-link-secondary btn-pc-default">
												<i data-feather="edit" class="f-18"></i>
											</a>
										</li>
										<li class="list-inline-item align-bottom" data-bs-toggle="tooltip" title="Configurer">
								    <a onclick="showConfigModalFromAttrs(this)"
									    data-evaluation-slug="{{ $evaluation->slug }}"
									    data-url="{{ $evaluation->fiche ? route('admin.fiches.update', $evaluation->fiche) : ''}}"
									    data-surv1="{{ $evaluation->fiche && $evaluation->fiche->surveillants->get(0) ? $evaluation->fiche->surveillants->get(0)->slug : '' }}"
									    data-surv2="{{ $evaluation->fiche && $evaluation->fiche->surveillants->get(1) ? $evaluation->fiche->surveillants->get(1)->slug : '' }}"
									    data-bs-toggle="modal" data-bs-target="#config-modal"
												 class="avtar avtar-xs btn-link-secondary btn-pc-default">
												<i data-feather="settings" class="f-18"></i>
											</a>
										</li>
										<li class="list-inline-item align-bottom center" data-bs-toggle="tooltip" title="Fiche d'anonymat">
											<a href="{{ route('admin.evaluations.fiche-de-note', $evaluation) }}"
												 class="avtar avtar-xs btn-link-secondary btn-pc-default">
												<i class="fa fas fa-file-signature f-18"></i>
											</a>
										</li>
										<li class="list-inline-item align-bottom center" data-bs-toggle="tooltip" title="Supprimer">
											<a href="{{ route('admin.evaluations.edit', $evaluation) }}"
												 class="avtar avtar-xs btn-link-secondary btn-pc-default">
												<i data-feather="trash" class="f-18"></i>
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
							<th scope="col">Type</th>
							<th scope="col">Matière</th>
							<th scope="col">Jour</th>
							<th scope="col">Publier</th>
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

	@include('admin.evaluations._show')
	@include('admin.evaluations._config')
@endsection

@section('other-js')
	@include('layouts._select-search-script')
	@include('layouts.admin._dt-scripts')
	<script>
		// flashy('Message')
		let select1 = document.getElementById('surveillant-1');
		let select2 = document.getElementById('surveillant-2');
		let configForm = document.getElementById('config-form');
	const defaultUrl = "{{ route('admin.fiches.store') }}";

		function displayShowModalFromAttrs(el) {
			const ds = el.dataset;
			// To use this, add data-* attributes on the link element in Blade
			// We'll populate via attributes attached below
			document.getElementById('show-type').value = ds.type ?? '';
			document.getElementById('show-group').value = (ds.group ?? '') + ' - ' + (ds.filiere ?? '');
			document.getElementById('show-matiere').value = ds.matiere ?? '';
			document.getElementById('show-salle').value = ds.salle ?? '';
			document.getElementById('show-date').value = ds.date ?? '';
			document.getElementById('show-debut').value = ds.debut ?? '';
			document.getElementById('show-fin').value = ds.fin ?? '';
			document.getElementById('show-published').value = (ds.published === '1') ? 'Publiée' : 'Non publiée';
			document.getElementById('show-passed').value = (ds.passed === '1') ? 'Passée' : 'À venir';
		}

		function handlePublishFromAttrs(el) {
			const slug = el.dataset.slug;
			const group = el.dataset.group;
			const filiere = el.dataset.filiere;
			Swal.fire({
				icon: 'info',
				showCancelButton: true,
				confirmButtonText: "Publier",
				cancelButtonText: 'Ne pas publier',
				html: `Si vous cochez cette case, une notification sera envoyée à tous les étudiants de
					<b>${group + ' - ' + filiere}</b> et l'évaluation sera ajoutée à leur emploi du temps.`
			}).then(result => {
				document.getElementById(slug).checked = result.isConfirmed;
				if (result.isConfirmed) {
					const baseUl = "{{ url('administration/evaluations/{slug}/publier') }}";
					$.get(baseUl.replace('{slug}', slug))
						.then(response => {
							showToast(response.message, 'success');
							document.getElementById(slug).checked = true;
							document.getElementById(slug).disabled = true;
							document.getElementById(`label-${slug}`).innerText = 'Publiée';
						}).catch(() => {
						showToast('Erreur', 'danger');
					});
				}
			});
		}

		function showConfigModalFromAttrs(el) {
			const ds = el.dataset;
			document.getElementById('evaluation_id').value = ds.evaluationSlug ?? '';

			if ((ds.url ?? '') !== '') {
				configForm.setAttribute('action', ds.url);
				let methodInput = document.createElement('input');
				methodInput.setAttribute('type', 'hidden');
				methodInput.setAttribute('name', '_method');
				methodInput.setAttribute('value', 'PUT');
				configForm.appendChild(methodInput);
			} else {
				refreshForm();
			}

			const surv1 = ds.surv1 ?? '';
			const surv2 = ds.surv2 ?? '';
			Array.from(select1.options).forEach((option, index) => {
				if (option.value === surv1) {
					select1.selectedIndex = index
				}
			});
			Array.from(select2.options).forEach((option, index) => {
				if (option.value === surv2) {
					select2.selectedIndex = index;
				}
			});
		}

		$('#config-modal').on('hidden.bs.modal', () => {
			refreshForm();
		});

		function refreshForm() {
			configForm.setAttribute('action', defaultUrl);
			select1.selectedIndex = 0;
			select2.selectedIndex = 0;
			const m = document.getElementsByName('_method')[0];
			if (m) m.remove();
		}
	</script>
@endsection

@section('other-css')
	<link rel="stylesheet" href="{{ asset('admin/assets/css/plugins/dataTables.bootstrap5.min.css') }}">
@endsection