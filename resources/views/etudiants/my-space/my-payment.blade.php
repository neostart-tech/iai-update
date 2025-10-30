@extends('base', [
    'title' => 'Mon dossier',
    'page_name' => 'Paiement des frais de scolarité',
    'breadcrumbs' => ['Frais de scolarité', 'Mes paiements'],
])

@section('content')


    {{-- Bouton pour ouvrir la modale --}}
    @if (!$Ajour)
        <div class="mb-4">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalPaiement">
                Effectuer un paiement
            </button>
        </div>
    @else
    @endif


    {{-- Tableau des paiements --}}
    @forelse($paiementsParAnnee as $annee => $paiements)
        <div class="card mb-3">
            <div class="card-header bg-light">
                <strong>Paiements pour l'année scolaire : {{ $annee }}</strong>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Tranche</th>
                                <th>Montant</th>
                                <th>Mode</th>
                                <th>Référence</th>
                                <th>Date</th>
                                <th>Statut</th>
                                <th>Reçu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($paiements as $paiement)
                                <tr>
                                    <td>{{ $paiement->tranchePaiement->libelle ?? '-' }}</td>
                                    <td>{{ number_format($paiement->montant, 0, ',', ' ') }} FCFA</td>
                                    <td>{{ ucfirst($paiement->mode_paiement) }}</td>
                                    <td>{{ $paiement->reference ?? '-' }}</td>
                                    <td>{{ $paiement->date_paiement ? \Carbon\Carbon::parse($paiement->date_paiement)->format('d/m/Y H:i') : '-' }}
                                    </td>
                                    <td>
                                        @if ($paiement->status === 'valide')
                                            <span class="badge bg-success">Validé</span>
                                        @elseif($paiement->status === 'en_attente')
                                            <span class="badge bg-warning text-dark">En attente</span>
                                        @elseif($paiement->status === 'rejete')
                                            <span class="badge bg-danger">Rejeté</span>
                                        @else
                                            <span class="badge bg-secondary">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($paiement->recu)
                                            <a href="{{ asset($paiement->recu) }}" target="_blank"
                                                class="btn btn-sm btn-primary">Voir reçu</a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">Aucun paiement enregistré pour cette année.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @empty
        <tr>
            <td colspan="3">No data</td>
        </tr>
    @endforelse

    {{-- Modale de paiement --}}
    <div class="modal fade" id="modalPaiement" tabindex="-1" aria-labelledby="modalPaiementLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('etudiants.semoa.payments.process') }}">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalPaiementLabel">Effectuer un paiement</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="lastname" class="form-label">Nom</label>
                            <input type="text" class="form-control" id="lastname" name="lastname"
                                value="{{ auth()->user()->nom }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="firstname" class="form-label">Prénom</label>
                            <input type="text" class="form-control" id="firstname" name="firstname"
                                value="{{ auth()->user()->prenom }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Téléphone (format international)</label>
                            <input type="tel" class="form-control" id="phone" name="phone" required
                                placeholder="+2289xxxxxxx" pattern="^\+228\d{8}$"
                                title="Le numéro doit commencer par +228 suivi de 8 chiffres">
                        </div>
                        <div class="mb-3">
                            <label for="amount" class="form-label">Montant (FCFA)</label>
                            <input type="number" class="form-control" id="amount" name="amount" min="100"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="payment_method" class="form-label">Mode de paiement</label>
                            <select name="payment_method" id="payment_method" class="form-select" required>
                                <option value="016eb63c-f29d-4384-92e4-b1bd37ef69f8">Flooz TG</option>
                                <option value="a2c87957-1033-46e9-8706-056e45737de1">Mix By Yas</option>
                                <option value="14f4597d-ef96-4263-8107-1e1970959133">SANDBOX</option>
                                <option value="Ecobank-Mobile">Ecobank</option>
                                <option value="f7bbfaef-eba3-4b82-ac31-61eb2b772289">Orabank</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Payer maintenant</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection
