@php use Illuminate\Database\Eloquent\Casts\Json; @endphp
@extends('base', [
    'title' => 'Liste des salles',
    'breadcrumbs' => ['Administration', 'Salles', 'Liste'],
    'page_name' => 'Liste des salles',
])

@section('content')
    <div class="card">
        <div class="text-end p-4 pb-sm-2 mb-2">
            <a href="#" data-bs-toggle="modal" data-bs-target="#editSalleModal" class="btn btn-primary"
                onclick="refreshForm();">
                <i class="ti ti-plus f-18"></i> Ajouter une salle
            </a>
        </div>
        <div class="card-body">
            @if ($salles->isNotEmpty())
                <div class="dt-responsive table-responsive">
                    <table id="dom-jquery" class="table table-hover">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Nom</th>
                                <th scope="col">Nombre de place</th>
                                <th scope="col" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($salles as $key => $salle)
                                <tr>
                                    <th scope="row">{{ $key += 1 }}</th>
                                    <td>{{ $salle->nom }}</td>
                                    <td>{{ $salle->effectif }}</td>
                                    <td class="text-center">
                                        <ul class="list-inline me-auto mb-0">
                                            <li class="list-inline-item align-bottom" data-bs-toggle="tooltip"
                                                title="Modifier">
                                                <a href="#"
                                                    onclick="event.preventDefault(); displayEditModal({{ Illuminate\Database\Eloquent\Casts\Json::encode($salle) }})"
                                                    data-bs-toggle="modal" data-bs-target="#editSalleModal"
                                                    class="avtar avtar-xs btn-link-success btn-pc-default">
                                                    <i class="ti ti-edit-circle f-18"></i>
                                                </a>
                                            </li>
                                            <li class="list-inline-item align-bottom" data-bs-toggle="tooltip"
                                                title="Planifications">
                                                <a href="{{ route('admin.salles.display-calendar', $salle) }}"
                                                    class="avtar avtar-xs btn-link-secondary btn-pc-default">
                                                    <i data-feather="calendar" class="f-18"></i>
                                                </a>
                                            </li>
                                            <li class="list-inline-item align-bottom" data-bs-toggle="tooltip"
                                                title="Supprimer">
                                                <a href="#" onclick="deleteSalle({{ $salle->id }});return false"
                                                    data-bs-target="#exampleModalToggle2" data-bs-toggle="modal"
                                                    class="avtar avtar-xs btn-link-danger btn-pc-default">
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
                                <th scope="col">Nombre de place</th>
                                <th scope="col" class="text-center">Action</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <x-empty-table />
            @endif
        </div>
    </div>
    @include('admin.salles._form')
    @include('admin.salles.___show-modal')
@endsection

@section('other-js')
    @include('layouts.admin._dt-scripts')
    <script>
        function displayEditModal(salle) {
            document.getElementById('nom').value = salle.nom;
            document.getElementById('effectif').value = salle.effectif;
            document.getElementById('exampleModalCenterTitle').innerHTML = "Formulaire de modification d'une salle";
        }

        function refreshForm() {
            document.getElementById('exampleModalCenterTitle').innerHTML = 'Formulaire de création d\'une salle';
            document.getElementById('nom').value = '';
            document.getElementById('effectif').value = '';
        }

        function deleteSalle(id) {
        document.getElementById('deleteSalleForm').value=id;
        }
    </script>
@endsection

@section('other-css')
    <link rel="stylesheet" href="{{ asset('admin/assets/css/plugins/dataTables.bootstrap5.min.css') }}">
@endsection
