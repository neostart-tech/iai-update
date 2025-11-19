@extends('professeurs.base', [
    'title' => 'Mon emploi de temps',
    'page_name' => 'Emploi de temps',
    'breadcrumbs' => ['Mon emploi de temps', 'Mes cours'],
])
@section('bases')
<div class="card">
    <div class="card-header">
        <h5>Liste de vos cours</h5>
    </div>
    <div class="card-body">
        <div class="dt-responsive table-responsive">
            <table id="dom-jquery" class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Matière</th>
                        <th>Salle</th>
                        <th>Jour</th>
                        <th>Heure</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody id="cours-tbody">
                    {{-- Les lignes seront injectées par JS via AJAX --}}
                </tbody>
                <tfoot>
                    <tr>
                        <th>#</th>
                        <th>Matière</th>
                        <th>Salle</th>
                        <th>Jour</th>
                        <th>Heure</th>
                        <th class="text-center">Action</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection

@section('other-js')
<script>
    fetch("{{ url('/espace-enseignant/espace-professeur/mes-cours') }}")
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('cours-tbody');
            let count = 1;
            data.forEach(item => {
                if(item.extendedProps.type_programme === 'Cours') {
                    const tr = document.createElement('tr');

                    const date = new Date(item.start);
                    const end = new Date(item.end);
                    const options = { year: 'numeric', month: 'long', day: 'numeric' };
                    const dateFormatted = date.toLocaleDateString('fr-FR', options);
                    const heure = date.toLocaleTimeString('fr-FR', {hour:'2-digit',minute:'2-digit'}) 
                                  + ' - ' + end.toLocaleTimeString('fr-FR', {hour:'2-digit',minute:'2-digit'});

                    tr.innerHTML = `
                        <th scope="row">${count++}</th>
                        <td>${item.extendedProps.matiere}</td>
                        <td>${item.extendedProps.salle}</td>
                        <td>${dateFormatted}</td>
                        <td>${heure}</td>
                        <td class="text-center">
                            <a href="/espace-enseignant/presence/vue/${item.extendedProps.emploi_du_temps_id}" 
                               class="btn btn-sm btn-info" title="Voir présence">
                                <i class="ti ti-eye"></i>
                            </a>
                        </td>
                    `;
                    tbody.appendChild(tr);
                }
            });
        });
</script>
@endsection
