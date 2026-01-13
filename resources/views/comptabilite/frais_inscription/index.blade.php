@php
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Support\Facades\Storage;
@endphp
@extends('base', [
'title' => 'Page des frais de scolarité',
'breadcrumbs' => ['Administration', 'Comptabilité', 'Frais'],
'page_name' => 'Liste des frais d\'inscription',
])

@section('content')
<div class="card">
    {{-- Modal Ajout Frais --}}
    <div class="modal fade" id="addFraisModal" tabindex="-1" aria-labelledby="addFraisModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('comptable.frais-inscription.store') }}">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addFraisModalLabel">Nouveau frais d'inscription</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">

                        <div class="mb-3">
                            <label for="montant" class="form-label">Montant</label>
                            <input type="number" name="montant" class="form-control" required>
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

    <div class="modal fade" id="deleteFraisModal" tabindex="-1" aria-labelledby="deleteFraisModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" id="deleteFraisForm">
                @csrf
                @method('DELETE')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteFraisModalLabel">Suppression</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">
                        Voulez-vous vraiment supprimer ce frais de scolarité ?
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-warning">Supprimer</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edition Frais --}}
    <div class="modal fade" id="editFraisModal" tabindex="-1" aria-labelledby="editFraisModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" id="editFraisForm">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editFraisModalLabel">Modifier frais d'inscription</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">

                      
                       
                        <div class="mb-3">
                            <label for="edit_montant" class="form-label">Montant</label>
                            <input type="number" name="montant" id="edit_montant" class="form-control" required>
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

    <div class="card-header">
        <div class="text-end p-4 pb-sm-2 mb-2">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFraisModal">
                <i class="ti ti-plus f-18"></i> Nouveau frais
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="dt-responsive table-responsive">
            <table class="table table-striped table-hover table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Montant</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @if($frais->count() > 0)
                    @foreach ($frais as $f)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ number_format($f->montant, 0, ',', ' ') }} F</td>
                        <td class="text-center">
                            <ul class="list-inline me-auto mb-0">
                                <li class="list-inline-item align-bottom" data-bs-toggle="tooltip"
                                    title="Edit">
                                    <a href="#" class="avtar avtar-xs btn-link-secondary btn-pc-default"
                                        onclick="editFrais({{ $f->id }}, {{ $f->montant }})">
                                        <i class="ti ti-edit-circle f-18"></i>
                                    </a>
                                </li>
                                <li class="list-inline-item align-bottom" data-bs-toggle="tooltip"
                                    title="Delete">
                                    <a href="#" class="avtar avtar-xs btn-link-danger btn-pc-default"
                                        onclick="deleteFrais({{ $f->id }})" data-bs-toggle="modal"
                                        data-bs-target="#deleteFraisModal">
                                        <i class="ti ti-trash f-18"></i>
                                    </a>
                                </li>

                              
                            </ul>
                        </td>
                    </tr>
                    @endforeach
                    @else
                    <tr>
                        <td colspan="3" class="text-center">Aucun frais d'inscription enregistré.</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('other-js')
<script>
    function deleteFrais(id) {
        let form = document.getElementById('deleteFraisForm');
        form.action = "{{ url('espace-comptable/frais-inscription/destroy') }}/" + id;
    }

    function editFrais(id, montant) {
        let form = document.getElementById('editFraisForm');
        form.action = "{{ url('espace-comptable/frais-inscription/update') }}/" + id;
        document.getElementById('edit_montant').value = montant;
        new bootstrap.Modal(document.getElementById('editFraisModal')).show();
    }
</script>
@endsection