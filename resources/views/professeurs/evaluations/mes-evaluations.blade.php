@extends('professeurs.base', [
    'title' => 'Mon emploi de temps',
    'page_name' => 'Emploi de temps',
    'breadcrumbs' => ['Mon emploi de temps', 'Mes cours'],
])
@section('bases')

<div class="row">

    <div class="col-12 mt-4">
        <div class="card">
            <div class="card-header"><h5>Évaluations / Examens / Devoirs</h5></div>
            <div class="card-body">
                <div class="dt-responsive table-responsive">
                    <table id="table-evaluations" class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Matière</th>
                                <th>Type</th>
                                <th>Salle</th>
                                <th>Jour</th>
                                <th>Heure</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="eval-tbody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection


@section('other-js')
<script>
    fetch("{{ url('/espace-enseignant/espace-professeur/mes-cours') }}")
        .then(response => response.json())
        .then(data => {

            const tbodyCours = document.getElementById('cours-tbody');
            const tbodyEval = document.getElementById('eval-tbody');

            let countCours = 1;
            let countEval = 1;

            data.forEach(item => {

                const start = new Date(item.start);
                const end = new Date(item.end);

                const dateFormatted = start.toLocaleDateString('fr-FR', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });

                const heure = start.toLocaleTimeString('fr-FR', {hour:'2-digit', minute:'2-digit'}) 
                            + ' - ' 
                            + end.toLocaleTimeString('fr-FR', {hour:'2-digit', minute:'2-digit'});


                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${countEval++}</td>
                        <td>${item.extendedProps.matiere}</td>
                        <td>${item.extendedProps.type_programme}</td>
                        <td>${item.extendedProps.salle}</td>
                        <td>${dateFormatted}</td>
                        <td>${heure}</td>

                        <td class="text-center">
                            <a href="/espace-enseignant/evaluation/programmer/${item.extendedProps.emploi_du_temps_id}" 
                               class="btn btn-sm btn-warning">
                                <i class="ti ti-file-plus"></i> 
                            </a>

                            <a href="/espace-enseignant/evaluation/soumissions/${item.extendedProps.emploi_du_temps_id}" 
                               class="btn btn-sm btn-success">
                                <i class="ti ti-check"></i> 
                            </a>
                             <a href="/espace-enseignant/evaluations/${item.extendedProps.emploi_du_temps_id}/create-question-evaluation" 
                               class="btn btn-sm btn-success">
                                <i class="ti ti-check"></i> 
                            </a>


                           
                        </td>
                    `;
                    tbodyEval.appendChild(tr);
                
            });

        });
</script>
@endsection
