@extends('base', [
    'title' => 'Liste des ues',
    'breadcrumbs' => ['Administration', 'ues', 'Liste'],
    'page_name' => 'Liste des ues',
])

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="text-end p-4 pb-sm-2 mb-2">
                <a href="{{ route('admin.ues.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus f-18"></i> Ajouter une UE
                </a>
            </div>
        </div>
        <div class="card-body">
            @if ($ues->isNotEmpty())
                <div class="dt-responsive table-responsive">
                    <table id="dom-jquery" class="table table-hover">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Nom</th>
                                <th scope="col">Code</th>
                                <th scope="col">Crédits</th>
                                <th scope="col">Filière</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        {{-- {{ dd(Illuminate\Database\Eloquent\Casts\Json::encode($ues)) }}  --}}
                        <tbody>
                            @foreach ($ues as $key => $ue)
                                <tr>
                                    <th scope="row">{{ $key + 1 }}</th>
                                    <td>{{ $ue->nom }}</td>
                                    <td>{{ $ue->code }}</td>
                                    <td>{{ $ue->credit }}</td>
                                    <td>{{ $ue->filiere->code }}</td>

                                    <td class="text-center">
                                        <ul class="list-inline me-auto mb-0">
                                            <li class="list-inline-item align-bottom" data-bs-toggle="tooltip"
                                                title="Modifier">
                                                <a href="{{ route('admin.ues.edit', [$ue]) }}"
                                                    class="avtar avtar-xs btn-link-success btn-pc-default">
                                                    <i class="ti ti-edit-circle f-18"></i>
                                                </a>
                                            </li>
                                            <li class="list-inline-item align-bottom" data-bs-toggle="tooltip"
                                                title="Planifications">
                                                <a href="#"
                                                    onclick="displayShowUe({{ Illuminate\Database\Eloquent\Casts\Json::encode($ue) }})"
                                                    data-bs-toggle="modal" data-bs-target="#eventShowModal"
                                                    class="avtar avtar-xs btn-link-secondary btn-pc-default">
                                                    <i class="ti ti-eye f-18"></i>
                                                </a>
                                            </li>
                                            <li class="list-inline-item align-bottom" data-bs-toggle="tooltip"
                                                title="Supprimer">
                                                <a data-bs-target="#exampleModalToggle2" data-bs-toggle="modal"
												onclick="deleteIdUe({{$ue->id}})"
                                                    href="#" class="avtar avtar-xs btn-link-danger btn-pc-default">
                                                    <i class="ti ti-trash f-18"></i>
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
                                <th scope="col">Code</th>
                                <th scope="col">Crédits</th>
                                <th scope="col">Filière</th>
                                <th scope="col">Action</th>
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
@include('admin.ues._show')
@include('admin.ues.___show')
@section('other-js')
    @include('layouts.admin._dt-scripts')
    <script>
		 function deleteIdUe(ue) {
            document.getElementById('idue').value =ue;
        }
        function displayShowUe(ue) {
            document.getElementById('ue-filiere-nom').value = ue.filiere.nom;
            document.getElementById('ue-periode').innerHTML = ue.periode.nom;
            document.getElementById('ue-credit').value = ue.credit;
            document.getElementById('card-nom').value = ue.nom;
            document.getElementById('ue-code').value = ue.code;

        }
    </script>
@endsection

@section('other-css')
    <link rel="stylesheet" href="{{ asset('admin/assets/css/plugins/dataTables.bootstrap5.min.css') }}">
@endsection
