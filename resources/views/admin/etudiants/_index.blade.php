@extends('base', [
'title' => 'Liste des étudiants',
'page_name' => 'Liste des étudiants',
'breadcrumbs' => ['Étudiants', 'Liste'],
])

@section('content')

<div class="card">

    <div class="card-header">
        <div class="d-flex flex-wrap justify-content-end gap-2 mb-3">
         
            <button
                type="button"
                class="btn btn-success d-flex align-items-center"
                data-bs-toggle="modal"
                data-bs-target="#importEtudiantsModal">
                <i class="fa fa-file-excel me-2"></i> Importer des étudiants
            </button>

            <a
                href="{{ route('admin.etudiants.export') }}"
                class="btn btn-primary d-flex align-items-center">
                <i class="fa fa-download me-2"></i> Exporter les étudiants
            </a>
        </div>

        <form method="GET" class="row g-3">


            <div class="col-md-4">
                <label class="form-label">Groupe</label>
                <select name="group_id" class="form-select">
                    <option value="">-- Tous les groupes --</option>
                    @foreach ($groupes as $groupe)
                    <option value="{{ $groupe->id }}" @selected(request('group_id')==$groupe->id)>
                        {{$groupe->niveau->libelle}} {{ $groupe->nom }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Filière</label>
                <select name="filiere_id" class="form-select">
                    <option value="">-- Toutes les filières --</option>
                    @foreach ($filieres as $filiere)
                    <option value="{{ $filiere->id }}" @selected(request('filiere_id')==$filiere->id)>
                        {{ $filiere->nom }}
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- NIVEAU --}}
            <div class="col-md-4">
                <label class="form-label">Niveau</label>
                <select name="niveau_id" class="form-select">
                    <option value="">-- Tous les niveaux --</option>
                    @foreach ($niveaux as $niveau)
                    <option value="{{ $niveau->id }}" @selected(request('niveau_id')==$niveau->id)>
                        {{ $niveau->libelle }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-12 text-end">
                <button class="btn btn-primary">
                    <i class="fa fa-filter"></i> Filtrer
                </button>
                <a href="{{ route('admin.etudiants.index') }}" class="btn btn-secondary">
                    Réinitialiser
                </a>
            </div>

        </form>
    </div>

    <div class="card-body">

        @if ($etudiants->isNotEmpty())

        <div class="dt-responsive table-responsive">
            <table class="table table-hover" id="dom-jquery">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nom & Prénoms</th>
                        <th>Matricule</th>
                        <th>Genre</th>
                        <th>Groupe</th>
                        <th>Filière</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($etudiants as $key => $etudiant)

                    @php
                    $inscription = $etudiant->etudiantGroups->first()->group->nom;
                    @endphp
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $etudiant->nom }} {{ $etudiant->prenom }}</td>
                        <td>{{ $etudiant->matricule }}</td>
                        <td>{{ $etudiant->genre->value }}</td>

                        <td>{{$etudiant->etudiantGroups->first()->niveau->libelle}} {{ $etudiant->etudiantGroups->first()->group->nom ?? '-' }}</td>
                        <td>{{ $etudiant->etudiantGroups->first()->filiere->nom ?? '-' }}</td>

                        <td class="text-center">
                            <ul class="list-inline mb-0">


                                <!-- <li class="list-inline-item" data-bs-toggle="tooltip" title="Changer de groupe">
                                    <a href="#" data-bs-toggle="modal"
                                        data-bs-target="#animateModal"
                                        onclick="handleGroupeUpdate(
                                                   '{{ route('admin.etudiants.change-group', $etudiant) }}',
                                                   '{{ $etudiant->etudiantGroups->first()->group->nom ?? '' }}'
                                               )"
                                        class="avtar avtar-xs btn-link-secondary">
                                        <i data-feather="edit"></i>
                                    </a>
                                </li> -->


                                <li class="list-inline-item" data-bs-toggle="tooltip" title="Détails">
                                    <a href="{{ route('admin.etudiants.show', $etudiant) }}"
                                        class="avtar avtar-xs btn-link-secondary">
                                        <i data-feather="eye"></i>
                                    </a>
                                </li>


                                <!-- <li class="list-inline-item" data-bs-toggle="tooltip" title="Voir relevé">
                                    <a onclick="releveliste({{ $etudiant->id }})"
                                        data-bs-toggle="modal"
                                        data-bs-target="#exampleModalToggle2"
                                        class="avtar avtar-xs btn-link-secondary">
                                        <i class="fa fa-file-pdf"></i>
                                    </a>
                                </li>

                                <li class="list-inline-item" data-bs-toggle="tooltip" title="Télécharger">
                                    <a href="{{ route('admin.releves.telecharger', $etudiant->slug) }}"
                                        class="avtar avtar-xs btn-link-secondary">
                                        <i data-feather="download"></i>
                                    </a>
                                </li> -->

                                <li class="list-inline-item" data-bs-toggle="tooltip" title="Carte étudiant">
                                    <a href="{{ route('admin.carte.index', $etudiant->slug) }}"
                                        class="avtar avtar-xs btn-link-secondary">
                                        <i data-feather="printer"></i>
                                    </a>
                                </li>

                            </ul>
                        </td>
                    </tr>
                    @endforeach
                </tbody>

            </table>
        </div>

        @else
        <x-empty-table />
        @endif

    </div>
</div>

@include('admin.etudiants._change-group')
@include('admin.etudiants.__show')
@include('admin.etudiants._import_modal')

@endsection

@section('other-css')
<link rel="stylesheet" href="{{ asset('admin/assets/css/plugins/dataTables.bootstrap5.min.css') }}">
@endsection

@section('other-js')
@include('layouts.admin._dt-scripts')
@endsection