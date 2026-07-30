@extends('base', [
    'title' => 'Mon dossier',
    'page_name' => 'Paiement des frais de scolarité',
    'breadcrumbs' => ['Frais de scolarité', 'Mes paiements'],
])

@section('content')


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

@endsection
