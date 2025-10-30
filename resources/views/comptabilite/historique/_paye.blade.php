@extends('base', [
    'title'       => 'Historique des paiements',
    'breadcrumbs' => ['Administration', 'Comptabilité', 'Historique des paiements'],
    'page_name'   => 'Etat des paiements',
])

@section('content')
<div class="card shadow-sm border-0 rounded-4">
    <div class="card-body">

        <div class="mb-3 d-flex justify-content-between align-items-center">
            <select id="statusFilter" class="form-select w-auto">
                <option value="">Tous</option>
                <option value="total_paye">Soldé</option>
                <option value="a_jour">À jour</option>
                <option value="en_retard">En retard</option>
            </select>

            <!-- Bouton CSV (sera déplacé automatiquement par DataTables) -->
            <div id="exportButtons"></div>
        </div>

        <div class="table-responsive">
            <table id="tablePaye" class="table table-striped table-hover align-middle w-100">
                <thead>
                    <tr>
                        <th>Étudiant</th>
                        <th>Niveau / Filière</th>
                        <th class="text-end">Montant dû (F CFA)</th>
                        <th class="text-end">Payé (F CFA)</th>
                        <th class="text-end">Reste à payer</th>
                        <th>Détails</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($etudiants_infos as $info)
                        @php
                            $mapBtn = [
                                'total_paye' => ['success' , 'bi-check-circle-fill'       , 'Soldé' ],
                                'a_jour'     => ['warning' , 'bi-bell-fill text-dark'    , 'À jour'],
                                'en_retard'  => ['danger'  , 'bi-exclamation-triangle-fill', 'Retard'],
                            ];
                            [$color, $icon, $txt] = $mapBtn[$info['statut']];
                        @endphp
                        <tr data-status="{{ $info['statut'] }}">
                            <td>{{ $info['etudiant']->nom }} {{ $info['etudiant']->prenoms }}</td>
                            <td>{{ $info['niveau'] }} / {{ $info['filiere'] }}</td>
                            <td class="text-end">{{ number_format($info['total_frais'],0,',',' ') }}</td>
                            <td class="text-end">{{ number_format($info['total_paye'],0,',',' ') }}</td>
                            <td class="text-end">{{ number_format($info['reste_a_payer'],0,',',' ') }}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-{{ $color }} rounded-pill d-flex align-items-center gap-1"
                                onclick='voirDetails(@json($info))'>
                                    <i class="bi {{ $icon }}"></i><span>{{ $txt }}</span>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal détails -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content shadow-lg border-0 rounded-4">
      <div id="detailModalHeader" class="modal-header rounded-top">
        <h5 class="modal-title fw-bold" id="detailModalLabel"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body p-4 bg-light">
        <div id="detailContainer" class="row g-4"></div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('other-js')
<!-- Feuilles de style -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css" rel="stylesheet">

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<!-- DataTables Buttons pour export CSV -->
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

<script>
    $(function () {
        $.fn.dataTable.ext.search.push(function (settings, data, idx) {
            const selected = $('#statusFilter').val();   
            if (!selected) return true;
            const rowStatus = $(settings.aoData[idx].nTr).data('status');
            return rowStatus === selected;
        });

        const table = $('#tablePaye').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json' },
            pageLength: 10,
            order: [[0, 'asc']],
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'csvHtml5',
                    text: 'Exporter en CSV',
                    className: 'btn btn-primary mb-2',
                    title: 'Historique des paiements',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4] // exclure le bouton détails
                    }
                }
            ]
        });

        // Déplace le bouton vers un conteneur personnalisé si nécessaire
        table.buttons().container().appendTo('#exportButtons');

        $('#statusFilter').on('change', () => table.draw());
    });

    function voirDetails(info) {
        const data = info;
        if (!data || !data.tranches) return;

        const mapHeader = {
            'total_paye': [' text-white bg-success', 'Paiements soldés'],
            'a_jour'    : [' text-dark bg-warning', 'Paiements à jour'],
            'en_retard' : [' text-white bg-danger', 'Paiements en retard'],
        };
        const [cls, titre] = mapHeader[data.statut];
        const header = document.getElementById('detailModalHeader');
        header.className = 'modal-header rounded-top ' + cls;
        document.getElementById('detailModalLabel').innerText =
            `${titre} – ${data.etudiant.nom} ${data.etudiant.prenom}`;

        let html = '';
        data.tranches.forEach(t => {
            const mapCard = {
                'soldé'     : ['border-success','bg-success-subtle text-success','bi-check-circle-fill','Soldé'],
                'a_jour'    : ['border-warning','bg-warning-subtle text-dark','bi-bell-fill','À jour'],
                'en_retard' : ['border-danger','bg-danger-subtle text-danger','bi-exclamation-triangle-fill','Retard'],
            };
            const [border, bg, icn, lbl] = mapCard[t.status];

            html += `
            <div class="col-md-6">
                <div class="card ${border} border-2 shadow-sm h-100 rounded-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="${bg} p-3 rounded-circle me-3">
                                <i class="bi ${icn} fs-3"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">${t.libelle}</h6>
                                <small class="text-muted">Date limite : ${t.date_limite}</small>
                            </div>
                        </div>
                        <p class="mb-1"><strong>Montant dû :</strong> ${Number(t.montant).toLocaleString()} F CFA</p>
                        <p class="mb-2"><strong>Montant payé :</strong> ${Number(t.paye).toLocaleString()} F CFA</p>
                        <span class="badge ${bg} px-3 py-2 rounded-pill">${lbl}</span>
                    </div>
                </div>
            </div>`;
        });

        document.getElementById('detailContainer').innerHTML = html;
        new bootstrap.Modal(document.getElementById('detailModal')).show();
    }
</script>
@endsection
