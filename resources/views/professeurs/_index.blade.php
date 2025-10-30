@extends('professeurs.base', [
    'title' => 'Mon emploi de temps',
    'page_name' => 'Emploi de temps',
    'breadcrumbs' => ['Mon emploi de temps', 'Mes cours'],
])
@section('bases')
    <div id="calendar"></div>
    <div class="modal fade" id="coursModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="coursTitre">Détail du cours</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs" id="tabsCours" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" id="cahier-tab" data-bs-toggle="tab"
                                data-bs-target="#cahier">Cahier de texte</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="presence-tab" data-bs-toggle="tab"
                                data-bs-target="#presence">Présence</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="devoirs-tab" data-bs-toggle="tab"
                                data-bs-target="#devoirs">Devoirs</button>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <!-- Cahier de texte -->
                        <div class="tab-pane fade show active" id="cahier">
                            <div class="card border-0 shadow-sm p-3">
                                <h5 class="mb-3">Ajouter un contenu de cours</h5>

                                <!-- Intitulé du cours -->
                                <div class="mb-3">
                                    <label class="form-label">Titre ou sujet du cours</label>
                                    <input type="text" class="form-control"
                                        placeholder="Ex: Le système solaire, Les équations, etc." />
                                </div>

                                <!-- Affichage de l'id de la séance sélectionnée -->
                                <div class="mb-3">
                                    <label class="form-label">ID Séance (Emploi du temps sélectionné)</label>
                                    <input type="text" id="inputEmploiDuTempsId" class="form-control" readonly />
                                </div>

                                <!-- Contenu enrichi -->
                                <div class="mb-3">
                                    <label class="form-label">Contenu détaillé du cours</label>
                                    <textarea id="content" class="form-control"></textarea>
                                </div>

                                <!-- Pièce jointe -->
                                <div class="mb-3">
                                    <label class="form-label">Ajouter une pièce jointe</label>
                                    <input type="file" class="form-control" accept=".pdf,.doc,.docx,.jpg,.png" />
                                </div>

                                <div class="text-end">
                                    <button class="btn btn-primary">
                                        <i class="bi bi-save me-1"></i> Enregistrer le cours
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Présence -->
                        <div class="tab-pane fade" id="presence">
                            <!-- Affiche l'id de la séance sélectionnée (emploi_du_temps_id) -->
                            <div class="mb-3">
                                <label class="form-label">ID Séance (Emploi du temps sélectionné)</label>
                                <input type="text" id="inputEmploiDuTempsIdPresence" class="form-control" readonly />
                            </div>
                            <div class="table-responsive mt-3">
                                <table id="tablePresence" class="table table-bordered table-striped table-hover w-100">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Nom</th>
                                            <th>Prénom</th>
                                            <th>Absent</th>
                                            <th>Comportement</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Koffi</td>
                                            <td>Jean</td>
                                            <td class="text-center">
                                                <input type="checkbox" class="absent-check" />
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm"
                                                    placeholder="Ex: Bon, Passif..." />
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Diallo</td>
                                            <td>Awa</td>
                                            <td class="text-center">
                                                <input type="checkbox" class="absent-check" />
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm" />
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Mensah</td>
                                            <td>Eric</td>
                                            <td class="text-center">
                                                <input type="checkbox" class="absent-check" />
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm" />
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <button class="btn btn-warning mt-2" id="validerAbsence">Valider les absents</button>
                        </div>

                        <div class="tab-pane fade" id="devoirs">
                            <input type="hidden" id="inputEmploiDuTempsIdDevoir" />
                            <form id="form-devoir">
                                <div class="mb-3">
                                    <label for="titreDevoir" class="form-label">📘 Titre du devoir</label>
                                    <input type="text" id="titreDevoir" name="titre" class="form-control"
                                        placeholder="Ex: Analyse du poème d’Aimé Césaire" required />
                                </div>

                                <div class="mb-3">
                                    <label for="consignesDevoir" class="form-label">📝 Consignes</label>
                                    <textarea id="consignesDevoir" name="consignes" class="form-control" rows="5"
                                        placeholder="Donner votre analyse en 300 mots minimum..."></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">📎 Joindre un fichier (PDF, image, etc.)</label>
                                    <input type="file" name="fichier" class="form-control"
                                        accept=".pdf,.doc,.docx,.jpg,.png" />
                                </div>

                                <div class="mb-3">
                                    <label for="dateLimite" class="form-label">📅 Date limite de remise</label>
                                    <input type="date" id="dateLimite" name="date_limite" class="form-control"
                                        required />
                                </div>

                                <hr />

                                <div class="mb-3">
                                    <label for="correction" class="form-label">✅ Correction / Commentaires
                                        (facultatif)</label>
                                    <textarea id="correction" name="correction" class="form-control" placeholder="Saisir la correction ici..."
                                        rows="4"></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary">💾 Enregistrer le devoir</button>
                            </form>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let date = new Date();
        let d = date.getDate();
        let m = date.getMonth();
        let y = date.getFullYear();
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const coursModal = new bootstrap.Modal(document.getElementById('coursModal'));
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'timeGridWeek',
                locale: 'fr',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
                },
                buttonText: {
                    today: 'Aujourd\'hui',
                    month: 'Mois',
                    week: 'Semaine',
                    day: 'Jour',
                    list: 'liste',
                },
                weekends: false,
                themeSystem: 'bootstrap',
                slotDuration: '00:10:00',
                navLinks: true,
                height: 'auto',
                slotMinTime: '07:30:00',
                slotMaxTime: '18:00:00',
                editable: false,
                dayMaxEvents: true,
                handleWindowResize: true,


                events: '/espace-enseignant/mes-cours',

                eventClick: function(info) {
                    document.getElementById('coursTitre').innerText = info.event.title;
                    const props = info.event.extendedProps;
                    window.currentEmploiDuTempsId = props.emploi_du_temps_id ?? info.event.id;

                    document.getElementById('inputEmploiDuTempsId').value = window.currentEmploiDuTempsId ?? '';
                    document.getElementById('inputEmploiDuTempsIdDevoir').value = window.currentEmploiDuTempsId ?? '';
                    document.getElementById('inputEmploiDuTempsIdPresence').value = window.currentEmploiDuTempsId ?? '';

                    coursModal.show();

                    if (props.groupe_id) {
                        recupererEtudiants(props.groupe_id);

                        // Après avoir récupéré les étudiants, récupère les absences et coche les cases
                        setTimeout(function() {
                            fetch(`/espace-enseignant/absences/${window.currentEmploiDuTempsId}`)
                                .then(res => res.json())
                                .then(presences => {
                                    // presences: [{etudiant_id, statut|motif}]
                                    document.querySelectorAll('#tablePresence tbody tr').forEach(row => {
                                        const etuId = row.dataset.etudiantId;
                                        const pr = presences.find(a => a.etudiant_id == etuId);
                                        const isAbsent = pr ? (pr.statut ? pr.statut === 'absent' : true) : false;
                                        row.querySelector('.absent-check').checked = isAbsent;
                                        row.querySelector('input[type="text"]').value = pr?.commentaire || pr?.motif || '';
                                    });
                                });
                        }, 500); // attend que les étudiants soient affichés
                    }

                    // 1. Charger le cahier de texte existant
                    fetch(`/espace-enseignant/cahier-texte/${window.currentEmploiDuTempsId}`)
                        .then(res => res.json())
                        .then(data => {
                            if (data && data.titre) {
                                document.querySelector('#cahier input[type="text"]').value = data.titre;
                                tinymce.get('content').setContent(data.contenu ?? '');
                            } else {
                                document.querySelector('#cahier input[type="text"]').value = '';
                                tinymce.get('content').setContent('');
                            }
                        });

                    // 2. Charger le devoir existant
                    fetch(`/espace-enseignant/devoir/${window.currentEmploiDuTempsId}`)
                        .then(res => res.json())
                        .then(data => {
                            if (data && data.titre) {
                                document.getElementById('titreDevoir').value = data.titre;
                                tinymce.get('consignesDevoir').setContent(data.consignes ?? '');
                                document.getElementById('dateLimite').value = data.date_limite ?? '';
                                document.getElementById('correction').value = data.correction ?? '';
                                // Pour le fichier, tu peux afficher un lien si besoin
                            } else {
                                document.getElementById('titreDevoir').value = '';
                                tinymce.get('consignesDevoir').setContent('');
                                document.getElementById('dateLimite').value = '';
                                document.getElementById('correction').value = '';
                            }
                        });

                    // 3. Charger les absences existantes pour la séance
                    fetch(`/espace-enseignant/absences/${window.currentEmploiDuTempsId}`)
                        .then(res => res.json())
                        .then(data => {
                            // data = [{etudiant_id:..., motif:...}, ...]
                            document.querySelectorAll('#tablePresence tbody tr').forEach(row => {
                                const etuId = row.dataset.etudiantId;
                                const pr = data.find(a => a.etudiant_id == etuId);
                                const isAbsent = pr ? (pr.statut ? pr.statut === 'absent' : true) : false;
                                row.querySelector('.absent-check').checked = isAbsent;
                                row.querySelector('input[type="text"]').value = pr?.commentaire || pr?.motif || '';
                            });
                        });
                }
            });
            calendar.render();

            // 1. Pour le bouton d'enregistrement du devoir
            const formDevoir = document.getElementById('form-devoir');
            if (formDevoir) {
                formDevoir.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const btn = formDevoir.querySelector('button[type="submit"]');
                    const originalHtml = btn.innerHTML;
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Enregistrement...';

                    const emploiDuTempsId = document.getElementById('inputEmploiDuTempsIdDevoir').value;
                    const titre = document.getElementById('titreDevoir').value;
                    const consignes = tinymce.get('consignesDevoir').getContent();
                    const fichier = document.querySelector('#form-devoir input[name="fichier"]').files[0];
                    const dateLimite = document.getElementById('dateLimite').value;
                    const correction = document.getElementById('correction').value;

                    // Validation JS côté client
                    if (!emploiDuTempsId) {
                        Swal.fire('Erreur', 'Séance non sélectionnée', 'error');
                        return;
                    }
                    if (!titre || titre.trim() === '') {
                        Swal.fire('Erreur', 'Le titre est obligatoire.', 'error');
                        return;
                    }
                    if (!consignes || consignes.trim() === '') {
                        Swal.fire('Erreur', 'Le champ consignes est obligatoire.', 'error');
                        return;
                    }
                    if (!dateLimite) {
                        Swal.fire('Erreur', 'La date limite est obligatoire.', 'error');
                        return;
                    }

                    const formData = new FormData();
                    formData.append('emploi_du_temps_id', emploiDuTempsId);
                    formData.append('titre', titre);
                    formData.append('consignes', consignes);
                    if (fichier) formData.append('fichier', fichier);
                    formData.append('date_limite', dateLimite);
                    formData.append('correction', correction);

                    fetch('/espace-enseignant/enregistrement-devoir', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: formData
                    })
                    .then(async res => {
                        let data;
                        try {
                            data = await res.json();
                        } catch (e) {
                            Swal.fire('Erreur', 'Erreur serveur (réponse non JSON)', 'error');
                            return;
                        }
                        btn.disabled = false;
                        btn.innerHTML = originalHtml;
                        if (res.ok && data.message) {
                            Swal.fire('Succès', data.message, 'success');
                        } else if (data && data.message) {
                            Swal.fire('Erreur', data.message, 'error');
                        } else {
                            Swal.fire('Erreur', 'Erreur lors de l\'enregistrement', 'error');
                        }
                    })
                    .catch(err => {
                        btn.disabled = false;
                        btn.innerHTML = originalHtml;
                        Swal.fire('Erreur', 'Erreur lors de l\'enregistrement', 'error');
                    });
                });
            }

            // 2. Pour le bouton d'enregistrement du cahier de texte
            document.querySelector('#cahier button.btn-primary').onclick = function(e) {
                e.preventDefault();
                const btn = this;
                const originalHtml = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Enregistrement...';

                const emploiDuTempsId = window.currentEmploiDuTempsId || null; // à définir lors du clic sur un cours
                const titre = document.querySelector('#cahier input[type="text"]').value;
                const contenu = tinymce.get('content').getContent();
                const pieceJointeInput = document.querySelector('#cahier input[type="file"]');
                const pieceJointe = pieceJointeInput.files[0];

                if (!emploiDuTempsId) {
                    Swal.fire('Erreur', 'Séance non sélectionnée', 'error');
                    return;
                }

                const formData = new FormData();
                formData.append('emploi_du_temps_id', emploiDuTempsId);
                formData.append('titre', titre);
                formData.append('contenu', contenu);
                if (pieceJointe) formData.append('piece_jointe', pieceJointe);

                fetch('/espace-enseignant/enregistrement-cahier-texte', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                    if (data.message) {
                        Swal.fire('Succès', data.message, 'success');
                    } else {
                        Swal.fire('Erreur', 'Erreur lors de l\'enregistrement', 'error');
                    }
                })
                .catch(err => {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                    Swal.fire('Erreur', 'Erreur lors de l\'enregistrement', 'error');
                });
            };

            // 3. Pour le bouton d'enregistrement des absences
            document.getElementById('validerAbsence').addEventListener('click', function() {
                const btn = this;
                const originalHtml = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Traitement...';

                const absents = [];
                document.querySelectorAll('#tablePresence tbody tr').forEach(row => {
                    const checkbox = row.querySelector('.absent-check');
                    if (checkbox && checkbox.checked) {
                        absents.push({
                            etudiant_id: row.dataset.etudiantId,
                            motif: row.querySelector('input[type="text"]').value
                        });
                    }
                });

                // Récupère l'id de la séance (emploi_du_temps_id) depuis l'input
                const emploi_du_temps_id = document.getElementById('inputEmploiDuTempsIdPresence').value || null;
                if (!emploi_du_temps_id) {
                    Swal.fire('Erreur', 'Séance non sélectionnée', 'error');
                    return;
                }

                fetch('/espace-enseignant/enregistrement-absences', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ emploi_du_temps_id, absents })
                })
                .then(res => res.json())
                .then(data => {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                    if (data.message) {
                        Swal.fire('Succès', data.message, data.errors ? 'error' : 'success');
                        if (data.errors) {
                            // Affiche les erreurs de validation si besoin
                            console.log(data.errors);
                        }
                    } else {
                        Swal.fire('Erreur', 'Erreur lors de l\'enregistrement', 'error');
                    }
                })
                .catch(err => {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                    Swal.fire('Erreur', 'Erreur lors de l\'enregistrement', 'error');
                });
            });


        // Lors de la récupération des étudiants, ajouter data-etudiant-id sur chaque ligne
        function recupererEtudiants(groupe_id) {
            fetch(`/espace-enseignant/etudiants/${groupe_id}/liste`)
                .then(response => {
                    if (!response.ok) throw new Error("Erreur lors du chargement des étudiants");
                    return response.json();
                })
                .then(etudiants => {
                    const tbody = document.querySelector('#tablePresence tbody');
                    tbody.innerHTML = '';
                    etudiants.forEach(etudiant => {
                        const row = `<tr data-etudiant-id="${etudiant.id}">
                            <td>${etudiant.nom}</td>
                            <td>${etudiant.prenom}</td>
                            <td class="text-center"><input type="checkbox" class="absent-check" /></td>
                            <td><input type="text" class="form-control form-control-sm" /></td>
                        </tr>`;
                        tbody.innerHTML += row;
                    });
                })
                .catch(error => {
                    Swal.fire('Erreur', 'Impossible de récupérer les étudiants du groupe.', 'error');
                });
        }
    });
    </script>

    <script src="{{ asset('admin/assets/js/plugins/tinymce/tinymce.min.js') }}"></script>
    <script>
        tinymce.init({
            selector: '#content',
            height: 400,
            menubar: false,
            content_style: 'body { font-family: "Inter", sans-serif; }',
            plugins: 'advlist autolink link image lists charmap print preview code',
            toolbar: [
                'styleselect fontselect fontsizeselect',
                'undo redo | cut copy paste | bold italic  | alignleft aligncenter alignright alignjustify',
                'bullist numlist | outdent indent | blockquote subscript superscript | preview code'
            ]
        });
        tinymce.init({
            selector: '#consignesDevoir',
            height: 300,
            menubar: false,
            plugins: 'advlist autolink link image lists charmap preview code',
            toolbar: 'undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
            branding: false
        });
    </script>

    </div>
    </div>
@endsection
