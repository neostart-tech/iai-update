@php use Illuminate\Support\Str; @endphp
@extends('base', [
    'title' => 'Liste des opportunités',
    'page_name' => 'Liste des opportunités',
    'breadcrumbs' => ['Opportunités', 'Liste des opportunités'],
])

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="text-end p-4 pb-sm-2 mb-2">
                <a href="{{ route('admin.announcements.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus f-18"></i> Ajouter une annonce
                </a>
            </div>
        </div>
        <div class="card-body">
            @if ($announcements->isNotEmpty())
                <div class="dt-responsive table-responsive">
                    <table id="dom-jquery" class="table table-hover table-bordered">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Titre</th>
                                <th scope="col">Annonceur</th>
                                <th scope="col">Date</th>
                                <th scope="col">Status</th>
                                <th scope="col" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($announcements as $key => $announcement)
                                <tr>
                                    <th scope="row">{{ $key += 1 }}</th>
                                    <td>{{ Str::limit($announcement->title, 50) }}</td>
                                    <td>{{ $announcement->advertiser->nom }}</td>
                                    <td class="text-capitalize">{{ $announcement->created_at->translatedFormat('d F Y') }}
                                    </td>
                                    <td>
                                        <div class="form-check form-switch custom-switch-v1 mb-2">
                                            <input type="checkbox" class="form-check-input input-primary"
                                                id="status-{{ $announcement->slug }}" @checked($announcement->status)
                                                onchange="changeStatus('{{ $announcement->slug }}')">
                                            <label class="form-check-label" id="label-{{ $announcement->slug }}"
                                                onclick="changeStatus('{{ $announcement->slug }}')"
                                                for="status-{{ $announcement->slug }}">{{ $announcement->status ? 'Publiée' : 'Non publiée' }}</label>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <ul class="list-inline me-auto mb-0">
                                            <li class="list-inline-item align-bottom" data-bs-toggle="tooltip"
                                                title="Détails">
                                                <a href="{{ route('admin.announcements.show', $announcement) }}"
                                                    class="avtar avtar-xs btn-link-secondary btn-pc-default">
                                                    <i class="ti ti-eye f-18"></i>
                                                </a>
                                            </li>
                                            <li class="list-inline-item align-bottom" data-bs-toggle="tooltip"
                                                title="Modifier">
                                                <a href="{{ route('admin.announcements.edit', $announcement) }}"
                                                    class="avtar avtar-xs btn-link-success btn-pc-default">
                                                    <i class="ti ti-edit-circle f-18"></i>
                                                </a>
                                            </li>
                                            <li class="list-inline-item align-bottom" data-bs-toggle="tooltip"
                                                title="Postulants">
                                                <a href="{{ route('admin.announcements.etudiants', $announcement) }}"
                                                    class="avtar avtar-xs btn-link-success btn-pc-default">
                                                    <i class="ti ti-users f-18"></i>
                                                </a>
                                            </li>
                                            <li class="list-inline-item align-bottom" data-bs-toggle="tooltip"
                                                title="Supprimer">
                                                <a href="#" data-bs-target="#exampleModalToggle2"
                                                    data-bs-toggle="modal"
                                                    onclick="deleteAnnounce({{ Illuminate\Database\Eloquent\Casts\Json::encode($announcement) }})"
                                                    class="avtar avtar-xs btn-link-danger btn-pc-default">
                                                    <i class="ti ti-trash f-18"></i>
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
                                <th scope="col">Titre</th>
                                <th scope="col">Annonceur</th>
                                <th scope="col">Date</th>
                                <th scope="col">Status</th>
                                <th scope="col" class="text-center">Actions</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <x-empty-table />
            @endif
        </div>
    </div>
@endsection

@include('admin.announcements.___show-modal')

@section('other-js')
    <script>
        const changeStatus = (slug) => {
            Swal.fire({
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: "Publier",
                cancelButtonText: 'Ne pas publier',
                html: "Publier cette annonce la rendra visible auprès des étudiants leur offrant ainsi la possibilité de postuler !"
            }).then(result => {
                document.getElementById('status-' + slug).checked = result.isConfirmed;
                if (result.isConfirmed) {
                    const baseUl = '{{ url('administration/opportunites/{slug}/publier') }}';
                    $.get(baseUl.replace('{slug}', slug))
                        .then(response => {
                            let newStatus = response.status;
                            showToast(response.message, 'success');
                            document.getElementById('status-' + slug).checked = newStatus;
                            document.getElementById(`label-${slug}`).innerText = newStatus ? 'Publiée' :
                                'Non publiée';
                        }).catch(() => {
                            showToast('Erreur', 'danger');
                        });
                }
            });
        };

        function deleteAnnounce(announce) {
            document.getElementById('annonceId').value = announce.id
        }
    </script>
@endsection
