
@extends('base', [
'title' => 'Page des années scolaires',
'breadcrumbs' => ['Administration', 'Année scolaire'],
'page_name' => 'Liste des années scolaires',
])

@section('content')
<div class="card">
    {{-- Modal Ajout Année --}}
    <div class="modal fade" id="addFraisModal" tabindex="-1" aria-labelledby="addFraisModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('admin.annescolaire.store') }}">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Nouvelle année</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="annee" class="form-label">Année scolaire</label>
                            <input type="text" name="nom" class="form-control" required>
                            <span>@error('nom') {{ $message }} @enderror</span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Enrégistrer</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Bouton Ajouter --}}
    <div class="card-header">
        <div class="text-end p-4 pb-sm-2 mb-2">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFraisModal">
                <i class="ti ti-plus f-18"></i> Nouvelle année
            </button>
        </div>
    </div>

    {{-- Liste des années --}}
    <div class="card-body">
        <div class="dt-responsive table-responsive">
            <table id="dom-jquery" class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Année scolaire</th>
                        <th>Code</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($annees as $key => $annee)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $annee->nom }}</td>
                        <td>{{ $annee->code }}</td>
                        <td>
                            @if ($annee->active)
                            <span class="badge bg-success">En cours</span>
                            @else
                            <span class="badge bg-secondary">Terminée</span>
                            @endif
                        </td>
                        <td>
                            @if ($annee->active)
                            <a href="#" class="text-danger btn-desactiver" data-id="{{ $annee->id }}"
                                title="Clôturer l'année">
                                <i class="fas fa-times-circle fa-lg"></i>
                            </a>
                            @else
                            <a href="#" class="text-success btn-activer" data-id="{{ $annee->id }}"
                                title="Activer l'année">
                                <i class="fas fa-check-circle fa-lg"></i>
                            </a>
                            @endif
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
    // Activation
    document.querySelectorAll('.btn-activer').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const id = this.dataset.id;

            Swal.fire({
                title: 'Activer cette année ?',
                text: "Cela désactivera toutes les autres années actives.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Oui, activer'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Méthode PUT pour activer
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/administration/annee-scolaire/${id}/activate`;

                    const csrf = document.createElement('input');
                    csrf.type = 'hidden';
                    csrf.name = '_token';
                    csrf.value = '{{ csrf_token() }}';

                    const method = document.createElement('input');
                    method.type = 'hidden';
                    method.name = '_method';
                    method.value = 'PUT';

                    form.appendChild(csrf);
                    form.appendChild(method);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    });

    // Désactivation
    document.querySelectorAll('.btn-desactiver').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const id = this.dataset.id;

            Swal.fire({
                title: 'Désactiver cette année ?',
                text: "Cela mettra fin a l'année mais restera quand meme enrégistré.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Oui, désactiver'
            }).then((result) => {
                if (result.isConfirmed) {
                Swal.fire({
                text: "Traitement en cours...",
                icon: 'info',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }

            })

                  fetch(`/administration/annee-scolaire/${id}/desactivate`, {
    method: 'PUT',
    headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({})
})
.then(response => {
    Swal.close();
    if (response.ok) {
        Swal.fire('Succès', 'Année désactivée avec succès', 'success')
        .then(() => {
            location.reload();
        });
           
    } else {
        Swal.fire('Erreur', 'Impossible de désactiver l\'année', 'error');
    }
    
});

                }
            });
        });
    });
// 
    // SweetAlert pour messages de session
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Succès',
text: {!! json_encode(session('success')) !!},
            confirmButtonColor: '#198754'
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Erreur',
text: {!! json_encode(session('success')) !!},
            confirmButtonColor: '#dc3545'
        });
    @endif
</script>
@endsection
