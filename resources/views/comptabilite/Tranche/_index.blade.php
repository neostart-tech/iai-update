@extends('base', [
    'title' => 'Page des tranches',
    'breadcrumbs' => ['Administration', 'Comptabilité', 'Tranche'],
    'page_name' => 'Liste des tranches',
])



@section('content')
@if(session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Succès',
            text: '{{ session('success') }}',
            timer: 3000,
            showConfirmButton: false
        });
    </script>
@endif

@if(session('error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Erreur',
            text: '{{ session('error') }}',
            timer: 5000,
            showConfirmButton: true
        });
    </script>
@endif

<div class="card">
    <div class="card-header">
        <div class="text-end p-4 pb-sm-2 mb-2">
            <button class="btn btn-primary" onclick="openTrancheModal({{ $frais->id }}, {{ $frais->montant }})">
                <i class="ti ti-plus f-18"></i> Ajouter des tranches
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
                        <th>Montant</th>
                        <th>Date limite</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tranches as $t)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $t->libelle }}</td>
                            <td>{{ number_format($t->montant, 0, ',', ' ') }} F</td>
                            <td>{{ \Carbon\Carbon::parse($t->date_limite)->format('d/m/Y') }}</td>
                            <td class="text-center">
                                {{-- <button class="btn btn-warning btn-sm" onclick="modifierTranche({{ $t->id }}, '{{ $t->libelle }}', {{ $t->montant }}, '{{ $t->date_limite }}')">
                                    <i class="ti ti-edit"></i>
                                </button> --}}

                                <button class="btn btn-danger btn-sm" onclick="supprimerTranche({{ $t->id }})">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">Aucune tranche enregistrée.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Ajout multiple tranches --}}
<div class="modal fade" id="multiTrancheModal" tabindex="-1" aria-labelledby="multiTrancheLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form id="multiTrancheForm" method="POST" action="{{ route('comptable.tranche.store') }}">
            @csrf
            <input type="hidden" name="frais_id" id="frais_id" />
            <input type="hidden" name="montant_total" id="montant_total" />
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ajouter plusieurs tranches</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <div id="trancheContainer"></div>
                    <div class="mt-3 text-end">
                        <button type="button" class="btn btn-outline-primary" onclick="ajouterChamp()">+ Ajouter une tranche</button>
                    </div>
                    <div class="mt-2 fw-bold text-danger" id="warningTotal"></div>
                    <div class="mt-2 fw-bold text-danger" id="warningDates"></div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success" onclick="return verifierDates()">Enregistrer</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('other-js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    let trancheIndex = 0;
    let montantTotal = 0;

    function openTrancheModal(fraisId, montant) {
        trancheIndex = 0;
        montantTotal = montant;
        document.getElementById('frais_id').value = fraisId;
        document.getElementById('montant_total').value = montant;
        document.getElementById('trancheContainer').innerHTML = '';
        document.getElementById('warningTotal').innerHTML = '';
        document.getElementById('warningDates').innerHTML = '';
        ajouterChamp(); 
        new bootstrap.Modal(document.getElementById('multiTrancheModal')).show();
    }

    function ajouterChamp() {
        trancheIndex++;
        let container = document.getElementById('trancheContainer');

        let inputsMontant = document.querySelectorAll('.tranche-montant');
        let sommeExistante = Array.from(inputsMontant).reduce((sum, input) => sum + parseInt(input.value || 0), 0);
        let montantRestant = Math.max(0, Math.floor((montantTotal - sommeExistante) / (inputsMontant.length + 1)));

        let html = `
            <div class="row mb-3 tranche-item" data-index="${trancheIndex}">
                <div class="col-md-4">
                    <input type="text" name="tranches[${trancheIndex}][libelle]" class="form-control" placeholder="Libellé tranche" required>
                </div>
                <div class="col-md-3">
                    <input type="number" name="tranches[${trancheIndex}][montant]" value="${montantRestant}" class="form-control tranche-montant" required oninput="verifierMontantTotal()">
                </div>
                <div class="col-md-4">
                    <input type="date" name="tranches[${trancheIndex}][date_limite]" class="form-control tranche-date" required onchange="verifierDates()">
                </div>
                <div class="col-md-1 text-center">
                    <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('.tranche-item').remove(); verifierMontantTotal(); verifierDates();">X</button>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', html);
        verifierMontantTotal();
        verifierDates();
    }

    function verifierMontantTotal() {
        let total = 0;
        document.querySelectorAll('.tranche-montant').forEach(input => {
            total += parseInt(input.value || 0);
        });

        if (total > montantTotal) {
            document.getElementById('warningTotal').innerText = `La somme des tranches (${total} F) dépasse le montant total autorisé (${montantTotal} F).`;
        } else {
            document.getElementById('warningTotal').innerText = '';
        }
    }

    function verifierDates() {
        const dates = Array.from(document.querySelectorAll('.tranche-date'))
            .map(input => input.value)
            .filter(date => date !== "");

        let isValid = true;
        for (let i = 0; i < dates.length - 1; i++) {
            if (new Date(dates[i]) >= new Date(dates[i + 1])) {
                isValid = false;
                break;
            }
        }

        if (!isValid) {
            document.getElementById('warningDates').innerText = "Les dates doivent être croissantes.";
        } else {
            document.getElementById('warningDates').innerText = "";
        }

        return isValid;
    }

    function supprimerTranche(id) {
        Swal.fire({
            title: 'Êtes-vous sûr ?',
            text: "Cette action est irréversible.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler',
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`{{ url('espace-comptable/Tranche/destroy') }}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                }).then(response => {
                    if (response.ok) {
                        Swal.fire('Supprimé!', 'La tranche a été supprimée.', 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Erreur', 'Une erreur est survenue.', 'error');
                    }
                });
            }
        });
    }

    function modifierTranche(id, libelle, montant, date_limite) {
        Swal.fire({
            title: 'Modifier la tranche',
            html: `
                <input id="libelle" class="swal2-input" placeholder="Libellé" value="${libelle}">
                <input id="montant" type="number" class="swal2-input" placeholder="Montant" value="${montant}">
                <input id="date_limite" type="date" class="swal2-input" value="${date_limite}">
            `,
            focusConfirm: false,
            confirmButtonText: 'Enregistrer',
            preConfirm: () => {
                const data = {
                    libelle: document.getElementById('libelle').value,
                    montant: document.getElementById('montant').value,
                    date_limite: document.getElementById('date_limite').value,
                    _method: 'PUT',
                    _token: '{{ csrf_token() }}'
                };

                return fetch(`{{ url('espace-comptable/Tranche/update') }}/${id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(data)
                }).then(response => {
                    if (!response.ok) {
                        return Promise.reject();
                    }
                    return response.json();
                }).catch(() => {
                    Swal.showValidationMessage('Erreur lors de la modification.');
                });
            }
        }).then(result => {
            if (result.isConfirmed) {
                Swal.fire('Modifié!', 'La tranche a été mise à jour.', 'success').then(() => {
                    location.reload();
                });
            }
        });
    }
</script>
@endsection
