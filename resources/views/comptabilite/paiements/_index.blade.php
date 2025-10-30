@php
    use Illuminate\Support\Facades\Storage;
@endphp

@extends('base', [
    'title' => 'Page de paiement des étudiants',
    'breadcrumbs' => ['Administration', 'Comptabilité', 'Paiement'],
    'page_name' => 'Liste des paiements',
])

@section('content')

    @if (session('success'))
        <div class="alert alert-success">
            <strong>{{ session('success') }}</strong>
            @if (session('recap'))
                <ul class="mb-0 mt-2">
                    @foreach (session('recap') as $r)
                        <li>{{ $r['tranche'] }} : {{ number_format($r['montant'], 0, ',', ' ') }} FCFA</li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            <strong>{{ session('error') }}</strong>
        </div>
    @endif

    <div class="card">

        {{-- Modal Ajout Paiement --}}
        <div class="modal fade" id="addFraisModal" tabindex="-1" aria-labelledby="addFraisModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" action="{{ route('comptable.paiement.store') }}">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Nouveau Paiement</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                        </div>
                        <div class="modal-body">
                            {{-- Étudiant --}}
                            <div class="mb-3">
                                <label class="form-label">Étudiant</label>
                                <select class="form-select" name="etudiant_id" data-trigger id="etudiant-select" required>
                                    <option value="">-- Sélectionner un étudiant --</option>
                                    @foreach ($etudiants as $etudiant)
                                        <option value="{{ $etudiant->id }}">
                                            {{ $etudiant->matricule }} - {{ $etudiant->nom }} {{ $etudiant->prenom }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Tranche --}}
                            {{-- <div class="mb-3">
                            <label class="form-label">Tranche de paiement</label>
                            <select class="form-select" name="tranche_paiement_id" id="tranche-select" required>
                                <option value="">-- Sélectionner un étudiant d'abord --</option>
                            </select>
                        </div> --}}

                            {{-- Mode paiement --}}
                            <div class="mb-3">
                                <label class="form-label">Mode de paiement</label>
                                <select class="form-select" name="mode_paiement" id="mode-paiement" required>
                                    <option value="">-- Choisir --</option>
                                    <option value="semoa">SEMOA</option>
                                    <option value="caisse">Paiement à la caisse</option>
                                    <option value="banque">Paiement à la banque</option>
                                </select>
                            </div>

                            {{-- Montant --}}
                            <div class="mb-3">
                                <label class="form-label">Montant</label>
                                <input type="number" name="montant" class="form-control" required>
                            </div>
                            <div class="mb-3" id="reference-field" style="display: none;">
                                <label class="form-label">Référence</label>
                                <input type="text" name="reference" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Justificatif</label>
                                <input type="file" name="justificatif" class="form-control">
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

        {{-- Bouton --}}
        <div class="card-header">
            <div class="text-end p-4 pb-sm-2 mb-2">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFraisModal">
                    <i class="ti ti-plus"></i> Nouveau paiement
                </button>
            </div>
        </div>

        {{-- Tableau --}}
        <div class="card-body">
            <div class="dt-responsive table-responsive">
                <table id="dom-jquery" class="table table-striped table-hover table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Étudiant</th>
                            <th>Montant</th>
                            <th>Mode</th>
                            <th>Référence</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($paiements as $paiement)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $paiement->etudiant->nom }} {{ $paiement->etudiant->prenom }}</td>
                                <td>{{ number_format($paiement->montant, 0, ',', ' ') }} FCFA</td>
                                <td>{{ ucfirst($paiement->mode_paiement) }}</td>
                                <td>{{ $paiement->reference ?? '-' }}</td>
                                <td>{{ $paiement->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    @if ($paiement->recu)
                                        <a href="{{ asset($paiement->recu) }}" class="btn btn-sm btn-success"
                                            target="_blank">
                                            <i class="ti ti-file-text"></i>
                                        </a>
                                    @endif

                                    @if ($paiement->status === 'valide')
                                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                            data-bs-target="#annulerPaiementModal"
                                            onclick="setPaiementId({{ $paiement->id }})">
                                            <i class="ti ti-x"></i>
                                        </button>
                                    @elseif ($paiement->status === 'en_attente')
                                        <button title="Validation" class="btn btn-sm btn-warning"
                                            onclick="ValiderPaiement({{ $paiement->id }})">
                                            <i class="ti ti-check"></i>
                                        </button>
                                    @else
                                        <button class="btn btn-sm btn-danger" disabled style='cursor:zoom-out'
                                            data-bs-toggle="modal" data-bs-target="#annulerPaiementModal"
                                            onclick="setPaiementId({{ $paiement->id }})">
                                            <i class="ti ti-x"></i>
                                        </button>
                                    @endif

                                    <button class="btn btn-sm btn-info" data-bs-toggle="modal"
                                        data-bs-target="#voirPaiementModal"
                                        onclick='showPaiementDetails(@json($paiement))'>
                                        <i class="ti ti-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Aucun paiement enregistré</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- Modal d’annulation --}}
    <div class="modal fade" id="annulerPaiementModal" tabindex="-1" aria-labelledby="annulerPaiementModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('comptable.paiement.annuler') }}">
                @csrf
                <input type="hidden" name="paiement_id" id="paiement_id_annulation">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Annuler ce paiement</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="motif_annulation" class="form-label">Motif d'annulation</label>
                            <textarea name="motif_annulation" class="form-control" required rows="4"
                                placeholder="Ex: Erreur de saisie, double paiement..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-danger" type="submit">
                            <i class="ti ti-check"></i> Confirmer l’annulation
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal de détails --}}
    <div class="modal fade" id="voirPaiementModal" tabindex="-1" aria-labelledby="voirPaiementModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Détails du paiement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Étudiant</label>
                            <input type="text" class="form-control" id="detail-etudiant" readonly>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Montant</label>
                            <input type="text" class="form-control" id="detail-montant" readonly>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Mode de paiement</label>
                            <input type="text" class="form-control" id="detail-mode" readonly>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Référence</label>
                            <input type="text" class="form-control" id="detail-reference" readonly>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Date paiement</label>
                            <input type="text" class="form-control" id="detail-date" readonly>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Statut</label>
                            <input type="text" class="form-control" id="detail-statut" readonly>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Motif annulation</label>
                            <input type="text" class="form-control" id="detail-motif" readonly>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Date annulation</label>
                            <input type="text" class="form-control" id="detail-date-annulation" readonly>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Auteur</label>
                            <input type="text" class="form-control" id="detail-auteur" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('other-js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ asset('admin/assets/js/plugins/choices.min.js') }}"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    @include('layouts.admin._dt-scripts')
    <link rel="stylesheet" href="{{ asset('admin/assets/css/plugins/dataTables.bootstrap5.min.css') }}">
@endsection

<script>
    function setPaiementId(id) {
        document.getElementById('paiement_id_annulation').value = id;
    }

    function showPaiementDetails(paiement) {
        document.getElementById('detail-etudiant').value = (paiement.etudiant?.nom ?? '') + ' ' + (paiement.etudiant
            ?.prenom ?? '');
        document.getElementById('detail-montant').value = Number(paiement.montant ?? 0).toLocaleString() + ' FCFA';
        document.getElementById('detail-mode').value = paiement.mode_paiement ?? '-';
        document.getElementById('detail-reference').value = paiement.reference ?? '-';
        document.getElementById('detail-date').value = paiement.date_paiement ?? '-';
        document.getElementById('detail-statut').value = paiement.annule ? 'Annulé' : 'Validé';
        document.getElementById('detail-motif').value = paiement.motif_annulation ?? '-';
        document.getElementById('detail-date-annulation').value = paiement.date_annulation ?? '-';
        document.getElementById('detail-auteur').value = (paiement.user?.nom ?? '') + ' ' + (paiement.user?.prenom ??
            '');
    }



    function ValiderPaiement(id) {
        Swal.fire({
            title: 'Êtes-vous sûr de confirmer ce paiement ?',
            text: "Les autres paiements avec la meme référence seront valides aussi.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Oui, valider',
            cancelButtonText: 'Annuler',
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`{{ url('espace-comptable/paiement/valider') }}/${id}`, {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                }).then(response => {
                    if (response.ok) {
                        Swal.fire('validé!', 'Le paiement a été validé.', 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Erreur', 'Une erreur est survenue.', 'error');
                    }
                });
            }
        });
    }
    document.addEventListener('DOMContentLoaded', function() {
        const modePaiement = document.getElementById('mode-paiement');
        const referenceField = document.getElementById('reference-field');

        modePaiement.addEventListener('change', function() {
            if (this.value === 'banque') {
                referenceField.style.display = 'block';
            } else {
                referenceField.style.display = 'none';
                referenceField.querySelector('input').value = ''; // vider le champ
            }
        });
    });
</script>

<style>
    #voirPaiementModal .form-label {
        font-weight: 600;
        color: #555;
    }

    #voirPaiementModal .form-control[readonly] {
        background: #f8f9fa;
        border: 1px solid #e3e3e3;
    }
</style>
