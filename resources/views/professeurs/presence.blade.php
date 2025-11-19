@extends('professeurs.base')
@section('bases')

<style>
    /* Styles pour les statuts dans le tableau */
    .statut-present {
        background-color: rgba(40, 167, 69, 0.08) !important;
    }
    
    .statut-retard {
        background-color: rgba(255, 193, 7, 0.08) !important;
    }
    
    .statut-absent {
        background-color: rgba(220, 53, 69, 0.08) !important;
    }
    
    .statut-justifie {
        background-color: rgba(23, 162, 184, 0.08) !important;
    }
    
    /* Améliorations du tableau */
    .table-presence {
        border-collapse: separate;
        border-spacing: 0;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 0 10px rgba(0,0,0,0.05);
    }
    
    .table-presence thead th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        padding: 12px 10px;
        font-weight: 600;
        color: #495057;
        vertical-align: middle;
    }
    
    .table-presence tbody td {
        padding: 10px 10px;
        vertical-align: middle;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .table-presence tbody tr:last-child td {
        border-bottom: none;
    }
    
    .table-presence tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.03);
        transform: translateY(-1px);
        transition: all 0.2s ease;
    }
    
    /* Améliorations des formulaires */
    .form-select-sm, .form-control-sm {
        border-radius: 4px;
        border: 1px solid #ced4da;
        transition: all 0.2s ease;
        vertical-align: middle;
    }
    
    .form-select-sm:focus, .form-control-sm:focus {
        box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.15);
        border-color: #80bdff;
    }
    
    /* Header avec bouton */
    .presence-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .presence-title {
        flex: 1;
        min-width: 300px;
    }
    
    .presence-actions {
        display: flex;
        gap: 15px;
        align-items: center;
    }
    
    /* Carte enseignant */
    .teacher-presence-card {
        border-left: 4px solid #0d6efd;
        background-color: rgba(13, 110, 253, 0.05);
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .presence-header {
            flex-direction: column;
            align-items: stretch;
        }
        
        .presence-actions {
            justify-content: flex-end;
        }
    }
</style>

<div class="card">
    <div class="presence-header p-4 pb-sm-2 mb-2">
        <div class="presence-title">
            <h4 class="mb-2">Liste de présence : {{ $cours->titre ?? '' }} ({{ $emploi->debut }})</h4>
            
            <div class="mb-2">
                <span class="badge bg-success me-2 p-2">Présent</span>
                <span class="badge bg-warning text-dark me-2 p-2">Retard</span>
                <span class="badge bg-danger me-2 p-2">Absent</span>
                <span class="badge bg-info text-dark me-2 p-2">Justifié</span>
            </div>
        </div>
        
        <div class="presence-actions">
            <button class="btn btn-primary" id="btn-save-presence">
                <i class="ti ti-save f-18 me-1"></i>Enregistrer les présences
            </button>
        </div>
    </div>

    <div class="card-body">
        <!-- Carte de présence de l'enseignant -->
        <div class="card border-0 shadow-sm mb-4 teacher-presence-card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <div>
                                <strong class="text-primary">Ma présence</strong>
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
                                <button class="btn btn-sm btn-outline-primary" id="btn-save-teacher">
                                    <i class="ti ti-save me-1"></i>Enregistrer ma présence
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques -->
        <div id="stats" class="mb-4 p-3 bg-light rounded"></div>

        <input type="hidden" id="saveUrl" value="{{ route('enseignants.absences.store') }}" />

        @if ($etudiants->isNotEmpty())
            <div class="dt-responsive table-responsive">
                <table id="dom-jquery" class="table table-striped table-hover table-bordered table-presence">
                    <thead>
                        <tr>
                            <th width="20%" scope="col">Nom</th>
                            <th width="20%" scope="col">Prénom</th>
                            <th width="15%" scope="col">Statut</th>
                            <th width="25%" scope="col">Commentaire</th>
                            <th width="20%" scope="col">Sanction (retard)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($etudiants as $etudiant)
                            @if($etudiant)
                            <tr class="statut-transition" id="row-{{ $etudiant->id }}">
                                <td class="fw-medium">{{ $etudiant->nom }}</td>
                                <td>{{ $etudiant->prenom }}</td>

                                <td>
                                    <select class="form-select form-select-sm statut-select" data-etudiant="{{ $etudiant->id }}">
                                        <option value="present" {{ in_array($etudiant->id, $absents ?? []) ? '' : 'selected' }}>Présent</option>
                                        <option value="retard">Retard</option>
                                        <option value="absent" {{ in_array($etudiant->id, $absents ?? []) ? 'selected' : '' }}>Absent</option>
                                        <option value="justifie">Justifié</option>
                                    </select>

                                    <span class="validation-badge badge bg-secondary d-none mt-1" data-etudiant="{{ $etudiant->id }}">—</span>
                                </td>

                                <td>
                                    <input type="text" class="form-control form-control-sm commentaire-input" placeholder="Commentaire (optionnel)">
                                </td>

                                <td>
                                    <input type="text" class="form-control form-control-sm sanction-input" placeholder="Sanction">
                                </td>
                            </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-warning">
                <div class="media align-items-center">
                    <i class="ti ti-info-circle h2 f-w-400 mb-0"></i>
                    <div class="media-body ms-3">Aucun étudiant à afficher</div>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fonction pour appliquer la couleur de fond selon le statut
    function updateRowColor(selectElement) {
        const row = selectElement.closest('tr');
        const statut = selectElement.value;
        
        // Supprimer toutes les classes de couleur existantes
        row.classList.remove('statut-present', 'statut-retard', 'statut-absent', 'statut-justifie');
        
        // Ajouter la classe correspondant au statut
        row.classList.add(`statut-${statut}`);
    }
    
    // Appliquer la couleur initiale pour tous les statuts
    document.querySelectorAll('.statut-select').forEach(select => {
        updateRowColor(select);
        
        // Écouter les changements de statut
        select.addEventListener('change', function() {
            updateRowColor(this);
        });
    });

    // Charger les présences déjà enregistrées
    fetch('/espace-enseignant/absences/{{ $emploi->id }}')
        .then(r => r.json())
        .then(data => {
            if (!Array.isArray(data)) return;
            const mapById = new Map(data.map(d => [String(d.etudiant_id), d]));
            document.querySelectorAll('tbody tr').forEach(tr => {
                const select = tr.querySelector('.statut-select');
                if (!select) return;
                const etudiantId = select.dataset.etudiant;
                const presence = mapById.get(String(etudiantId));
                if (presence) {
                    select.value = presence.statut || 'present';
                    updateRowColor(select); // Mettre à jour la couleur
                    
                    const com = tr.querySelector('.commentaire-input');
                    const sanc = tr.querySelector('.sanction-input');
                    if (com) com.value = presence.commentaire || '';
                    if (sanc) sanc.value = presence.sanction || '';
                    const badge = tr.querySelector('.validation-badge');
                    if (badge) {
                        if (presence.statut === 'absent') {
                            const needs = !!presence.needs_validation;
                            badge.textContent = needs ? 'À valider' : 'Validée';
                            badge.classList.remove('d-none', 'bg-secondary', 'bg-success', 'bg-warning');
                            badge.classList.add(needs ? 'bg-warning' : 'bg-success');
                        } else {
                            badge.classList.add('d-none');
                        }
                    }
                }
            });
        }).catch(console.error);

    // Enregistrement des présences étudiants (création ou mise à jour)
    document.getElementById('btn-save-presence')?.addEventListener('click', function () {
        const rows = Array.from(document.querySelectorAll('tbody tr'));
        const payload = [];

        rows.forEach(r => {
            const select = r.querySelector('.statut-select');
            if (!select) return;

            const etudiantId = select.dataset.etudiant;
            const statut = select.value;
            const commentaire = r.querySelector('.commentaire-input')?.value || null;
            const sanction = r.querySelector('.sanction-input')?.value || null;

            payload.push({
                etudiant_id: etudiantId,
                statut,
                commentaire,
                sanction
            });
        });

        const saveUrl = document.getElementById('saveUrl').value;

        fetch(saveUrl, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                emploi_du_temps_id: "{{ $emploi->id }}",
                presences: payload
            })
        })
        .then(r => r.json())
        .then(d => {
            Swal.fire({
                icon: d.errors ? 'error' : 'success',
                title: d.message || "Présences enregistrées !",
                timer: 2500,
                showConfirmButton: false
            });
            
            // Recharger les statistiques après enregistrement
            loadStats();
        })
        .catch(() => Swal.fire({
            icon: 'error',
            title: "Erreur réseau",
            timer: 2500,
            showConfirmButton: false
        }));
    });

    // Fonction pour charger et afficher les statistiques
    function loadStats() {
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
    }

    // Charger les statistiques au démarrage
    loadStats();

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
            Swal.fire({
                icon: 'success',
                title: "Présence enseignant enregistrée !",
                timer: 2000,
                showConfirmButton: false
            });
        }).catch(() => Swal.fire({
            icon: 'error',
            title: "Erreur d'enregistrement",
            timer: 2000,
            showConfirmButton: false
        }));
    });
});
</script>

@endsection