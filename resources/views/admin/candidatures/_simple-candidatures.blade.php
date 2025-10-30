<div class="row">

    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="text-end p-4 pb-sm-2">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                        data-bs-target="#candidatureModal">
                        <i class="ti ti-plus f-18"></i> Nouvelle candidature
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="dt-responsive table-responsive">
                    @if ($simpleCandidatures->isNotEmpty())
                        <table id="dom-jquery" class="table table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Nom</th>
                                    <th scope="col">Prénoms</th>
                                    <th scope="col">Code</th>
                                    <th scope="col" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($simpleCandidatures as $key => $candidature)
                                    <tr>
                                        <th scope="row">{{ $key += 1 }}</th>
                                        <td>{{ $candidature->nom }}</td>
                                        <td>{{ $candidature->prenom }}</td>
                                        <td>{{ $candidature->code }}</td>
                                        <td class="text-center">
                                            <ul class="list-inline me-auto mb-0">
                                                <li class="list-inline-item align-bottom" data-bs-toggle="tooltip"
                                                    title="Aperçu">
                                                    <a href="{{ route('admin.candidatures.show', $candidature) }}"
                                                        class="avtar avtar-xs btn-link-secondary btn-pc-default">
                                                        <i class="ti ti-eye f-18"></i>
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
                                    <th scope="col">Nom</th>
                                    <th scope="col">Prénoms</th>
                                    <th scope="col">Code</th>
                                    <th scope="col" class="text-center">Actions</th>
                                </tr>
                            </tfoot>
                        </table>
                    @else
                        <div class="alert alert-danger">
                            <div class="media align-items-center">
                                <i class="ti ti-info-circle h2 f-w-400 mb-0"></i>
                                <div class="media-body ms-3"> Aucune candidature a afficher dans cette section</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @include('admin.candidatures._admin_inscription_etudiant')

        </div>
    </div>
</div>

@include('admin.candidatures._script_formulaire')