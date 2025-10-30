@php
    use Illuminate\Database\Eloquent\Casts\Json;
    use Illuminate\Support\Facades\Storage;
@endphp
@extends('base', [
    'title' => 'Page des frais de scolarité',
    'breadcrumbs' => ['Administration', 'Comptabilité', 'Frais'],
    'page_name' => 'Liste des frais de scolarité',
])

@section('content')
<div class="card">
    {{-- Modal Ajout Frais --}}
    <div class="modal fade" id="addFraisModal" tabindex="-1" aria-labelledby="addFraisModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('comptable.frais.store') }}">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addFraisModalLabel">Nouveau frais de scolarité</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="niveau_id" class="form-label">Niveau</label>
                            <select name="niveau_id" class="form-control" required>
                                @foreach($niveaux as $niveau)
                                    <option value="{{ $niveau->id }}">{{ $niveau->libelle }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="genre" class="form-label">Genre <span class="text-danger">*</span></label>
                            <select name="genre" class="form-control" required>
                                <option value="">-- Sélectionner --</option>
                                <option value="Tous">Tous (Hommes et Femmes)</option>
                                <option value="Masculin">Hommes uniquement</option>
                                <option value="Féminin">Femmes uniquement</option>
                            </select>
                            <small class="form-text text-muted">Définissez si ces frais s'appliquent à un genre spécifique</small>
                        </div>
                        <div class="mb-3">
                            <label for="montant" class="form-label">Montant</label>
                            <input type="number" name="montant" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description (optionnel)</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Ex: Frais spéciaux pour les étudiantes..."></textarea>
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

    {{-- Modal Suppression --}}
    <div class="modal fade" id="deleteFraisModal" tabindex="-1" aria-labelledby="deleteFraisModalLabel" aria-hidden="true">
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
                        <h5 class="modal-title" id="editFraisModalLabel">Modifier frais de scolarité</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_annee_scolaire_id" class="form-label">Année scolaire</label>
                            <select name="annee_scolaire_id" id="edit_annee_scolaire_id" class="form-control" required>
                                @foreach($annees as $annee)
                                    <option value="{{ $annee->id }}">{{ $annee->libelle }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_niveau_id" class="form-label">Niveau</label>
                            <select name="niveau_id" id="edit_niveau_id" class="form-control" required>
                                @foreach($niveaux as $niveau)
                                    <option value="{{ $niveau->id }}">{{ $niveau->libelle }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_genre" class="form-label">Genre <span class="text-danger">*</span></label>
                            <select name="genre" id="edit_genre" class="form-control" required>
                                <option value="">-- Sélectionner --</option>
                                <option value="Tous">Tous (Hommes et Femmes)</option>
                                <option value="Masculin">Hommes uniquement</option>
                                <option value="Féminin">Femmes uniquement</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_montant" class="form-label">Montant</label>
                            <input type="number" name="montant" id="edit_montant" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Description (optionnel)</label>
                            <textarea name="description" id="edit_description" class="form-control" rows="2"></textarea>
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
                        <th>Année scolaire</th>
                        <th>Niveau</th>
                        <th>Genre</th>
                        <th>Montant</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($frais as $f)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $f->anneeScolaire->nom ?? '' }}</td>
                            <td>{{ $f->niveau->libelle ?? '' }}</td>
                            <td>
                                <span class="badge {{ $f->genre == 'Masculin' ? 'bg-primary' : ($f->genre == 'Féminin' ? 'bg-pink' : 'bg-secondary') }}">
                                    {{ $f->genre == 'Masculin' ? '👨 Hommes' : ($f->genre == 'Féminin' ? '👩 Femmes' : '👥 Tous') }}
                                </span>
                            </td>
                            <td>{{ number_format($f->montant, 0, ',', ' ') }} F</td>
                            <td>{{ $f->description ?? '-' }}</td>
                            <td class="text-center">
                                <ul class="list-inline me-auto mb-0">
                                    <li class="list-inline-item align-bottom" data-bs-toggle="tooltip" title="Edit">
                                        <a href="#" class="avtar avtar-xs btn-link-success btn-pc-default"
                                            onclick="editFrais({{ $f->id }}, {{ $f->annee_scolaire_id }}, {{ $f->niveau_id }}, '{{ $f->genre }}', {{ $f->montant }}, '{{ addslashes($f->description ?? '') }}')"
                                            <i class="ti ti-edit-circle f-18"></i>
                                        </a>
                                    </li>
                                    <li class="list-inline-item align-bottom" data-bs-toggle="tooltip" title="Delete">
                                        <a href="#" class="avtar avtar-xs btn-link-danger btn-pc-default"
                                            onclick="deleteFrais({{ $f->id }})" data-bs-toggle="modal" data-bs-target="#deleteFraisModal">
                                            <i class="ti ti-trash f-18"></i>
                                        </a>
                                    </li>

                                    <li class="list-inline-item align-bottom" data-bs-toggle="tooltip" title="Tranches">
            
                                      <a href="{{ route('comptable.frais.show', $f->id) }}" class="avtar avtar-xs btn-link-info btn-pc-default">
<svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-eye"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg>
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
<script>
    function deleteFrais(id) {
        let form = document.getElementById('deleteFraisForm');
        form.action = "{{ url('espace-comptable/frais/destroy') }}/" + id;
    }

    function editFrais(id, annee_id, niveau_id, genre, montant, description) {
        let form = document.getElementById('editFraisForm');
        form.action = "{{ url('espace-comptable/frais/update') }}/" + id;
        document.getElementById('edit_annee_scolaire_id').value = annee_id;
        document.getElementById('edit_niveau_id').value = niveau_id;
        document.getElementById('edit_genre').value = genre;
        document.getElementById('edit_montant').value = montant;
        document.getElementById('edit_description').value = description;
        new bootstrap.Modal(document.getElementById('editFraisModal')).show();
    }
</script>
@endsection
