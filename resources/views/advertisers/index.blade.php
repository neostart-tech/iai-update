@extends('base', [
    'title' => 'Liste des partenaires',
    'page_name' => 'Liste des partenaires',
    'breadcrumbs' => ['Partenaires', 'Liste des partenaires'],
])

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="text-end p-4 pb-sm-2 mb-2">
                <a href="{{ route('admin.advertisers.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus f-18"></i> Ajouter un partenaire
                </a>
            </div>
        </div>
        <div class="card-body">
            @if ($advertisers->isNotEmpty())
                <div class="dt-responsive table-responsive">
                    <table id="dom-jquery" class="table table-striped table-hover table-bordered">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Email</th>
                                <th scope="col">Code</th>
                                <th scope="col">Lien du site</th>
                                <th scope="col" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($advertisers as $key => $advertiser)
                                <tr>
                                    <th scope="row">{{ $key += 1 }}</th>
                                    <td>{{ $advertiser->nom }}</td>
                                    <td>{{ $advertiser->email }}</td>
                                    <td>
                                        <a href="{{ $advertiser->site }}" target="_blank">
                                            {{ $advertiser->site }}
                                            <i data-feather="external-link"></i>
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <ul class="list-inline me-auto mb-0">
                                            <li class="list-inline-item align-bottom" data-bs-toggle="tooltip"
                                                title="Détails">
                                                <a href="{{ route('admin.advertisers.show', $advertiser) }}"
                                                    class="avtar avtar-xs btn-link-secondary btn-pc-default">
                                                    <i class="ti ti-eye f-18"></i>
                                                </a>
                                            </li>
                                            <li class="list-inline-item align-bottom" data-bs-toggle="tooltip"
                                                title="Modifier">
                                                <a href="{{ route('admin.advertisers.edit', $advertiser) }}"
                                                    class="avtar avtar-xs btn-link-success btn-pc-default">
                                                    <i class="ti ti-edit-circle f-18"></i>
                                                </a>
                                            </li>
                                            <li class="list-inline-item align-bottom" data-bs-toggle="tooltip"
                                                title="Supprimer">
                                                <a href="#" data-bs-target="#exampleModalToggle2"
                                                    data-bs-toggle="modal"
                                                    onclick="deletePartenaire({{ Illuminate\Database\Eloquent\Casts\Json::encode($advertiser) }})"
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
                                <th scope="col">Nom</th>
                                <th scope="col">Email</th>
                                <th scope="col">Lien du site</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <x-empty-table />
            @endif
        </div>
    </div>
    @include('advertisers.____show')
    <script>
        function deletePartenaire(partenaire) {
            document.getElementById('partenaireId').value = partenaire.id
        }
    </script>
@endsection
