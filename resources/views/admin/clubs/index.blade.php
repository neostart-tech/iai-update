@php
use Illuminate\Database\Eloquent\Casts\Json;
@endphp

@extends('base', [
'title' => 'Liste des clubs',
'breadcrumbs' => ['Administration', 'Clubs étudiants', 'Liste'],
'page_name' => 'Liste des clubs étudiants',
])
@section('content')
<div class="card">
    <div class="card-header">
        <div class="text-end p-4 pb-sm-2 mb-2">
            <a href="#" data-bs-toggle="modal" data-bs-target="#clubModal" class="btn btn-primary"
                onclick="refreshForm();">
                <i class="ti ti-plus f-18"></i> Ajouter un club
            </a>
        </div>
    </div>

    <div class="card-body">
        @if ($clubs->isNotEmpty())
        <div class="dt-responsive table-responsive">
            <table id="dom-jquery" class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nom</th>
                        <th>Responsable</th>
                        <th>Effectif</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($clubs as $key => $club)
                    <tr>
                        <th>{{ $key + 1 }}</th>
                        <td>{{ $club->nom }}</td>
                        <td>{{ $club->responsable->nom .' '. $club->responsable->prenom  ?? '—' }}</td>
                        <td>{{ $club->etudiants_count }}</td>
                        <td class="text-center">
                            <ul class="list-inline me-auto mb-0">

                                {{-- Modifier --}}
                                <li class="list-inline-item" data-bs-toggle="tooltip" title="Modifier">
                                    <a href="#"
                                        onclick="event.preventDefault(); displayEditModal({{ Json::encode($club) }})"
                                        data-bs-toggle="modal" data-bs-target="#clubModal"
                                        class="avtar avtar-xs btn-link-success btn-pc-default">
                                        <i class="ti ti-edit-circle f-18"></i>
                                    </a>
                                </li>

                                {{-- Membres --}}
                                <li class="list-inline-item" data-bs-toggle="tooltip" title="Membres">
                                    <a href="{{ route('admin.club.etudiants.create', $club) }}"
                                        class="avtar avtar-xs btn-link-secondary btn-pc-default">
                                        <i class="fas fa-users f-18"></i>
                                    </a>
                                </li>

                                {{-- Supprimer --}}
                                <li class="list-inline-item" data-bs-toggle="tooltip" title="Supprimer">
                                    <a href="#" data-bs-toggle="modal"
                                        data-bs-target="#deleteModal"
                                        onclick="deleteClub({{ $club->id }})"
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
                        <th>#</th>
                        <th>Nom</th>
                        <th>Responsable</th>
                        <th>Effectif</th>
                        <th class="text-center">Action</th>
                    </tr>
                </tfoot>
            </table>
        </div>
        @else
        <div class="alert alert-warning">
            <i class="ti ti-info-circle"></i> Aucun club enregistré
        </div>
        @endif
    </div>
</div>

@include('admin.clubs._modal')
@endsection


<script>
    function displayEditModal(club) {
        document.getElementById('nom').value = club.nom;
        document.getElementById('clubId').value = club.id;
        document.getElementById('responsable_id').value = club.responsable_id;
        document.getElementById('exampleModalCenterTitle').innerHTML =
            "Formulaire de modification du club";
    }



    function deleteClub(id) {
        document.getElementById('club_id').value = id;
    }

    function refreshForm() {
        document.getElementById('exampleModalCenterTitle').innerHTML =
            "Formulaire de création d'un club";
        document.getElementById('nom').value = '';
        document.getElementById('clubId').value = '';
    }
</script>

