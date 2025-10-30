@php
    use Illuminate\Database\Eloquent\Casts\Json;
    use Illuminate\Support\Facades\Storage;
@endphp
@extends('base', [
    'title' => 'Gestion des années scolaires',
    'breadcrumbs' => ['Administration', 'Années scolaires'],
    'page_name' => 'Liste des années scolaires',
])

@section('content')
<div class="card">
    {{-- Modal Ajout Année --}}
    <div class="modal fade" id="addAnneeModal" tabindex="-1" aria-labelledby="addAnneeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('anneescolaires.store') }}">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addAnneeModalLabel">Nouvelle année scolaire</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="libelle" class="form-label">Libellé</label>
                            <input type="text" name="libelle" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edition Année --}}
    <div class="modal fade" id="editAnneeModal" tabindex="-1" aria-labelledby="editAnneeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" id="editAnneeForm">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editAnneeModalLabel">Modifier année scolaire</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_libelle" class="form-label">Libellé</label>
                            <input type="text" name="libelle" id="edit_libelle" class="form-control" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" name="active" id="edit_active" class="form-check-input" value="1">
                            <label class="form-check-label" for="edit_active">Activer cette année</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Modifier</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Suppression --}}
    <div class="modal fade" id="deleteAnneeModal" tabindex="-1" aria-labelledby="deleteAnneeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" id="deleteAnneeForm">
                @csrf
                @method('DELETE')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteAnneeModalLabel">Suppression</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">
                        Voulez-vous vraiment supprimer cette année scolaire ?
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-warning">Supprimer</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card-header">
        <div class="text-end p-4 pb-sm-2 mb-2">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAnneeModal">
                <i class="ti ti-plus f-18"></i> Nouvelle année scolaire
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="dt-responsive table-responsive">
            <table class="table table-striped table-hover table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Libellé</th>
                        <th>Active</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($annees as $annee)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $annee->libelle }}</td>
                            <td>
                                @if($annee->active)
                                    <span class="badge bg-success">Oui</span>
                                @else
                                    <span class="badge bg-secondary">Non</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <ul class="list-inline me-auto mb-0">
                                    <li class="list-inline-item align-bottom" data-bs-toggle="tooltip" title="Edit">
                                        <a href="#" class="avtar avtar-xs btn-link-success btn-pc-default"
                                            onclick="editAnnee({{ $annee->id }}, '{{ addslashes($annee->libelle) }}', {{ $annee->active ? 1 : 0 }})">
                                            <i class="ti ti-edit-circle f-18"></i>
                                        </a>
                                    </li>
                                    <li class="list-inline-item align-bottom" data-bs-toggle="tooltip" title="Delete">
                                        <a href="#" class="avtar avtar-xs btn-link-danger btn-pc-default"
                                            onclick="deleteAnnee({{ $annee->id }})" data-bs-toggle="modal" data-bs-target="#deleteAnneeModal">
                                            <i class="ti ti-trash f-18"></i>
                                        </a>
                                    </li>
                                </ul>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('other-js')
{{-- Inclure SweetAlert si ce n'est pas déjà fait --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function deleteAnnee(id) {
        let form = document.getElementById('deleteAnneeForm');
        form.action = "{{ url('admin/anneescolaires') }}/" + '/' + id;
    }

    function editAnnee(id, libelle, active) {
        let form = document.getElementById('editAnneeForm');
        form.action = "{{ url('admin/anneescolaires') }}/" + '/' + id;
        document.getElementById('edit_libelle').value = libelle;
        document.getElementById('edit_active').checked = !!active;
        new bootstrap.Modal(document.getElementById('editAnneeModal')).show();
    }

    // Affichage des messages de succès ou d'échec avec Swal
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Succès',
            text: "{{ session('success') }}",
            timer: 2500,
            showConfirmButton: false
        });
    @endif
    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Erreur',
            
            text: "{{ session('error') }}",
            timer: 2500,
            showConfirmButton: false
        });
    @endif
</script>
@endsection
