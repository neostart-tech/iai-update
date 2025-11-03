
@extends('base', [
    'title' => 'Mes relevés',
    'page_name' => 'Voir mon relevé de notes',
    'breadcrumbs' => ['Mes relevés', 'Mes informations'],
])

{{-- {{ dd($candidature) }}  --}}

@section('content')
    <style>
        @media print {
            body {
                margin: 0;
                padding: 0;
                font-size: 11px;
            }

            .modal {
                display: none !important;
            }

            .table {
                font-size: 11px;
            }

            .no-print {
                display: none;
            }

            #releveToPrint {
                width: 100%;
                page-break-inside: avoid;
            }
        }

        .releve-header {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 15px;
            padding: 10px;
            border: 1px solid #dee2e6;
            margin-bottom: 10px;
            font-size: 13px;
        }

        .releve-header p {
            margin: 0;
        }

        .releve-summary table {
            font-size: 12px;
        }

        .releve-signature {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            font-size: 12px;
        }

        .table td,
        .table th {
            padding: 4px;
        }
    </style>
    <div class="card">
        <div class="card-body">
            <div 
               >
                <div class="">
                    <div class="modal-content" style=" overflow-y: auto;">
                        <div class="modal-header">
                         
                        </div>
                        <div class="modal-body">
                            <div id="releveModalBody"></div>
                            {{-- <div class="text-end mt-3 no-print">
          <button class="btn btn-outline-primary" onclick="printReleve()">Imprimer</button>
        </div> --}}
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
<script>
 document.addEventListener('DOMContentLoaded', function() {
    fetchReleve()
 });
  const fetchReleve=async ()=>{
        const url = `/espace-etudiant/mes-releves`;
        const response = await fetch(url, {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json"
            }
        });

        if (!response.ok) {
            alert("Erreur lors de la récupération des données");
            return;
        }

        const data = await response.json();

        const today = new Date().toLocaleDateString('fr-FR');

        let html = `
      <div id="releveToPrint" class="container">

        <!-- En-tête -->
        <div class="text-center border-bottom pb-2 mb-2">
          <img src="{{ Storage::url(AppGetters::getAppLogo()) }}" alt="Logo École" style="height: 60px;" class="mb-1">
        
          <small class="text-muted d-block">Excellence, Rigueur, Innovation</small>
          <small class="text-muted">B.P. 1234 – Lomé, Togo | Tél : +228 90 00 00 00 | Email : contact@ispl.tg</small>
          <hr class="mt-2 mb-1" style="border-top: 1px solid #007BFF;">
        </div>

       
        <div class="releve-header mb-5">
          <p><strong>Nom :</strong> ${data.user.nom.toUpperCase()}</p>
          <p><strong>Prénom :</strong> ${data.user.prenom}</p>
          <p><strong>Période :</strong> ${data.periode_nom}</p>
          <p><strong>Année :</strong> ${data.anne}</p>
        </div>
    `;

        for (const ueNom in data.releve_grouped) {
            const uvs = data.releve_grouped[ueNom];
            if (!Array.isArray(uvs)) continue;

            const credit = uvs[0]?.credit || 0;
            const moyenne_ue = uvs[0]?.moyenne_ue || 0;
            
            // Vérifier s'il y a des matières bloquées (< 5/20)
            const hasBlockedUV = uvs.some(uv => parseFloat(uv.moyenne_uv) < 5);
            const gratification = uvs[0]?.gratification;
            
            let validation;
            if (gratification) {
                validation = `<span class="text-success fw-bold">Validé par gratification</span>
                             <br><small class="text-muted">Type: ${gratification.type}</small>`;
            } else if (hasBlockedUV) {
                validation = `<span class="text-danger fw-bold">Bloqué (matière < 5/20)</span>`;
            } else if (moyenne_ue >= 10) {
                validation = `<span class="text-success fw-bold">Validé</span>`;
            } else {
                validation = `<span class="text-warning fw-bold">Non Validé</span>`;
            }

            html += `<p class="mt-2 fw-bold text-primary">UE : ${ueNom} (${credit} crédits)</p>`;
            html += `
        <table class="table table-bordered table-sm align-middle">
          <thead class="table-light">
            <tr>
              <th>Eléments Constitutifs</th>
              <th>Devoir</th>
              <th>Interro</th>
              <th>Examen</th>
              <th>Moy. EC</th>
              <th>Coef.</th>
              <th>Valid.</th>
            </tr>
          </thead>
          <tbody>
      `;

            uvs.forEach(uv => {
                html += `
          <tr>
            <td>${uv.uv}</td>
            <td>${uv.devoir}</td>
            <td>${uv.interrogation}</td>
            <td>${uv.examen}</td>
            <td>${uv.moyenne_uv}</td>
            <td>${uv.coefficient}</td>
            <td>${uv.validation}</td>
          </tr>
        `;
            });

            html += `
          </tbody>
        </table>
      `;
        }

        html += `
      <div class="releve-summary mt-3">
        <table class="table table-bordered w-75 mx-auto">
          <tr><th>Moyenne générale</th><td>${data.moyenne_generale}</td></tr>
          <tr><th>Total crédits validés</th><td>${data.total_credits_valides}</td></tr>
          <tr><th>Total crédits non validés</th><td>${data.total_credits_non_valides}</td></tr>
        </table>
      </div>

      <!-- Signature -->
      <div class="releve-signature">
        <div><strong>Fait à Lomé, le ${today}</strong></div>
        <div class="text-end">
          <strong>{{ AppGetters::getAppTitreDe() }}</strong> <br>
          <strong>{{ AppGetters::getAppDe() }}</strong> 
          <div style="height: 70px;"></div>
          <p>Signature</p>
        </div>
      </div>

      </div>
    `;


        document.getElementById('releveModalBody').innerHTML = html;

        // const modal = new bootstrap.Modal(document.getElementById('exampleModalToggle2'));
        // modal.show();
    }

  
</script>
