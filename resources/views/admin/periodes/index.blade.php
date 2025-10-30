@extends('base', [
    'title' => 'Liste des périodes',
    'breadcrumbs' => ['Administration', ['url' => route('admin.periodes.index'), 'text' => 'Périodes'], 'Liste'],
    'page_name' => 'Liste des périodes',
])

@section('content')
    <div class="card">
        <div class="text-end p-4 pb-sm-2 mb-2">
            <a href="{{ route('admin.periodes.create') }}" class="btn btn-primary">
                <i class="ti ti-plus f-18"></i> Ajouter une période
            </a>
        </div>
        <div class="card-body">
            @if ($periodes->isNotEmpty())
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
                            @forelse($periodes as $key => $periode)
                                <tr>
                                    <th scope="row">{{ $key += 1 }}</th>
                                    <td>{{ $periode->nom }}</td>
                                    <td>{{ $periode->debut->translatedFormat('d F Y') }}</td>
                                    <td>{{ $periode->fin->translatedFormat('d F Y') }}</td>
                                    <td class="text-center">
                                        <ul class="list-inline me-auto mb-0">
                                            <li class="list-inline-item align-bottom" data-bs-toggle="tooltip"
                                                title="Modifier">
                                                <a href="{{ route('admin.periodes.edit', [$periode]) }}"
                                                    class="avtar avtar-xs btn-link-success btn-pc-default">
                                                    <i class="ti ti-edit-circle f-18"></i>
                                                </a>
                                            </li>
                                            <li class="list-inline-item align-bottom" title="Supprimer">
                                                <a href="#" data-bs-target="#exampleModalToggle2"
                                                    data-bs-toggle="modal" onclick="deletePeriode({{ $periode->id }})"
                                                    class="avtar avtar-xs btn-link-danger btn-pc-default">
                                                    <i class="ti ti-trash f-18"></i>
                                                </a>
                                            </li>
                                        </ul>
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
                                <th scope="col">Code</th>
                                <th scope="col">Description</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <div class="alert alert-warning">
                    <div class="media align-items-center">
                        <i class="ti ti-info-circle h2 f-w-400 mb-0"></i>
                        <div class="media-body ms-3"> Aucune donnée à afficher</div>
                    </div>
                </div>
            @endif

        </div>
    </div>
@endsection
@include('admin.periodes.____show-modal')
@section('other-js')
    @include('layouts.admin._dt-scripts')

	<script>
		 function deletePeriode(periode) {
            document.getElementById('periode').value = periode;
        }
	</script>
@endsection

@section('other-css')
    <link rel="stylesheet" href="{{ asset('admin/assets/css/plugins/dataTables.bootstrap5.min.css') }}">
@endsection
