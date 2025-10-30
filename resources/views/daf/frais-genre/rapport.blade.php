@extends('base', [
    'title' => 'Rapport frais par genre - DAF',
    'breadcrumbs' => ['DAF', 'Paramétrage', 'Rapport frais genre'],
    'page_name' => 'Rapport des frais différenciés par genre',
])

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="ti ti-chart-bar"></i> Analyse des Frais par Genre</h5>
                <div>
                    <a href="{{ route('daf.frais-genre.index') }}" class="btn btn-outline-secondary">
                        <i class="ti ti-arrow-left"></i> Retour
                    </a>
                    <button class="btn btn-success" onclick="exportRapport()">
                        <i class="ti ti-download"></i> Exporter PDF
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th rowspan="2" class="text-center">Niveau</th>
                                <th colspan="3" class="text-center">Montants par Genre</th>
                                <th rowspan="2" class="text-center">Différence<br><small>(H - F)</small></th>
                                <th rowspan="2" class="text-center">Statut</th>
                            </tr>
                            <tr>
                                <th class="text-center">👨 Hommes</th>
                                <th class="text-center">👩 Femmes</th>
                                <th class="text-center">👥 Mixte</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rapports as $rapport)
                                <tr>
                                    <td class="fw-bold">{{ $rapport['niveau']->libelle }}</td>
                                    <td class="text-center">
                                        @if($rapport['frais_hommes'])
                                            <span class="badge bg-primary">
                                                {{ number_format($rapport['frais_hommes']->montant, 0, ',', ' ') }} F
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($rapport['frais_femmes'])
                                            <span class="badge bg-pink">
                                                {{ number_format($rapport['frais_femmes']->montant, 0, ',', ' ') }} F
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($rapport['frais_mixtes'])
                                            <span class="badge bg-secondary">
                                                {{ number_format($rapport['frais_mixtes']->montant, 0, ',', ' ') }} F
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($rapport['difference'] != 0)
                                            <span class="badge {{ $rapport['difference'] > 0 ? 'bg-warning' : 'bg-info' }}">
                                                {{ $rapport['difference'] > 0 ? '+' : '' }}{{ number_format($rapport['difference'], 0, ',', ' ') }} F
                                            </span>
                                        @else
                                            <span class="text-muted">0 F</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($rapport['frais_hommes'] && $rapport['frais_femmes'])
                                            <span class="badge bg-success">✅ Complet</span>
                                        @elseif($rapport['frais_mixtes'])
                                            <span class="badge bg-info">🔄 Mixte</span>
                                        @else
                                            <span class="badge bg-danger">❌ Manquant</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques d'analyse -->
    <div class="col-md-6 mt-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="ti ti-chart-pie"></i> Répartition par Type</h5>
            </div>
            <div class="card-body">
                <canvas id="chartRepartition" height="200"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-6 mt-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="ti ti-trending-up"></i> Comparaison des Montants</h5>
            </div>
            <div class="card-body">
                <canvas id="chartComparaison" height="200"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@section('other-js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Données pour les graphiques
    const rapports = @json($rapports);
    
    // Graphique en secteurs pour la répartition
    const ctxRepartition = document.getElementById('chartRepartition').getContext('2d');
    const chartRepartition = new Chart(ctxRepartition, {
        type: 'pie',
        data: {
            labels: ['Frais Hommes', 'Frais Femmes', 'Frais Mixtes'],
            datasets: [{
                data: [
                    rapports.filter(r => r.frais_hommes).length,
                    rapports.filter(r => r.frais_femmes).length,
                    rapports.filter(r => r.frais_mixtes).length
                ],
                backgroundColor: ['#007bff', '#e83e8c', '#6c757d']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    
    // Graphique en barres pour la comparaison
    const ctxComparaison = document.getElementById('chartComparaison').getContext('2d');
    const chartComparaison = new Chart(ctxComparaison, {
        type: 'bar',
        data: {
            labels: rapports.map(r => r.niveau.libelle),
            datasets: [
                {
                    label: '👨 Hommes',
                    data: rapports.map(r => r.frais_hommes ? r.frais_hommes.montant : 0),
                    backgroundColor: '#007bff'
                },
                {
                    label: '👩 Femmes',
                    data: rapports.map(r => r.frais_femmes ? r.frais_femmes.montant : 0),
                    backgroundColor: '#e83e8c'
                },
                {
                    label: '👥 Mixte',
                    data: rapports.map(r => r.frais_mixtes ? r.frais_mixtes.montant : 0),
                    backgroundColor: '#6c757d'
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat('fr-FR').format(value) + ' F';
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    
    function exportRapport() {
        // Fonction d'export à implémenter
        Swal.fire('Info', 'Export PDF à implémenter', 'info');
    }
</script>
@endsection