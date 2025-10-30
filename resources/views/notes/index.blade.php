@extends('base', [
    'title' => 'Mes notes',
    'page_name' => 'Mes notes',
    'breadcrumbs' => ['Notes'],
])

@section('content')
    <div class="card">
        <div class="card-body">
            @if ($notes->isNotEmpty())
                <div class="dt-responsive table-responsive">
                    <table id="dom-jquery" class="table table-striped table-hover table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>UE</th>
                                <th>UV</th>
                                <th>Type</th>
                                <th>Note</th>
                                <th>Réclamer</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($notes as $key => $note)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $note->evaluation->uniteValeur->uniteEnseignement->nom }}</td>
                                    <td>{{ $note->evaluation->uniteValeur->nom }}</td>
                                    <td>{{ $note->evaluation->type }}</td>
                                    <td>{{ $note->notation === 'R' ? 'R' : ($note->note ?? 'Non disponible') }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                            data-bs-target="#reclamerNoteModal" data-note-id="{{ $note->id }}"
                                            data-evaluation-id="{{ $note->evaluation_id }}">
                                            Réclamer
                                        </button>
                                    </td>
                                    {{-- <td class="text-center">
                  <button type="button" class="btn btn-sm {{ $note->note !== null ? 'btn-warning' : 'btn-danger' }}"
                          data-bs-toggle="modal"
                          data-bs-target="#modalReclam"
                          data-type="{{ $note->note !== null ? 'note_incorrecte' : 'note_absente' }}"
                          data-evaluation="{{ $note->evaluation->id }}"
                          data-matiere="{{ $note->evaluation->uniteValeur->id }}"
                          data-note="{{ $note->note }}">
                    {{ $note->note !== null ? 'Réclamer' : 'Note manquante' }}
                  </button>
                </td> --}}
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

    <!-- Modal commun -->
   <!-- Modal Réclamation -->
<div class="modal fade" id="reclamerNoteModal" tabindex="-1" aria-labelledby="reclamerNoteLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="reclamationForm" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title">Réclamation de note</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="note_id" name="note_id">
                    <div class="mb-3">
                        <label for="motif" class="form-label">Motif *</label>
                        <textarea name="motif" id="motif" class="form-control" maxlength="500" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="justificatif" class="form-label">Justificatif (facultatif)</label>
                        <input type="file" name="justificatif" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-warning">Envoyer</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@section('other-js')
    @include('layouts.admin._dt-scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
 <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const modal = document.getElementById('reclamerNoteModal');
    modal.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget;
        const noteId = button.getAttribute('data-note-id');
        document.getElementById('note_id').value = noteId;
    });

    document.getElementById('reclamationForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        const noteId = document.getElementById('note_id').value;

    fetch(`/espace-etudiant/reclamations/${noteId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            let modalInstance = bootstrap.Modal.getInstance(modal);
            modalInstance.hide();

            if (data.status === 'success') {
                Swal.fire('Succès', data.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('Erreur', data.message, 'error');
            }
        })
        .catch(error => {
            console.error(error);
            Swal.fire('Erreur', 'Une erreur est survenue.', 'error');
        });
    });
</script>
@endsection

@section('other-css')
    <link rel="stylesheet" href="{{ asset('admin/assets/css/plugins/dataTables.bootstrap5.min.css') }}">
@endsection
