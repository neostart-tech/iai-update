@extends('base', [
'title' => 'Étudiants du club',
'breadcrumbs' => ['Administration', 'Clubs étudiants', $club->nom, 'Étudiants'],
'page_name' => 'Liste des étudiants du club : ' . $club->nom,
])

@section('content')
<div class="card">
    @if(session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Succès',
            text: "{{ session('success') }}",
            timer: 2000,
            showConfirmButton: false
        });
    </script>
    @endif
    @if (session('error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Erreur',
            text: "{{ session('error') }}",
            timer: 2000,
            showConfirmButton: false
        });
    </script>
    @endif


    <div class="card-header">
        <h5>
            Club : <strong>{{ $club->nom }}</strong>
        </h5>
        <p class="text-muted mb-0">
            Responsable : {{ $club->responsable->nom ?? '-' }} {{ $club->responsable->prenom ?? '' }}
        </p>
        <div class="text-end mb-3">
            <button class="btn btn-primary"
                data-bs-toggle="modal"
                data-bs-target="#addEtudiantModal">
                <i class="ti ti-plus"></i> Ajouter un étudiant
            </button>
        </div>

    </div>

    <div class="card-body">

        @if ($club->etudiants->isNotEmpty())
        <div class="dt-responsive table-responsive">
            <table class="table table-hover" id="dom-jquery">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Matricule</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Date d’adhésion</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($club->etudiants as $key => $etudiant)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $etudiant->matricule }}</td>
                        <td>{{ $etudiant->nom }}</td>
                        <td>{{ $etudiant->prenom }}</td>
                        <td>
                            {{ \Carbon\Carbon::parse($etudiant->pivot->date_adhesion)->format('d/m/Y') }}
                        </td>
                        <td class="text-center">
                            <form method="POST"
                                action="{{ route('admin.club.etudiants.destroy', [$club, $etudiant]) }}"
                                class="delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </form>

                        </td>
                    </tr>
                    @endforeach
                </tbody>

            </table>
        </div>
        @else
        <div class="alert alert-warning">
            <i class="ti ti-info-circle"></i>
            Aucun étudiant n’est encore membre de ce club.
        </div>
        @endif

    </div>

    @include('admin.clubs.etudiants._add-etudiant-modal')


</div>


@include('layouts._scripts')

<script>
    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            Swal.fire({
                title: 'Confirmation',
                text: "Voulez-vous retirer cet étudiant du club ?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Oui, retirer',
                cancelButtonText: 'Annuler'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>

@endsection