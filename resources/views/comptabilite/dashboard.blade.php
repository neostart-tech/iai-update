{{-- resources/views/comptable/dashboard.blade.php --}}
@extends('base', [
    'title' => 'Tableau de bord comptabilité',
])

@section('content')
{{-- ---------- Sélecteur d’année ---------- --}}
<form method="get" class="mb-4">
    <label class="form-label fw-semibold me-2">Année :</label>
    <select name="year" class="form-select d-inline-block w-auto" onchange="this.form.submit()">
        @foreach ($years as $id => $nom)
            <option value="{{ $id }}" @selected($id == $annee->id)>{{ $nom }}</option>
        @endforeach
    </select>
</form>

{{-- ---------- DASHBOARD CARDS ---------- --}}
@php
    $cards = [
        ['Total attendu', $totalAttendu, 'bi-wallet',               'primary'],
        ['Total payé',    $totalPaye,    'bi-cash-stack',           'success'],
        ['Soldés',        $soldes,       'bi-check-circle',         'success'],
        ['À jour',        $ajours,       'bi-bell',                 'warning'],
        ['En retard',     $retards,      'bi-exclamation-triangle', 'danger'],
    ];
@endphp

<div class="row g-4 mb-4">
    @foreach ($cards as $card)
        @php
            list($title, $value, $icon, $color) = $card;
        @endphp
        <div class="col-md-2 col-6">
            <div class="card text-center border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <i class="bi {{ $icon }} fs-2 text-{{ $color }}"></i>
                    <h6 class="mt-2">{{ $title }}</h6>
                    <h5 class="fw-bold">{{ number_format($value, 0, ',', ' ') }}</h5>
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- ---------- GRAPHIQUES ---------- --}}
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card shadow-sm rounded-4">
            <div class="card-header fw-bold">Paiements mensuels ({{ $annee->nom }})</div>
            <div class="card-body"><canvas id="chartMensuel" height="200"></canvas></div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm rounded-4">
            <div class="card-header fw-bold">Répartition par tranche</div>
            <div class="card-body"><canvas id="chartTranche" height="200"></canvas></div>
        </div>
    </div>
</div>


@endsection

@section('other-js')
@parent
<script>
/* --------- Datatable + boutons export --------- */
$(function () {
    $('#tablePaye').DataTable({
        language: {url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json'},
        dom: 'Bfrtip',
        buttons: [
            {
                text: 'Excel',
                className: 'btn btn-outline-success',
                action: () => window.location = '{{ route('comptable.export', [$annee->id, 'xlsx']) }}'
            },
            {
                text: 'CSV',
                className: 'btn btn-outline-primary',
                action: () => window.location = '{{ route('comptable.export', [$annee->id, 'csv']) }}'
            }
        ],
        pageLength: 10
    });
});

/* --------- Graphiques --------- */
const mois = ['', 'Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];

new Chart(document.getElementById('chartMensuel'), {
    type: 'bar',
    data: {
        labels: mois.slice(1),
        datasets: [{
            label: 'Montant payé (F)',
            data: {!! json_encode(array_replace(array_fill(1, 12, 0), $mensuel)) !!},
        }]
    },
    options: {plugins: {legend: {display: false}}}
});

new Chart(document.getElementById('chartTranche'), {
    type: 'doughnut',
    data: {
        labels: {!! json_encode($parTranche->keys()) !!},
        datasets: [{
            data: {!! json_encode($parTranche->values()) !!}
        }]
    },
    options: {plugins: {legend: {position: 'right'}}}
});
</script>
@endsection
