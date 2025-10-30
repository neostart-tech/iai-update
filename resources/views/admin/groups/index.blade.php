@php
    use App\Models\Group;
    use Illuminate\Database\Eloquent\Casts\Json;
@endphp
@extends('base', [
    'title' => 'Liste des groupes',
    'breadcrumbs' => ['Administration', 'Groupes d\'étudiant', 'Liste'],
    'page_name' => 'Liste des groupes d\'étudiant',
])

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="text-end p-4 pb-sm-2 mb-2">
                <a href="#" data-bs-toggle="modal" data-bs-target="#editSalleModal" class="btn btn-primary"
                    onclick="refreshForm();">
                    <i class="ti ti-plus f-18"></i> Ajouter un groupe
                </a>
            </div>
        </div>
        <div class="card-body">
            @if ($groups->isNotEmpty())
                <div class="dt-responsive table-responsive">
                    <table id="dom-jquery" class="table table-hover">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Nom</th>
                                <th scope="col">Filière</th>
                                <th scope="col">Effectif</th>
                                <th scope="col" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($groups as $key => $group)
                                <tr>
                                    <th scope="row">{{ $key += 1 }}</th>
                                    <td>{{ $group->nom }}</td>
                                    <td>{{ $group->filiere->nom }}</td>
                                    <td>{{ $group->etudiants_count }}</td>
                                    <td class="text-center">
                                        <ul class="list-inline me-auto mb-0">
                                            <li class="list-inline-item align-bottom" data-bs-toggle="tooltip"
                                                title="Modifier">
                                                <a href="#"
                                                    onclick="event.preventDefault(); displayEditModal({{ Illuminate\Database\Eloquent\Casts\Json::encode($group) }})"
                                                    data-bs-toggle="modal" data-bs-target="#editSalleModal"
                                                    class="avtar avtar-xs btn-link-success btn-pc-default">
                                                    <i class="ti ti-edit-circle f-18"></i>
                                                </a>
                                            </li>
                                            <li class="list-inline-item align-bottom" data-bs-toggle="tooltip"
                                                title="Planifications">
                                                <a href="{{ route('admin.groups.display-calendar', $group) }}"
                                                    class="avtar avtar-xs btn-link-secondary btn-pc-default">
                                                    <i data-feather="calendar" class="f-18"></i>
                                                </a>
                                            </li>
                                            <li class="list-inline-item align-bottom" data-bs-toggle="tooltip"
                                                title="Liste des étudiants">
                                                <a href="{{ route('admin.groups.etudiants', $group) }}"
                                                    class="avtar avtar-xs btn-link-secondary btn-pc-default">
                                                    <i class="fas fa-list-ol f-18"></i>
                                                </a>
                                            </li>
                                           
                                            <li class="list-inline-item align-bottom" data-bs-toggle="tooltip"
                                                title="Supprimer">
                                                <a href="#" data-bs-target="#exampleModalToggle2"
                                                    data-bs-toggle="modal" onclick='deleteGroup({{ $group->id }})'
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
                                <th scope="col">Filière</th>
                                <th scope="col">Effectif</th>
                                <th scope="col" class="text-center">Action</th>
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
    @include('admin.groups._show-modal')
@endsection

@section('other-js')
    @include('admin.groups.___show-modal')
    @include('layouts.admin._dt-scripts')
    @include('layouts._select-search-script')
    <script>
        function displayEditModal(group) {
            document.getElementById('nom').value = group.nom;
            document.getElementById('groupId').value = group.id;
            document.getElementById('exampleModalCenterTitle').innerHTML = "Formulaire de modification d'une salle";
        }

        function deleteGroup(id) {
            document.getElementById('groupe').value = id
        }

        function refreshForm() {
            document.getElementById('exampleModalCenterTitle').innerHTML = 'Formulaire de création d\'une salle';
            document.getElementById('nom').value = '';
            document.getElementById('groupId').value = "";
        }
    </script>
@endsection

@section('other-css')
    <link rel="stylesheet" href="{{ asset('admin/assets/css/plugins/dataTables.bootstrap5.min.css') }}">
@endsection
