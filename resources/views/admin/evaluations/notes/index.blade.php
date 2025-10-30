@extends('base', [
    'title' => 'Fiche d\'anonymat',
    'page_name' => 'Fiche d\'anonymat',
    'breadcrumbs' => [
        [
            'text' => 'Évaluation',
            'url' => route('admin.evaluations.index'),
        ],
        $evaluation->matiere->nom,
        'Fiche d\'anonymat',
    ],
])

@section('content')
    @include('admin.evaluations.notes._details')
    <div class="card">
        <div class="card-header text-end">
            @unless ($evaluation->correction_submission_date)
                <button class="btn btn-primary col-12 mb-3 mb-md-0 col-md-auto" onclick="handlePublication()">
                    Publier les notes
                </button>
                <form action="{{ route('admin.evaluations.notes.publish-notes', $evaluation) }}" hidden=""
                    id="notes-publication-form" method="post">
                    @csrf @method('put')
                </form>
            @endunless
            <a href="{{ route('admin.evaluations.notes.export', $evaluation) }}" class="btn btn-outline-secondary col-12 col-md-auto ms-md-2">
                Exporter en Excel
            </a>
            @if ($notes->isNotEmpty() or $correctionEnable)
                <button class="btn btn-primary col-12 col-md-auto"
                    onclick="document.getElementById('notes-store-form').submit()">
                    Soumettre les notes
                </button>
            @endif
        </div>
        <div class="card-body">
            @if ($notes->isNotEmpty())
                <form action="{{ route('admin.evaluations.notes.store-notes', $evaluation) }}" method="post"
                    id="notes-store-form">
                    @csrf
                    <div class="dt-responsive table-responsive">
                        <table id="dom-jquery" class="table table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Matricule</th>
                                    <th scope="col">Nom & prénoms</th>
                                    <th scope="col">Anonymat</th>
                                    <th scope="col">Note</th>
                                    <th scope="col" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($notes->sortBy('anonymat') as $key => $note)
                                    <tr>
                                        <th scope="row">{{ $key += 1 }}</th>
                                        <td>{{ $note->etudiant->matricule }}</td>
                                        <td>{{ $note->etudiant->nom . ' ' . $note->etudiant->prenom }}</td>
                                        <td>{{ $note->anonymat }}</td>
                                        <td>
                                            @if ($correctionEnable)
                                                <input type="text" class="form-control" 
                                                    name="notes[]" id="note-{{ $note->etudiant->slug }}"
                                                    value="{{ $note->notation === 'R' ? 'R' : $note->note }}" placeholder="0-20 ou R">
                                                <input name="etudiants[]" value="{{ $note->etudiant->slug }}"
                                                    hidden="">
                                            @else
                                                <div class="form-control text-muted">{{ $note->notation === 'R' ? 'R' : $note->note }}</div>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <ul class="list-inline me-auto mb-0">
                                                <li class="list-inline-item align-bottom" data-bs-toggle="tooltip"
                                                    title="Détails">
                                                    <a href="#" data-pc-animate="blur" type="button"
                                                        data-bs-toggle="modal" data-bs-target="#animateModal"
                                                        data-note-id="{{ $note->id }}" data-note-value="{{ $note->note }}"
                                                        onclick="handleNoteEditFromAttrs(this)"
                                                        class="avtar avtar-xs btn-link-secondary btn-pc-default">
                                                        <i data-feather="edit" class="f-18"></i>
                                                    </a>
                                                </li>
                                                {{-- <li class="list-inline-item align-bottom" data-bs-toggle="tooltip" title="Détails">
											<a href="{{ route('admin.etudiants.show', $note->etudiant) }}"
												 class="avtar avtar-xs btn-link-secondary btn-pc-default">
												<i data-feather="eye" class="f-18"></i>
											</a>
										</li> --}}
                                            </ul>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Matricule</th>
                                    <th scope="col">Nom & prénoms</th>
                                    <th scope="col">Anonymat</th>
                                    <th scope="col">Note</th>
                                    <th scope="col" class="text-center">Action</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </form>
            @else
                <x-empty-table />
            @endif
        </div>
        <div class="card-footer text-end">
            @if ($notes->isNotEmpty() or $correctionEnable)
                <button class="btn btn-primary col-12 col-md-auto"
                    onclick="document.getElementById('notes-store-form').submit()">
                    Soumettre les notes
                </button>
            @endif
        </div>
    </div>

    @include('admin.evaluations.notes._edit')
@endsection

@section('other-js')
    @include('layouts.admin._dt-scripts')

    <script>
        function handlePublication() {
            Swal.fire({
                title: 'Voulez-vous publier?',
                text: 'Cette action rendra les notes visibles auprès des étudiants concernés',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Oui, publier',
                cancelButtonText: 'Non, annuler!',
                reverseButtons: true
            }).then(result => result.isConfirmed && document.getElementById('notes-publication-form').submit());
        }

        function handleNoteEditFromAttrs(el) {
            document.getElementById('note-id').value = el.getAttribute('data-note-id');
            document.getElementById('current-note').value = el.getAttribute('data-note-value');
        }
    </script>
@endsection

@section('other-css')
    <link rel="stylesheet" href="{{ asset('admin/assets/css/plugins/dataTables.bootstrap5.min.css') }}">
@endsection
