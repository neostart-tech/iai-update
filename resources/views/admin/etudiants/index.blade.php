@extends('base', [
    'title' => 'Liste des étudiants',
    'page_name' => 'Liste des étudiants',
    'breadcrumbs' => ['Étudiants', $group->nom, 'Liste'],
])



@section('content')
    <div class="card">
         <div class="card-header text-end gap-5">
            {{-- <button class="btn btn-primary" onclick="genererReleve('{{ route('admin.releves.pdf', $group->slug) }}')">
                <i class="fa fa-file-pdf"></i> Générer relevé de notes
            </button>
            <span id="loading-msg" class="text-muted ms-3" style="display: none;">
                Génération en cours... <i class="fa fa-spinner fa-spin"></i>
            </span> --}}
            <div class="d-flex gap-3 col-md-6">
                <select class="form-select" name="niveau" data-trigger>
                    @forelse ($niveaux as $niveau)
                        <option value="{{ $niveau->id }}">{{ $niveau->libelle }}</option>
                    @empty
                        {{ "Aucun niveau disponible" }}
                    @endforelse
                </select>
                <span class="text-danger">
                    @error('niveau')
                        {{ $message }}
                    @enderror
                </span>

                
                 <select class="form-select" name="periode" data-trigger>   
                    @if (!empty($periodes))        
                     @foreach ($periodes as $periode)
                        <option value="{{ $periode->id }}">{{ $periode->nom }}</option>
                    @endforeach
                    @else
                     <option value=""> Aucune période disponible</option>

                      @endif        
                </select>
                <span class="text-danger">
                    @error('periode')
                        {{ $message }}
                    @enderror
                </span>

                 
            </div>
        </div> 

        <div class="card-body">
            @if ($etudiants->isNotEmpty())
                <div class="dt-responsive table-responsive">
                    <table id="dom-jquery" class="table table-hover">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Nom & prénoms</th>
                                <th scope="col">Matricule</th>
                                <th scope="col">Genre</th>
                                <th scope="col" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($etudiants as $key => $etudiant)
                                <tr>
                                    <th scope="row">{{ $key += 1 }}</th>
                                    <td>{{ $etudiant->nom . ' ' . $etudiant->prenom }}</td>
                                    <td>{{ $etudiant->matricule }}</td>
                                    <td>{{ $etudiant->genre->value }}</td>
                                    <td class="text-center">
                                        <ul class="list-inline me-auto mb-0">
                                            <li class="list-inline-item align-bottom" data-bs-toggle="tooltip"
                                                title="Changer de groupe">
                                                <a href="#" data-pc-animate="just-me" data-bs-toggle="modal"
                                                    onclick="handleGroupeUpdate('{{ route('admin.etudiants.change-group', $etudiant) }}', '{{ $etudiant->group->first()->nom }}')"
                                                    class="avtar avtar-xs btn-link-secondary btn-pc-default"
                                                    data-bs-target="#animateModal">
                                                    <i data-feather="edit" class="f-18"></i>
                                                </a>
                                            </li>
                                            <li class="list-inline-item align-bottom" data-bs-toggle="tooltip"
                                                title="Détails">
                                                <a href="{{ route('admin.etudiants.show', $etudiant) }}"
                                                    class="avtar avtar-xs btn-link-secondary btn-pc-default">
                                                    <i data-feather="eye" class="f-18"></i>
                                                </a>
                                            </li>
                                            <li class="list-inline-item align-bottom" data-bs-toggle="tooltip"
                                                title="Voir relevé de note">
                                                <a onclick="releveliste({{ $etudiant->id }})"
                                                    class="avtar avtar-xs btn-link-secondary btn-pc-default"
                                                    data-bs-target="#exampleModalToggle2" data-bs-toggle="modal">
                                                    <i class="fa fa-file-pdf"></i>
                                                </a>
                                            </li>
                                            <li class="list-inline-item align-bottom" data-bs-toggle="tooltip"
                                                title="Télécharger">
                                                <a href="{{ route('admin.releves.telecharger', $etudiant->slug) }}"
                                                    class="avtar avtar-xs btn-link-secondary btn-pc-default">
                                                    <i data-feather="download" class="f-18"></i>
                                                </a>
                                            </li>
                                            <li class="list-inline-item align-bottom" data-bs-toggle="tooltip"
                                                title="Imprimer la carte étudiant">
                                                <a href="{{route('admin.carte.index',$etudiant->slug)}}" class="avtar avtar-xs btn-link-secondary btn-pc-default"
                                                   >
                                                    <i data-feather="printer" class="f-18"></i>
                                                </a>
                                            </li>

                                        </ul>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Nom & prénoms</th>
                                <th scope="col">Matricule</th>
                                <th scope="col">Genre</th>
                                <th scope="col" class="text-center">Action</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <x-empty-table />
            @endif
        </div>
    </div>

    @include('admin.etudiants._change-group')
    @include('admin.etudiants.__show')
@endsection

@section('other-js')
    @include('layouts.admin._dt-scripts')
    <script>
        let animateModal = document.getElementById('animateModal');
        animateModal.addEventListener('show.bs.modal', function(event) {
            let button = event.relatedTarget;
            let recipient = button.getAttribute('data-pc-animate');
            let modalTitle = animateModal.querySelector('.modal-title');
            modalTitle.textContent = 'Changer de groupe';
            animateModal.classList.add('anim-' + recipient);
            if (recipient == 'let-me-in' || recipient == 'make-way' || recipient == 'slip-from-top') {
                document.body.classList.add('anim-' + recipient);
            }
        });
        animateModal.addEventListener('hidden.bs.modal', function(event) {
            removeClassByPrefix(animateModal, 'anim-');
            removeClassByPrefix(document.body, 'anim-');
        });

        function removeClassByPrefix(node, prefix) {
            for (let i = 0; i < node.classList.length; i++) {
                let value = node.classList[i];
                if (value.startsWith(prefix)) {
                    node.classList.remove(value);
                }
            }
        }
    </script>

    <script>
        function genererReleve(url) {
            alert(url)
            const loadingMsg = document.getElementById('loading-msg');
            loadingMsg.style.display = 'inline';

            fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) throw new Error('Erreur de réponse lors de la génération' + error.message);
                    return response.json();
                })
                .then(data => {
                    if (data) {
                        checkPdfReady("{{ route('admin.releves.checked') }}");
                    }
                })
                .catch(error => {
                    loadingMsg.style.display = 'none';
                    alert("Erreur lors de la génération  2: " + error.message);
                });
        }

        function checkPdfReady(checkUrl) {
            const loadingMsg = document.getElementById('loading-msg');

            const interval = setInterval(() => {
                fetch(checkUrl)
                    .then(response => response.json())
                    .then(data => {
                        if (data.ready) {
                            clearInterval(interval);
                            loadingMsg.innerHTML = 'Téléchargement...';
                            window.location.href = data.download_url;
                        }
                    })
                    .catch(error => {

                        console.error('Erreur de vérification 1:', error);
                        clearInterval(interval);
                        loadingMsg.style.display = 'none';
                        alert("Erreur lors de la vérification. 2", error.message);
                    });
            }, 10000); // vérifie toutes les 3 secondes
        }




        let changeGroupForm = document.getElementById('change-group-form');

        function handleGroupeUpdate(etudiantUrl, oldGroup) {
            document.getElementById('old-group').value = oldGroup;
            changeGroupForm.setAttribute('action', etudiantUrl);
        }

        document.getElementById('submit-form-button').addEventListener('click', event => {
            // event.preventDefault();
            // console.log('ici')
            //
            // Swal.mixin({
            // 	customClass: {
            // 		confirmButton: 'btn btn-success',
            // 		cancelButton: 'btn btn-danger'
            // 	},
            // 	buttonsStyling: false
            // }).fire({
            // 	title: 'Confirmez-vous ce choix?',
            // 	text: "Vous changerai de groupe à l'étudiant(e)",
            // 	icon: 'warning',
            // 	showCancelButton: true,
            // 	confirmButtonText: 'Oui, confirmer',
            // 	cancelButtonText: 'Non, annuler!',
            // 	reverseButtons: true
            // }).then((result) => {
            // 	result.isConfirmed && changeGroupForm.submit();
            // });
        });
    </script>
@endsection

@section('other-css')
    <link rel="stylesheet" href="{{ asset('admin/assets/css/plugins/dataTables.bootstrap5.min.css') }}">
@endsection
