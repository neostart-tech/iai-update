@php use Illuminate\Database\Eloquent\Casts\Json; @endphp
@extends("base", [
	'title' => 'Page des rôles',
	'breadcrumbs' => ['Administration', 'Rôles', 'Liste'],
	'page_name' => 'Liste des rôles'
])

@section('content')
	<div class="card">
		<div class="card-body">
			<div class="dt-responsive table-responsive">
				<table id="dom-jquery" class="table table-hover">
					<thead>
					<tr>
						<th scope="col">#</th>
						<th scope="col">Nom</th>
						<th scope="col">Actions</th>
					</tr>
					</thead>
					<tbody>
					@forelse($roles as $key => $role)
						<tr>
							<th scope="row">{{ $key+=1 }}</th>
							<td>{{ $role->nom  }}</td>
							<td class="text-center">
								<button type="button" onclick="displayShowModal({{ Json::encode($role) }})" data-bs-toggle="modal"
												data-bs-target="#rolesIndexModal" class="btn btn-icon btn-outline-info">
									<i class="ti ti-info-circle"></i>
								</button>
							</td>
						</tr>
					@empty
						<tr>
							<td colspan="3">No data</td>
						</tr>
					@endforelse
					</tbody>
					<tfoot>
					<tr>
						<th scope="col">#</th>
						<th scope="col">Nom</th>
						<th scope="col">Actions</th>
					</tr>
					</tfoot>
				</table>
			</div>
		</div>
	</div>
	@include('admin.roles._show')
@endsection

@section('other-js')
	@include('layouts.admin._dt-scripts')
	<script>
		let permissionsTable = document.getElementById('permissions-table');

		function displayShowModal(role) {
			document.getElementById('role-nom').innerHTML = `Role: ${role.nom}`;
			Array.from(role.permissions).forEach((permission) => {
				let tr = document.createElement('tr');
				let td = document.createElement('td');
				td.innerText = permission.nom;
				tr.appendChild(td);
				permissionsTable.appendChild(tr);
			});
		}

		$('.rolesIndexModal').on('hidden.bs.modal', () => permissionsTable.innerHTML = '');
	</script>
@endsection

@section('other-css')
	<link rel="stylesheet" href="{{ asset('admin/assets/css/plugins/dataTables.bootstrap5.min.css') }}">
@endsection
