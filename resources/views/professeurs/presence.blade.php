@extends('professeurs.base')
@section('bases')
    <h3>Liste de présence pour la séance : {{ $cours->titre ?? '' }} ({{ $emploi->debut }})</h3>
    <div class="mb-2">
        <span class="badge bg-success me-1">Présent</span>
        <span class="badge bg-warning text-dark me-1">Retard</span>
        <span class="badge bg-danger me-1">Absent</span>
        <span class="badge bg-info text-dark">Justifié</span>
    </div>
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body d-flex align-items-center gap-3 flex-wrap">
            <div>
                <strong>Ma présence</strong>
            </div>
            <div>
                <select id="teacher-statut" class="form-select form-select-sm" style="min-width: 160px;">
                    <option value="present">Présent</option>
                    <option value="retard">Retard</option>
                    <option value="absent">Absent</option>
                </select>
            </div>
            <div>
                <input type="text" id="teacher-comment" class="form-control form-control-sm" placeholder="Commentaire (optionnel)" style="min-width: 240px;"/>
            </div>
            <div>
                <span id="teacher-badge" class="badge bg-success">Présent</span>
            </div>
            <div class="ms-auto">
                <button class="btn btn-sm btn-outline-primary" id="btn-save-teacher">Enregistrer ma présence</button>
            </div>
        </div>
    </div>
    <input type="hidden" id="saveUrl" value="{{ route('enseignants.absences.store') }}" />
    <div id="stats" class="mb-3"></div>
    <table class="table table-bordered align-middle">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Statut</th>
                <th>Commentaire</th>
                <th>Sanction (si retard)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($etudiants as $etudiant)
                <tr>
                    <td>{{ $etudiant->nom }}</td>
                    <td>{{ $etudiant->prenom }}</td>
                    <td style="min-width: 280px;">
                        <select class="form-select form-select-sm statut-select d-inline-block w-auto" data-etudiant="{{ $etudiant->id }}">
                            <option value="present" {{ in_array($etudiant->id, $absents) ? '' : 'selected' }}>Présent</option>
                            <option value="retard">Retard</option>
                            <option value="absent" {{ in_array($etudiant->id, $absents) ? 'selected' : '' }}>Absent</option>
                            <option value="justifie">Justifié</option>
                        </select>
                        <span class="ms-2 validation-badge badge bg-secondary d-none" data-etudiant="{{ $etudiant->id }}">—</span>
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm commentaire-input" placeholder="Commentaire (optionnel)">
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm sanction-input" placeholder="Sanction si retard">
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="text-end">
        <button class="btn btn-primary" id="btn-save-presence">Enregistrer</button>
    </div>
    <script>
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('statut-select')) {
                const td = e.target.closest('td');
                const tr = e.target.closest('tr');
                tr.classList.remove('table-danger', 'table-warning', 'table-info');
                const v = e.target.value;
                if (v === 'absent') tr.classList.add('table-danger');
                if (v === 'retard') tr.classList.add('table-warning');
                if (v === 'justifie') tr.classList.add('table-info');
            }
        });

        // Charger et afficher les statistiques
        fetch('/espace-enseignant/presence/{{ $emploi->id }}/stats')
            .then(r => r.json())
            .then(s => {
                const total = (s.present||0)+(s.retard||0)+(s.absent||0)+(s.justifie||0);
                document.getElementById('stats').innerHTML = `
                    <strong>Statistiques:</strong>
                    <span class="badge bg-success ms-2">Présents: ${s.present||0}</span>
                    <span class="badge bg-warning text-dark ms-2">Retards: ${s.retard||0}</span>
                    <span class="badge bg-danger ms-2">Absents: ${s.absent||0}</span>
                    <span class="badge bg-info text-dark ms-2">Justifiés: ${s.justifie||0}</span>
                    <span class="badge bg-secondary ms-2">Total: ${total}</span>
                `;
            }).catch(()=>{});

        // Prefill from backend tri-state presences and show validation badges
        fetch('/espace-enseignant/absences/{{ $emploi->id }}')
          .then(r=>r.json())
          .then(rows => {
              if(!Array.isArray(rows)) return;
              const byId = new Map(rows.map(x => [String(x.etudiant_id), x]));
              document.querySelectorAll('.statut-select').forEach(sel => {
                  const id = sel.dataset.etudiant;
                  const pr = byId.get(String(id));
                  if(pr){
                      sel.value = pr.statut || 'present';
                      const tr = sel.closest('tr');
                      tr.classList.remove('table-danger','table-warning','table-info');
                      if(pr.statut==='absent') tr.classList.add('table-danger');
                      if(pr.statut==='retard') tr.classList.add('table-warning');
                      if(pr.statut==='justifie') tr.classList.add('table-info');
                      const com = tr.querySelector('.commentaire-input');
                      const sanc = tr.querySelector('.sanction-input');
                      if(com) com.value = pr.commentaire||'';
                      if(sanc) sanc.value = pr.sanction||'';
                      const badge = tr.querySelector('.validation-badge');
                      if(badge){
                         if(pr.statut==='absent'){
                             const needs = !!pr.needs_validation;
                             badge.textContent = needs ? 'À valider' : 'Validée';
                             badge.classList.remove('d-none','bg-secondary','bg-success','bg-warning');
                             badge.classList.add(needs ? 'bg-warning':'bg-success');
                         }else{
                             badge.classList.add('d-none');
                         }
                      }
                  }
              });
          })
          .catch(()=>{});

        document.getElementById('btn-save-presence')?.addEventListener('click', function() {
            const rows = Array.from(document.querySelectorAll('tbody tr'));
            const payload = [];
            rows.forEach(r => {
                const etudiantId = r.querySelector('.statut-select').dataset.etudiant;
                const statut = r.querySelector('.statut-select').value;
                const commentaire = r.querySelector('.commentaire-input').value || null;
                const sanction = r.querySelector('.sanction-input').value || null;
                payload.push({ etudiant_id: etudiantId, statut, commentaire, sanction });
            });

            const saveUrl = document.getElementById('saveUrl').value;
            fetch(saveUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ emploi_du_temps_id: '{{ $emploi->id }}', presences: payload })
            })
            .then(r => r.json())
            .then(d => {
                alert(d.message || 'Enregistré');
                // refresh stats
                fetch('/espace-enseignant/presence/{{ $emploi->id }}/stats')
                    .then(r => r.json())
                    .then(s => {
                        const total = (s.present||0)+(s.retard||0)+(s.absent||0)+(s.justifie||0);
                        document.getElementById('stats').innerHTML = `
                            <strong>Statistiques:</strong>
                            <span class=\"badge bg-success ms-2\">Présents: ${s.present||0}</span>
                            <span class=\"badge bg-warning text-dark ms-2\">Retards: ${s.retard||0}</span>
                            <span class=\"badge bg-danger ms-2\">Absents: ${s.absent||0}</span>
                            <span class=\"badge bg-info text-dark ms-2\">Justifiés: ${s.justifie||0}</span>
                            <span class=\"badge bg-secondary ms-2\">Total: ${total}</span>
                        `;
                    }).catch(()=>{});
                // refresh validation badges from backend
                fetch('/espace-enseignant/absences/{{ $emploi->id }}')
                  .then(r=>r.json()).then(rows=>{
                    if(!Array.isArray(rows)) return;
                    const byId = new Map(rows.map(x => [String(x.etudiant_id), x]));
                    document.querySelectorAll('.statut-select').forEach(sel => {
                        const id = sel.dataset.etudiant;
                        const pr = byId.get(String(id));
                        const tr = sel.closest('tr');
                        const badge = tr.querySelector('.validation-badge');
                        if(badge){
                           if(pr && pr.statut==='absent'){
                               const needs = !!pr.needs_validation;
                               badge.textContent = needs ? 'À valider' : 'Validée';
                               badge.classList.remove('d-none','bg-secondary','bg-success','bg-warning');
                               badge.classList.add(needs ? 'bg-warning':'bg-success');
                           }else{
                               badge.classList.add('d-none');
                           }
                        }
                    });
                  }).catch(()=>{});
            })
            .catch(() => alert('Erreur d\'enregistrement'));
        });

        // Teacher presence UI: set badge color/text
        function updateTeacherBadge(val){
            const badge = document.getElementById('teacher-badge');
            badge.classList.remove('bg-success','bg-warning','bg-danger');
            if(val==='present'){ badge.classList.add('bg-success'); badge.textContent='Présent'; }
            if(val==='retard'){ badge.classList.add('bg-warning'); badge.textContent='Retard'; }
            if(val==='absent'){ badge.classList.add('bg-danger'); badge.textContent='Absent'; }
        }
        document.getElementById('teacher-statut').addEventListener('change', (e)=>updateTeacherBadge(e.target.value));

        // Load teacher presence
        fetch('/espace-enseignant/presence/enseignant/{{ $emploi->id }}')
          .then(r=>r.json())
          .then(p=>{ if(p){
                document.getElementById('teacher-statut').value = p.statut || 'present';
                document.getElementById('teacher-comment').value = p.commentaire || '';
                updateTeacherBadge(p.statut||'present');
          }}).catch(()=>{});

        // Save teacher presence
        document.getElementById('btn-save-teacher').addEventListener('click', function(){
            const statut = document.getElementById('teacher-statut').value;
            const commentaire = document.getElementById('teacher-comment').value;
            fetch('/espace-enseignant/presence/enseignant',{
                method:'POST',
                headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
                body: JSON.stringify({ emploi_du_temps_id: '{{ $emploi->id }}', statut, commentaire })
            }).then(r=>r.json()).then(d=>{
                updateTeacherBadge(statut);
            }).catch(()=>{});
        });
    </script>
@endsection
