@php
    use Illuminate\Database\Eloquent\Casts\Json;
    use Illuminate\Support\Facades\Storage;
@endphp
@extends('base', [
    'title' => 'Page des filières',
    'breadcrumbs' => ['Administration', 'Filières', 'Liste'],
    'page_name' => 'Liste des filières',
])

@section('content')
    <div class="card">
        <div class="modal fade" id="exampleModalToggle" aria-hidden="true" aria-labelledby="exampleModalToggleLabel"
            tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="exampleModalToggleLabel">Suppression</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Voulez-vous vraiment supprimer cette filiere ?
                    </div>
                    <div class="modal-footer">
                        <form action="{{ route('admin.filieres.delete') }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" id='idf' name="idfil">
                            <button class='btn btn-warning'>Supprimer</button>
                        </form>
                        <button class='btn btn-secondary' class="btn-close" data-bs-dismiss="modal" aria-label="Close">Annuler</button>
                    </div>

                </div>
            </div>
        </div>
        <div class="card-header">
            <div class="text-end p-4 pb-sm-2 mb-2">
                <a href="{{ route('admin.filieres.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus f-18"></i> Ajouter une filière
                </a>
            </div>
        </div>
        <div class="card-body">
            @if ($filieres->isNotEmpty())
                <div class="dt-responsive table-responsive">
                    <table id="dom-jquery" class="table table-striped table-hover table-bordered">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Nom</th>
                                <th scope="col">Code</th>
                                <th scope="col">Description</th>
                                <th scope="col">Inscrits</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($filieres as $key => $filiere)
                            {{-- {{dd( Storage::disk('public')->exists($filiere->image))}} --}}
                                <tr>
                                    <th scope="row">{{ $key += 1 }}</th>
                                    <td>{{ $filiere->nom }}</td>
                                    <td>{{ $filiere->code }}</td>
                                    <td>{{ substr($filiere->description, 0, 20) }}...</td>
                                    <td>{{ $filiere->etudiants_count }}</td>
                                    <td class="text-center">
                                        <ul class="list-inline me-auto mb-0">
                                            <li class="list-inline-item align-bottom" data-bs-toggle="tooltip"
                                                title="View">
                                                <a href="#"
                                                    onclick="displayShowModal({{ Illuminate\Database\Eloquent\Casts\Json::encode($filiere) }}, '{{ Storage::disk('public')->exists($filiere->image) ? Storage::url($filiere->image) : asset($filiere->image) }}')"
                                                    class="avtar avtar-xs btn-link-secondary btn-pc-default"
                                                    data-bs-toggle="modal" data-bs-target="#show-modal">
                                                    <i class="ti ti-eye f-18"></i>
                                                </a>
                                            </li>
                                            <li class="list-inline-item align-bottom" data-bs-toggle="tooltip"
                                                title="Edit">
                                                <a href="{{ route('admin.filieres.edit', [$filiere]) }}"
                                                    class="avtar avtar-xs btn-link-success btn-pc-default">
                                                    <i class="ti ti-edit-circle f-18"></i>
                                                </a>
                                            </li>
                                            <li class="list-inline-item align-bottom" data-bs-toggle="tooltip"
                                                title="Delete">
                                                <a onclick="filiereDelete({{ $filiere->id }})"
                                                    data-bs-target="#exampleModalToggle" data-bs-toggle="modal"
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
                                <th scope="col">Code</th>
                                <th scope="col">Description</th>
                                <th scope="col">Inscrits</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <x-empty-table />
            @endif
        </div>
    </div>

    @include('admin.filieres._show')
@endsection

@section('other-js')
    @include('layouts.admin._dt-scripts')
    <script>
        function filiereDelete(id) {
            document.getElementById('idf').value = id;
        }

        function displayShowModal(filiere, imageUrl) {
            document.getElementById('filiere-desc').innerHTML = filiere.description;
            // document.getElementById('filiere-nom').innerHTML = filiere.nom;
            document.getElementById('etudiants-count').innerHTML = filiere.etudiants_count;
            document.getElementById('card-nom').innerHTML = filiere.nom;
            document.getElementById('filiere-code').innerHTML = filiere.code;
            document.getElementById('filiere-img').src = imageUrl;
        }
    </script>
@endsection

@section('other-css')
    <link rel="stylesheet" href="{{ asset('admin/assets/css/plugins/dataTables.bootstrap5.min.css') }}">
@endsection
